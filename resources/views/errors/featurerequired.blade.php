<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Feature Access Required</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            color: #1f2937;
        }

        .container {
            background-color: #ffffff;
            padding: 3rem 2.5rem;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 620px;
            width: 100%;
            animation: fadeInUp 0.5s ease-in-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            20%,
            50%,
            80%,
            100% {
                transform: translateY(0);
            }

            40% {
                transform: translateY(-10px);
            }

            60% {
                transform: translateY(-5px);
            }
        }

        h1 {
            color: #111926;
            font-size: 2.2rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .subtitle {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .features-box {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1rem;
            gap: 0.75rem;
        }

        .feature-item:last-child {
            margin-bottom: 0;
        }

        .feature-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .feature-text {
            flex: 1;
        }

        .feature-title {
            font-weight: 600;
            color: #111926;
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .feature-desc {
            color: #6b7280;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .highlight {
            color: #667eea;
            font-weight: 600;
        }

        .cta-section {
            margin-top: 2rem;
        }

        a.button {
            display: inline-block;
            padding: 1rem 2.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        a.button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
        }

        a.button:active {
            transform: translateY(0);
        }

        .secondary-button {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.3s ease;
        }

        .secondary-button:hover {
            color: #111926;
        }

        .badge {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @media (max-width: 640px) {
            .container {
                padding: 2rem 1.5rem;
            }

            h1 {
                font-size: 1.8rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            a.button {
                padding: 0.9rem 2rem;
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon">🔓</div>
        <div class="badge">Unlock Premium Features</div>
        <h1>Feature Access Required</h1>
        <p class="subtitle">
            To access this feature, you need to subscribe to a new plan or avail branch add-ons.
            Choose the option that works best for your business!
        </p>

        <div class="features-box">
            <div class="feature-item">
                <div class="feature-icon">📦</div>
                <div class="feature-text">
                    <div class="feature-title">Subscription Portal</div>
                    <div class="feature-desc">
                        <span class="highlight">Your own dedicated portal</span> to manage all your subscriptions in one
                        place. Full control and transparency.
                    </div>
                </div>
            </div>

            <div class="feature-item">
                <div class="feature-icon">💰</div>
                <div class="feature-text">
                    <div class="feature-title">Branch Add-ons</div>
                    <div class="feature-desc">
                        <span class="highlight">Pay only for what you use</span> – flexible add-ons that scale with your
                        needs. No commitment, just results.
                    </div>
                </div>
            </div>

            <div class="feature-item">
                <div class="feature-icon">⚡</div>
                <div class="feature-text">
                    <div class="feature-title">Instant Activation</div>
                    <div class="feature-desc">
                        Get started immediately with our seamless onboarding process. Access premium features in
                        minutes.
                    </div>
                </div>
            </div>
        </div>

        <div class="cta-section">
            <a href="https://wizard.timora.ph/wizard?system=timora&plan=core&trial=true&billingPeriod=monthly"
                class="button">
                Get Started Now →
            </a>
            <br>
            <a href="javascript:history.back()" class="secondary-button">← Go Back</a>
        </div>
    </div>
</body>

</html>