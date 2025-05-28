<?php

namespace App\Models;

use App\Models\Package;
use App\Models\Subscription;
use App\Models\OrganizationDatabase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'package_id', 'status'];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function database()
    {
        return $this->hasOne(OrganizationDatabase::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // ✅ Get the latest active subscription
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->orderBy('subscription_end', 'desc')
            ->first();
    }

    // ✅ Check if the organization has an active subscription
    public function hasActiveSubscription()
    {
        $subscription = $this->activeSubscription();
        return $subscription && $subscription->subscription_end >= now();
    }
}
