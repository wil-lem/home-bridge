<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Assistant Bridge - @yield('title')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px;
        }
        header {
            background: #2c3e50;
            color: white;
            padding: 15px 0;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        header p {
            opacity: 0.8;
            font-size: 13px;
        }
        nav {
            background: white;
            padding: 10px 0;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-radius: 8px;
        }
        nav ul {
            list-style: none;
            display: flex;
            gap: 3px;
            flex-wrap: wrap;
        }
        nav a {
            text-decoration: none;
            padding: 8px 15px;
            color: #2c3e50;
            border-radius: 5px;
            transition: all 0.3s;
            display: block;
            font-size: 14px;
        }
        nav a:hover {
            background: #f0f0f0;
            color: #3498db;
        }
        nav a.active {
            background: #3498db;
            color: white;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .card h2 {
            margin-bottom: 10px;
            color: #2c3e50;
            font-size: 18px;
        }
        .card h3 {
            margin-top: 12px;
            margin-bottom: 8px;
            color: #34495e;
            font-size: 14px;
        }
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-ok { background: #27ae60; }
        .status-error { background: #e74c3c; }
        .status-warning { background: #f39c12; }
        code {
            background: #f8f8f8;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        pre {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 10px 0;
        }
        pre code {
            background: none;
            color: inherit;
            padding: 0;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-success:hover {
            background: #229954;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        input[type="text"], input[type="number"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .form-group {
            margin-bottom: 10px;
        }
        label {
            display: block;
            margin-bottom: 4px;
            font-weight: 500;
            color: #555;
            font-size: 13px;
        }
        .response-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 12px;
            margin-top: 10px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 300px;
            overflow-y: auto;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
        }
        footer {
            text-align: center;
            padding: 15px;
            color: #7f8c8d;
            font-size: 12px;
            margin-top: 30px;
        }
    </style>
    @yield('styles')
</head>
<body>
    <header>
        <div class="container">
            <h1>🏠 Home Assistant Bridge</h1>
            <p>AI-Friendly Home Automation API</p>
        </div>
    </header>

    <div class="container">
        <nav>
            <ul>
                <li><a href="/" class="{{ request()->is('/') ? 'active' : '' }}">API Testing</a></li>
                <li><a href="/training/overview" class="{{ request()->is('training/*') ? 'active' : '' }}">Training Steps</a></li>
                <li><a href="https://github.com/meta-llama/llama3" target="_blank">LLaMA 3 Docs</a></li>
            </ul>
        </nav>

        @yield('content')
    </div>

    <footer>
        <p>Home Assistant Bridge API - Powered by Laravel & Home Assistant</p>
    </footer>

    @yield('scripts')
</body>
</html>
