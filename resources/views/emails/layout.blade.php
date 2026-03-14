<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #0f172a;
            padding-bottom: 40px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #1e293b;
            border-radius: 24px;
            overflow: hidden;
            margin-top: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        }
        .header {
            background: linear-gradient(to bottom right, #3b82f6, #6366f1);
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.025em;
            text-transform: uppercase;
            color: #ffffff;
        }
        .content {
            padding: 40px;
            line-height: 1.6;
            font-size: 16px;
        }
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            background: linear-gradient(to right, #3b82f6, #4f46e5);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 12px;
            font-weight: bold;
            margin-top: 20px;
        }
        .hr {
            border: 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin: 30px 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>{{ config('app.name', 'MovieShelf') }}</h1>
            </div>
            <div class="content">
                @yield('content')
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} {{ config('app.name') }}. Alle Rechte vorbehalten.<br>
                Diese E-Mail wurde automatisch generiert.
            </div>
        </div>
    </div>
</body>
</html>
