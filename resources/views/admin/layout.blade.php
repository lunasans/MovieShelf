<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global ACP - MovieShelf Mastery</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: #e50914;
            --primary-hover: #b20710;
            --bg-dark: #0f0f0f;
            --card-bg: #1a1a1a;
            --text-main: #ffffff;
            --text-muted: #a0a0a0;
            --accent: #f5f5f1;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        aside {
            width: 280px;
            background-color: #000;
            border-right: 1px solid #333;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            margin-bottom: 3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        nav li {
            margin-bottom: 0.5rem;
        }

        nav a {
            color: var(--text-muted);
            text-decoration: none;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            display: block;
            transition: all 0.3s ease;
        }

        nav a:hover, nav a.active {
            background-color: rgba(229, 9, 20, 0.1);
            color: var(--primary);
        }

        /* Main Content */
        main {
            flex-grow: 1;
            padding: 3rem;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        h1 { margin: 0; font-size: 2rem; }

        .btn {
            background-color: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover { background-color: var(--primary-hover); }

        .card {
            background-color: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            border: 1px solid #333;
        }

        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        .alert-success { background: rgba(5, 150, 105, 0.2); color: #10b981; border: 1px solid #065f46; }
    </style>
</head>
<body>
    <aside>
        <a href="{{ route('admin.dashboard') }}" class="logo">
            <img src="{{ asset('img/logo/logo.png') }}" alt="MovieShelf" style="height: 40px; margin-right: 10px;">
        </a>
        <nav>
            <ul>
                <li><a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a></li>
                <li><a href="{{ route('admin.tenants') }}" class="{{ request()->routeIs('admin.tenants') ? 'active' : '' }}">Filmregale (Tenants)</a></li>
                <li><a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">Plattform-Einstellungen</a></li>
                <li style="margin-top: 2rem;"><a href="/" style="font-size: 0.9rem;">← Zur Landingpage</a></li>
            </ul>
        </nav>
    </aside>

    <main>
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
