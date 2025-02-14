<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
        <link rel="icon" type="image/svg" href="{{ asset('favicon.svg') }}">
        <title inertia>{{ config('app.name', 'Laravel') }}</title>
        
        @inertiaHead
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        @inertia
    </body>
</html>