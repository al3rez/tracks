<!DOCTYPE html>
<html>
<head>
    <title>Tracks Framework</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            background: #c52f24;
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        header h1 {
            font-size: 1.5rem;
        }
        
        nav {
            margin-top: 0.5rem;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            margin-right: 1rem;
            opacity: 0.9;
        }
        
        nav a:hover {
            opacity: 1;
        }
        
        main {
            background: white;
            padding: 2rem;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .flash {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }
        
        .flash.notice {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .flash.alert {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }
        
        th, td {
            text-align: left;
            padding: 0.75rem;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        form {
            margin: 1rem 0;
        }
        
        .field {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.25rem;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        textarea,
        select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        button,
        input[type="submit"] {
            background: #c52f24;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        
        button:hover,
        input[type="submit"]:hover {
            background: #a02319;
        }
        
        a {
            color: #c52f24;
            text-decoration: none;
        }
        
        a:hover {
            text-decoration: underline;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            border: 1px solid #f5c6cb;
        }
        
        .error ul {
            margin-left: 1.5rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Tracks Framework</h1>
            <nav>
                <a href="/">Home</a>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <?php if (!empty($flash['notice'])): ?>
            <div class="flash notice">
                <?= htmlspecialchars($flash['notice']) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($flash['alert'])): ?>
            <div class="flash alert">
                <?= htmlspecialchars($flash['alert']) ?>
            </div>
        <?php endif; ?>
        
        <main>
            <?= $yield ?>
        </main>
    </div>
</body>
</html>