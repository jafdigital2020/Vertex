<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Access Denied</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #111926;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #1f2937;
        }

        .container {
            background-color: #ffffff;
            padding: 2.5rem 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            text-align: center;
            max-width: 420px;
            width: 90%;
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        h1 {
            color: #dc2626;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        p {
            color: #4b5563;
            margin-bottom: 1.8rem;
            font-size: 1rem;
        }

        a.button {
            display: inline-block;
            padding: 0.6rem 1.5rem;
            background-color: #111926;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        a.button:hover {
            background-color: #0c121b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <h1>Access Denied</h1>
        <p>You do not have permission to access this page.</p>
        <a href="javascript:history.back()" class="button">Go Back</a>
    </div>
</body>
</html>
