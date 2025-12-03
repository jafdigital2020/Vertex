{{-- Subscription Days Remaining Marquee --}}
@php
    $user = Auth::user() ?? Auth::guard('global')->user();
    $showMarquee = false;
    $marqueeMessage = '';

    if ($user && isset($user->tenant_id)) {
        $sub = \App\Models\Subscription::where('tenant_id', $user->tenant_id)
            ->where('status', 'active')
            ->first();

        if ($sub) {
            $days = method_exists($sub, 'getDaysUntilRenewal')
                ? $sub->getDaysUntilRenewal()
                : \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($sub->next_renewal_date ?? \Carbon\Carbon::now()), false);

            if ($days >= 0) {
                $showMarquee = true;
                $expiryDate = \Carbon\Carbon::parse($sub->next_renewal_date)->format('M d, Y');

                if ($days === 0) {
                    $marqueeMessage = "⚠️ Your subscription expires TODAY ({$expiryDate})! Please renew to continue service.";
                } elseif ($days === 1) {
                    $marqueeMessage = "⚠️ Your subscription expires TOMORROW ({$expiryDate})! Please renew soon.";
                } elseif ($days <= 7) {
                    $marqueeMessage = "⚠️ Your subscription will expire in {$days} days on {$expiryDate}. Please renew to avoid service interruption.";
                } elseif ($days <= 30) {
                    $marqueeMessage = "📅 Your subscription will renew in {$days} days on {$expiryDate}.";
                } else {
                    $marqueeMessage = "✅ Your subscription is active. Next renewal: {$expiryDate} ({$days} days remaining).";
                }
            }
        } else {
            // For testing: Show even if no subscription found
            $showMarquee = true;
            $marqueeMessage = "📢 Welcome to Timora! Your workspace is ready.";
        }
    } else {
        // For testing: Show for users without tenant_id
        $showMarquee = true;
        $marqueeMessage = "📢 Welcome to Timora! Your workspace is ready.";
    }
@endphp

{{-- Debug: Show Marquee = {{ $showMarquee ? 'TRUE' : 'FALSE' }} --}}
{{-- Debug: Message = {{ $marqueeMessage }} --}}

<div id="subscription-marquee-overlay" class="subscription-marquee-overlay" style="display: none;">
    <div class="subscription-marquee-container">
        <button type="button" class="marquee-close-btn" id="closeMarqueeBtn" aria-label="Close">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        <div class="subscription-marquee">
            <div class="marquee-content">
                <span class="marquee-text">{{ $marqueeMessage ?: 'Test Marquee Message - Subscription Info Here' }}</span>
                <span class="marquee-text">{{ $marqueeMessage ?: 'Test Marquee Message - Subscription Info Here' }}</span>
                <span class="marquee-text">{{ $marqueeMessage ?: 'Test Marquee Message - Subscription Info Here' }}</span>
            </div>
        </div>
    </div>
</div>

<style>
    .subscription-marquee-overlay {
        position: fixed;
        top: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10000;
        width: 90%;
        max-width: 1200px;
    }

    .subscription-marquee-container {
        position: relative;
        background: linear-gradient(135deg, #008080 0%, #006666 100%);
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 128, 128, 0.4);
        padding: 20px 60px 20px 20px;
        overflow: hidden;
        animation: slideDown 0.5s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .marquee-close-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        color: white;
        z-index: 10;
    }

    .marquee-close-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .marquee-close-btn:active {
        transform: scale(0.95);
    }

    .subscription-marquee {
        width: 100%;
        overflow: hidden;
        white-space: nowrap;
        position: relative;
    }

    .marquee-content {
        display: inline-block;
        animation: marquee 30s linear infinite;
        padding-left: 100%;
    }

    .marquee-text {
        display: inline-block;
        padding: 0 50px;
        font-size: 18px;
        font-weight: 600;
        color: white;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    @keyframes marquee {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(-66.666%);
        }
    }

    /* Pause animation on hover */
    .subscription-marquee:hover .marquee-content {
        animation-play-state: paused;
    }

    /* Responsive design */
    @media (max-width: 991.98px) {
        .subscription-marquee-overlay {
            width: 95%;
        }

        .subscription-marquee-container {
            padding: 15px 50px 15px 15px;
        }

        .marquee-text {
            font-size: 14px;
            padding: 0 30px;
        }

        .marquee-close-btn {
            width: 28px;
            height: 28px;
        }

        .marquee-close-btn svg {
            width: 16px;
            height: 16px;
        }
    }

    @media (max-width: 575.98px) {
        .marquee-text {
            font-size: 12px;
            padding: 0 20px;
        }

        .subscription-marquee-container {
            padding: 12px 45px 12px 12px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const overlay = document.getElementById('subscription-marquee-overlay');
        const closeBtn = document.getElementById('closeMarqueeBtn');
        const SHOW_INTERVAL = 3 * 60 * 1000; // 3 minutes in milliseconds
        const DISPLAY_DURATION = 10 * 1000; // 10 seconds
        const STORAGE_KEY = 'lastMarqueeShown';
        let marqueeInterval;

        console.log('🎯 Subscription Marquee Script Loaded');
        console.log('Overlay element:', overlay);

        if (!overlay) {
            console.error('❌ Marquee overlay element not found!');
            return;
        }

        function showMarquee() {
            console.log('📢 Showing marquee...');
            overlay.style.display = 'block';
            localStorage.setItem(STORAGE_KEY, Date.now().toString());

            // Auto-hide after DISPLAY_DURATION
            setTimeout(() => {
                hideMarquee();
            }, DISPLAY_DURATION);
        }

        function hideMarquee() {
            console.log('🚫 Hiding marquee...');
            overlay.style.display = 'none';
        }

        function shouldShowMarquee() {
            const lastShown = localStorage.getItem(STORAGE_KEY);
            if (!lastShown) return true;

            const timeSinceLastShow = Date.now() - parseInt(lastShown);
            return timeSinceLastShow >= SHOW_INTERVAL;
        }

        function startMarqueeTimer() {
            console.log('⏰ Starting marquee timer - will show in 2 seconds...');
            // Show immediately on first page load (after 2 seconds)
            setTimeout(showMarquee, 2000);

            // Set up recurring interval
            marqueeInterval = setInterval(() => {
                showMarquee();
            }, SHOW_INTERVAL);
        }

        // Close button handler
        closeBtn.addEventListener('click', function() {
            hideMarquee();
        });

        // Close on overlay click (outside container)
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                hideMarquee();
            }
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && overlay.style.display === 'block') {
                hideMarquee();
            }
        });

        // Start the marquee timer
        console.log('🚀 Calling startMarqueeTimer()...');
        startMarqueeTimer();

        // Clean up interval on page unload
        window.addEventListener('beforeunload', function() {
            if (marqueeInterval) {
                clearInterval(marqueeInterval);
            }
        });
    });

    // Log when script is parsed
    console.log('📝 Subscription Marquee Script Parsed');
</script>
