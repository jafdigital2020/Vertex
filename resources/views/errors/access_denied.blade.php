<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Access Denied</title>
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
                repeating-linear-gradient(
                    90deg,
                    transparent 0px,
                    rgba(255, 255, 255, 0.02) 1px,
                    transparent 2px,
                    transparent 80px
                ),
                repeating-linear-gradient(
                    0deg,
                    transparent 0px,
                    rgba(255, 255, 255, 0.02) 1px,
                    transparent 2px,
                    transparent 80px
                );
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

        .shape-4 {
            width: 85px;
            height: 85px;
            bottom: 25%;
            left: 12%;
            animation-delay: 9s;
            background: radial-gradient(circle, rgba(237, 116, 100, 0.07) 0%, transparent 80%);
        }

        .shape-5 {
            width: 35px;
            height: 35px;
            top: 60%;
            left: 20%;
            animation-delay: 12s;
            background: radial-gradient(circle, rgba(18, 81, 93, 0.08) 0%, transparent 80%);
        }

        .geometric-pattern {
            position: absolute;
            width: 150px;
            height: 150px;
            opacity: 0.02;
            background: 
                conic-gradient(
                    from 0deg,
                    rgba(0, 128, 128, 0.1) 0deg,
                    rgba(255, 180, 0, 0.1) 72deg,
                    rgba(237, 116, 100, 0.1) 144deg,
                    rgba(181, 54, 84, 0.1) 216deg,
                    rgba(18, 81, 93, 0.1) 288deg,
                    rgba(0, 128, 128, 0.1) 360deg
                );
            border-radius: 50%;
            animation: rotate 30s linear infinite;
            filter: blur(1px);
        }

        .pattern-1 {
            top: 8%;
            right: 8%;
            animation-delay: 0s;
        }

        .pattern-2 {
            bottom: 10%;
            left: 5%;
            animation-delay: 10s;
            transform: scale(0.6);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-10px) rotate(1deg); }
            66% { transform: translateY(5px) rotate(-1deg); }
        }

        @keyframes drift {
            from { transform: translateX(-50px) translateY(-25px); }
            to { transform: translateX(50px) translateY(25px); }
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
                var(--mustard) 20%, 
                var(--coral) 50%, 
                var(--teal) 80%, 
                transparent 100%
            );
            opacity: 0.6;
        }

        .container::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border-radius: 20px;
            background: 
                radial-gradient(circle at 30% 30%, rgba(255, 180, 0, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 70% 70%, rgba(0, 128, 128, 0.03) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
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

        .access-icon {
            width: 96px;
            height: 96px;
            margin: 0 auto 32px;
            background: 
                linear-gradient(135deg, var(--raspberry) 0%, var(--coral) 50%, #ff6b8a 100%);
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            animation: iconPulse 3s ease-in-out infinite;
            box-shadow: 
                0 8px 32px rgba(181, 54, 84, 0.4),
                0 4px 16px rgba(237, 116, 100, 0.3),
                inset 0 2px 0 rgba(255, 255, 255, 0.2),
                inset 0 -2px 0 rgba(0, 0, 0, 0.1);
        }

        .access-icon::before {
            content: '';
            position: absolute;
            inset: -3px;
            background: 
                linear-gradient(135deg, 
                    var(--mustard) 0%, 
                    var(--coral) 25%, 
                    var(--raspberry) 50%, 
                    var(--teal) 75%, 
                    var(--mustard) 100%
                );
            border-radius: 27px;
            z-index: -1;
            opacity: 0.7;
            animation: rotate 8s linear infinite;
        }

        .access-icon::after {
            content: '';
            position: absolute;
            inset: -6px;
            background: 
                conic-gradient(
                    from 0deg,
                    transparent 0deg,
                    rgba(255, 180, 0, 0.1) 60deg,
                    rgba(0, 128, 128, 0.1) 120deg,
                    rgba(181, 54, 84, 0.1) 180deg,
                    rgba(237, 116, 100, 0.1) 240deg,
                    transparent 300deg,
                    transparent 360deg
                );
            border-radius: 30px;
            z-index: -2;
            animation: rotate 12s linear infinite reverse;
        }

        .access-icon svg {
            width: 48px;
            height: 48px;
            color: var(--white);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
            animation: iconFloat 2s ease-in-out infinite;
        }

        @keyframes iconPulse {
            0%, 100% { 
                transform: scale(1);
                box-shadow: 
                    0 8px 32px rgba(181, 54, 84, 0.4),
                    0 4px 16px rgba(237, 116, 100, 0.3);
            }
            50% { 
                transform: scale(1.05);
                box-shadow: 
                    0 12px 40px rgba(181, 54, 84, 0.5),
                    0 6px 20px rgba(237, 116, 100, 0.4);
            }
        }

        @keyframes iconFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
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
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
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
            margin-bottom: 40px;
            line-height: 1.6;
            max-width: 440px;
            margin-left: auto;
            margin-right: auto;
        }

        .info-grid {
            display: grid;
            gap: 16px;
            margin-bottom: 40px;
            text-align: left;
        }

        .info-card {
            background: linear-gradient(135deg, var(--gray-50), var(--gray-100));
            border: 1px solid var(--gray-200);
            border-radius: 16px;
            padding: 20px;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--raspberry), var(--coral));
        }

        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .info-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .info-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .info-card:nth-child(1) .info-icon {
            background: linear-gradient(135deg, var(--raspberry), var(--coral));
            color: var(--white);
        }

        .info-card:nth-child(2) .info-icon {
            background: linear-gradient(135deg, var(--teal), var(--dark-forest));
            color: var(--white);
        }

        .info-card:nth-child(3) .info-icon {
            background: linear-gradient(135deg, var(--mustard), var(--coral));
            color: var(--white);
        }

        .info-title {
            font-weight: 600;
            color: var(--gray-900);
            font-size: 15px;
            margin-bottom: 4px;
        }

        .info-description {
            color: #525252;
            font-size: 14px;
            line-height: 1.5;
        }

        .highlight {
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
            background: linear-gradient(135deg, var(--raspberry), var(--coral));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .primary-button:hover::before {
            opacity: 1;
        }

        .primary-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(181, 54, 84, 0.4);
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
            opacity: 0.8;
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
        <div class="floating-shape shape-4"></div>
        <div class="floating-shape shape-5"></div>
        <div class="geometric-pattern pattern-1"></div>
        <div class="geometric-pattern pattern-2"></div>
    </div>
    
    <div class="container">
        <div class="hr-context">HR System</div>
        
        <div class="access-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M12 1v6m0 6v6"/>
                <path d="M21 12h-6m-6 0H3"/>
                <path d="M12 1l3 3-3 3"/>
                <path d="M12 23l-3-3 3-3"/>
                <path d="M1 12l3-3v6"/>
                <path d="M23 12l-3-3v6"/>
            </svg>
        </div>

        <div class="status-badge">
            <div class="status-dot"></div>
            Premium Feature
        </div>

        <h1>Upgrade Required</h1>
        <p class="subtitle">
            This advanced HR feature requires a premium subscription to unlock its full potential. 
            Upgrade your plan to access enhanced workforce management capabilities.
        </p>

        <div class="info-grid">
            <div class="info-card">
                <div class="info-header">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </div>
                    <div class="info-title">Premium HR Analytics</div>
                </div>
                <div class="info-description">
                    Unlock <span class="highlight">advanced reporting</span> and workforce analytics to make data-driven HR decisions with comprehensive insights.
                </div>
            </div>

            <div class="info-card">
                <div class="info-header">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                        </svg>
                    </div>
                    <div class="info-title">Enhanced Automation</div>
                </div>
                <div class="info-description">
                    Access <span class="highlight">automated workflows</span> for payroll processing, compliance reporting, and employee lifecycle management.
                </div>
            </div>

            <div class="info-card">
                <div class="info-header">
                    <div class="info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                    </div>
                    <div class="info-title">Advanced Configuration</div>
                </div>
                <div class="info-description">
                    Customize <span class="highlight">enterprise-grade settings</span> with multi-branch management, custom fields, and integration capabilities.
                </div>
            </div>
        </div>

        <div class="cta-section">
            <a href="https://wizard.timora.ph/subscription" class="primary-button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
                <span>Upgrade to Premium</span>
            </a>
            <a href="javascript:history.back()" class="secondary-button">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5"/>
                    <path d="M12 19l-7-7 7-7"/>
                </svg>
                Return to Dashboard
            </a>
        </div>
    </div>
</body>

</html>