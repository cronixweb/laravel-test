<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Expense Calculator')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }
        header {
            background: #0f172a;
            color: #f8fafc;
            padding: 1.25rem;
        }
        header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 960px;
            margin: 0 auto;
        }
        header nav a {
            color: #e2e8f0;
            text-decoration: none;
            margin-left: 1rem;
            font-weight: 500;
        }
        header nav a:hover {
            color: #94a3b8;
        }
        main {
            max-width: 960px;
            margin: 1.5rem auto 3rem;
            padding: 0 1.5rem;
        }
        .card {
            background: #fff;
            border-radius: 0.75rem;
            padding: 1.5rem;
            box-shadow: 0 20px 25px -15px rgba(15, 23, 42, 0.15), 0 10px 10px -10px rgba(15, 23, 42, 0.08);
        }
        .status {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #bbf7d0;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        a.button,
        button.button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #2563eb;
            color: #fff;
            padding: 0.6rem 1.2rem;
            border-radius: 0.6rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s ease-out;
        }
        a.button:hover,
        button.button:hover {
            background: #1d4ed8;
        }
        button.button {
            border: none;
            cursor: pointer;
        }
    </style>
    @stack('head')
</head>
<body>
<header>
    <div class="container">
        <div>Expense Calculator</div>
        <nav>
            <a href="{{ route('expenses.index') }}">Overview</a>
            <a href="{{ route('expenses.create') }}">Quick Add</a>
        </nav>
    </div>
</header>
<main>
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif
    @yield('content')
</main>
@stack('scripts')
</body>
</html>
