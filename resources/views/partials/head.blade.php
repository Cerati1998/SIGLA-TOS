<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ collect([$title ?? null, auth()->check() ? session('company.name') : null, config('app.name', 'Laravel')])->filter()->implode(' | ') }}</title>

<link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2">

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
