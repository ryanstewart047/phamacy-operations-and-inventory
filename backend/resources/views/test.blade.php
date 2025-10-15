<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 40px;
            background: #f0f0f0;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }
        h1 { color: #4CAF50; }
        .status { 
            padding: 10px;
            background: #e8f5e9;
            border-left: 4px solid #4CAF50;
            margin: 20px 0;
        }
        a {
            display: inline-block;
            padding: 12px 24px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        a:hover { background: #45a049; }
    </style>
</head>
<body>
    <div class="card">
        <h1>✓ Laravel Application is Running</h1>
        <div class="status">
            <strong>Status:</strong> Server is responding correctly<br>
            <strong>Laravel Version:</strong> {{ app()->version() }}<br>
            <strong>PHP Version:</strong> {{ PHP_VERSION }}<br>
            <strong>Environment:</strong> {{ config('app.env') }}
        </div>
        <p>If you're seeing this page, your Laravel server is working properly.</p>
        <a href="/">Go to Main Application</a>
    </div>
</body>
</html>
