<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Struk')</title>
    @vite(['resources/css/app.css'])
    <style>
        body { background: #f3f4f6; }
        .receipt { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 12px; }
        @media print {
            @page { size: 80mm auto; margin: 4mm; }
            body { background: #fff; }
            .no-print { display: none !important; }
            .receipt-wrap { box-shadow: none !important; margin: 0 !important; width: 100% !important; max-width: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="antialiased">
    @yield('content')
</body>
</html>
