<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot()
    {
        // Rate Limiting Configuration
        $this->configureRateLimiting();

        View::composer('*', function ($view) {
            if (Session::has('role_data')) {
                $roleData = Session::get('role_data');
            } elseif (Auth::check()) {
                $roleData = [
                    "role_id" => 'global_user',
                    "menu_ids" => [],
                    "module_ids" => [],
                    "user_permission_ids" => [],
                ];
            } else {
                $roleData = [
                    "role_id" => null,
                    "menu_ids" => [],
                    "module_ids" => [],
                    "user_permission_ids" => [],
                ];
            }

            $view->with('role_data', $roleData);
        });

        View::composer('layout.partials.sidebar', function ($view) {
            $user = Auth::user() ?? Auth::guard('global')->user();
            $subscriptionNotice = ['show' => false];

            if ($user && $user->tenant_id) {
                $sub = Subscription::where('tenant_id', $user->tenant_id)
                    ->where('status', 'active')
                    ->first();

                if ($sub) {
                    $days = method_exists($sub, 'getDaysUntilRenewal') ? $sub->getDaysUntilRenewal() : Carbon::now()->diffInDays(Carbon::parse($sub->next_renewal_date ?? Carbon::now()), false);

                    if ($days <= 7 && $days >= 0 && method_exists($sub, 'hasRenewalInvoice') && $sub->hasRenewalInvoice()) {
                        $inv = $sub->getRenewalInvoice();
                        $subscriptionNotice = [
                            'show' => true,
                            'expiry_date' => Carbon::parse($sub->next_renewal_date)->format('M d, Y'),
                            'days_remaining' => $days,
                            'invoice_number' => $inv->invoice_number ?? null,
                            'amount_due' => $inv->amount_due ?? 0,
                            'billing_url' => url('billing'),
                        ];
                    }
                }
            }

            $view->with('subscriptionNotice', $subscriptionNotice);
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('sensitive', function (Request $request) {
            return Limit::perMinute(10)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
