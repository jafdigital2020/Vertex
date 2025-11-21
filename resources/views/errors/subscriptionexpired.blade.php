<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Subscription Expired</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-forest: #12515D;
            --teal: #008080;
            --raspberry: #b53654;
            --coral: #ed7464;
            --mustard: #FFB400;
            --white: #ffffff;
            --black: #000000;
            --gray-50: #fafafa;
            --gray-100: #f5f5f5;
            --gray-200: #e5e5e5;
            --gray-900: #171717;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background:
                linear-gradient(135deg, var(--dark-forest) 0%, #0f3d47 25%, #1a4b56 50%, var(--teal) 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background:
                radial-gradient(circle at 20% 30%, rgba(255, 180, 0, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 80% 20%, rgba(237, 116, 100, 0.06) 0%, transparent 35%),
                radial-gradient(circle at 30% 80%, rgba(181, 54, 84, 0.05) 0%, transparent 30%),
                radial-gradient(circle at 70% 70%, rgba(0, 128, 128, 0.07) 0%, transparent 45%);
            pointer-events: none;
            animation: float 10s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            right: -50%;
            bottom: -50%;
            background:
                repeating-linear-gradient(90deg,
                    transparent 0px,
                    rgba(255, 255, 255, 0.02) 1px,
                    transparent 2px,
                    transparent 80px),
                repeating-linear-gradient(0deg,
                    transparent 0px,
                    rgba(255, 255, 255, 0.02) 1px,
                    transparent 2px,
                    transparent 80px);
            animation: drift 25s linear infinite;
            pointer-events: none;
            opacity: 0.4;
        }

        .background-elements {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: -1;
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.02));
            animation: floatShapes 12s ease-in-out infinite;
        }

        .shape-1 {
            width: 100px;
            height: 100px;
            top: 15%;
            left: 10%;
            animation-delay: 0s;
            background: radial-gradient(circle, rgba(0, 128, 128, 0.06) 0%, transparent 80%);
        }

        .shape-2 {
            width: 70px;
            height: 70px;
            top: 75%;
            right: 15%;
            animation-delay: 3s;
            background: radial-gradient(circle, rgba(255, 180, 0, 0.08) 0%, transparent 80%);
        }

        .shape-3 {
            width: 50px;
            height: 50px;
            top: 25%;
            right: 20%;
            animation-delay: 6s;
            background: radial-gradient(circle, rgba(181, 54, 84, 0.05) 0%, transparent 80%);
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            33% {
                transform: translateY(-10px) rotate(1deg);
            }
            66% {
                transform: translateY(5px) rotate(-1deg);
            }
        }

        @keyframes drift {
            from {
                transform: translateX(-50px) translateY(-25px);
            }
            to {
                transform: translateX(50px) translateY(25px);
            }
        }

        @keyframes floatShapes {
            0%, 100% {
                transform: translateY(0px) translateX(0px) rotate(0deg);
                opacity: 0.6;
            }
            25% {
                transform: translateY(-15px) translateX(8px) rotate(45deg);
                opacity: 0.4;
            }
            50% {
                transform: translateY(-8px) translateX(-12px) rotate(90deg);
                opacity: 0.8;
            }
            75% {
                transform: translateY(12px) translateX(4px) rotate(135deg);
                opacity: 0.5;
            }
        }

        .container {
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.92) 100%),
                linear-gradient(45deg, transparent 30%, rgba(0, 128, 128, 0.03) 50%, transparent 70%);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 48px 40px;
            border-radius: 28px;
            box-shadow:
                0 40px 80px rgba(0, 0, 0, 0.25),
                0 16px 40px rgba(18, 81, 93, 0.35),
                0 4px 16px rgba(181, 54, 84, 0.1),
                inset 0 2px 0 rgba(255, 255, 255, 0.8),
                inset 0 -1px 0 rgba(0, 0, 0, 0.05);
            text-align: center;
            max-width: 580px;
            width: 100%;
            position: relative;
            animation: slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg,
                    transparent 0%,
                    var(--raspberry) 20%,
                    var(--coral) 50%,
                    var(--mustard) 80%,
                    transparent 100%);
            opacity: 0.6;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.96);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .icon-wrapper {
            width: 80px;
            height: 80px;
            margin: 0 auto 32px;
            background: linear-gradient(135deg, var(--raspberry), var(--coral));
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: float 3s ease-in-out infinite;
        }

        .icon-wrapper::before {
            content: '';
            position: absolute;
            inset: -2px;
            background: linear-gradient(135deg, var(--mustard), var(--coral));
            border-radius: 22px;
            z-index: -1;
            opacity: 0.6;
        }

        .icon-wrapper svg {
            width: 40px;
            height: 40px;
            color: var(--white);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--raspberry), #d1477a);
            color: var(--white);
            padding: 8px 16px;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: var(--white);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        h1 {
            color: var(--gray-900);
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .subtitle {
            color: #525252;
            font-size: 16px;
            font-weight: 400;
            margin-bottom: 32px;
            line-height: 1.6;
            max-width: 440px;
            margin-left: auto;
            margin-right: auto;
        }

        .message-box {
            background: linear-gradient(135deg, #FFF5E1, #FFE8CC);
            border: 1px solid var(--mustard);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 32px;
        }

        .message-box p {
            color: var(--gray-900);
            font-size: 15px;
            line-height: 1.6;
            margin: 0;
        }

        .message-box strong {
            color: var(--raspberry);
            font-weight: 600;
        }

        .cta-section {
            display: flex;
            flex-direction: column;
            gap: 16px;
            align-items: center;
        }

        .primary-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 16px 32px;
            background: linear-gradient(135deg, var(--teal), var(--dark-forest));
            color: var(--white);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            min-width: 200px;
            justify-content: center;
        }

        .primary-button::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--mustard), var(--coral));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .primary-button:hover::before {
            opacity: 1;
        }

        .primary-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0, 128, 128, 0.4);
        }

        .primary-button span {
            position: relative;
            z-index: 1;
        }

        .secondary-button {
            color: #525252;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .secondary-button:hover {
            background: var(--gray-100);
            color: var(--gray-900);
        }

        .hr-context {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, var(--raspberry), var(--coral));
            color: var(--white);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.9;
        }

        @media (max-width: 640px) {
            .container {
                padding: 32px 24px;
                margin: 16px;
            }

            h1 {
                font-size: 24px;
            }

            .subtitle {
                font-size: 15px;
            }

            .primary-button {
                padding: 14px 24px;
                font-size: 14px;
                min-width: 180px;
            }

            .hr-context {
                position: static;
                margin-bottom: 16px;
                display: inline-block;
            }
        }
    </style>
</head>

<body>
    <div class="background-elements">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
    </div>

    <div class="container">
        <div class="hr-context">Timora HR System</div>

        <div class="icon-wrapper">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>

        <div class="status-badge">
            <div class="status-dot"></div>
            Subscription Expired
        </div>

        <h1>Subscription Has Expired</h1>
        <p class="subtitle">
            Your subscription has ended and access to system features is currently restricted.
        </p>

        <div class="message-box">
            <p>
                <strong>Action Required:</strong>
                {{ session('subscription_expired') ?? 'Your subscription has expired. Please contact your administrator to renew your subscription and restore access to all features.' }}
            </p>
        </div>

        <div class="cta-section">
            <a href="{{ route('employee-dashboard') }}" class="primary-button">
                <span>Return to Dashboard</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14" />
                    <path d="M12 5l7 7-7 7" />
                </svg>
            </a>
            <a href="{{ route('logout') }}" class="secondary-button"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                </svg>
                Sign Out
            </a>
        </div>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
</body>

</html>
