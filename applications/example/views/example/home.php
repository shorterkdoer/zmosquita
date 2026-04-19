<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .badge {
            background: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .info {
            background: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1><?= $this->e($title) ?></h1>

    <div class="info">
        <p><strong><?= $this->e($message) ?></strong></p>
    </div>

    <h2>Application Information</h2>
    <ul>
        <li>Current Application: <span class="badge">example</span></li>
        <li>Namespace: <span class="badge">Applications\Example</span></li>
        <li>Base Path: <span class="badge">applications/example/</span></li>
    </ul>

    <h2>How it works</h2>
    <p>This application is accessed via the subdomain <strong>example.yourdomain.com</strong></p>
    <p>You can create as many applications as you need by:</p>
    <ol>
        <li>Creating a new directory in <code>applications/</code></li>
        <li>Registering it in <code>config/applications.php</code></li>
        <li>Creating your controllers, models, and views</li>
        <li>Setting up the subdomain in your DNS/server</li>
    </ol>
</body>
</html>
