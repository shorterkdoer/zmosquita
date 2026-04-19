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
            border-bottom: 2px solid #28a745;
            padding-bottom: 10px;
        }
        .badge {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .info {
            background: #d4edda;
            padding: 15px;
            border-left: 4px solid #28a745;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1><?= $this->e($title) ?></h1>

    <div class="info">
        <p>This demonstrates route parameters in multi-app mode.</p>
    </div>

    <h2>Route Information</h2>
    <ul>
        <li>Parameter ID: <span class="badge"><?= $this->e($id) ?></span></li>
        <li>Namespace: <span class="badge"><?= $this->e($namespace) ?></span></li>
        <li>Current App: <span class="badge"><?= $this->e($app) ?></span></li>
    </ul>

    <p><a href="/">← Back to Home</a></p>
</body>
</html>
