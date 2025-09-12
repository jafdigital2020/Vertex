<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Account Credentials</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background: #f4f6fb;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 420px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.07);
            padding: 32px 28px;
        }
        h3 {
            color: #2d3748;
            margin-bottom: 8px;
            font-size: 1.5rem;
            font-weight: 600;
        }
        p {
            color: #4a5568;
            margin-bottom: 18px;
            font-size: 1rem;
        }
        ul {
            list-style: none;
            padding: 0;
            margin-bottom: 22px;
        }
        ul li {
            background: #f1f5f9;
            margin-bottom: 10px;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 1rem;
            color: #2d3748;
            display: flex;
            align-items: center;
        }
        ul li strong {
            min-width: 120px;
            color: #2563eb;
            font-weight: 500;
            margin-right: 8px;
        }
        a.button {
            display: inline-block;
            background: #2563eb;
            color: #fff !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 10px;
            transition: background 0.2s;
        }
        a.button:hover {
            background: #1e40af;
        }
        em {
            color: #e53e3e;
            font-size: 0.97rem;
        }
        @media (max-width: 500px) {
            .container {
                padding: 18px 6vw;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h3>Welcome, {{ $fullName }}!</h3>
        <p>Your account has been created. Here are your login details:</p>
        <ul>
            <li><strong>Full Name:</strong> {{ $fullName }}</li>
            <li><strong>Company Code:</strong> {{ $company_code }}</li>
            <li><strong>Username:</strong> {{ $username }}</li>
            <li><strong>Email:</strong> {{ $email }}</li>
            <li><strong>Password:</strong> {{ $password }}</li>
        </ul>
        <a href="https://payroll.timora.ph" class="button">Log In to Your Account</a>
        <p style="margin-top:22px;"><em>Please change your password after logging in for security purposes.</em></p>
    </div>
</body>
</html>