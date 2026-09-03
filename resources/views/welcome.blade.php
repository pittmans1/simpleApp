<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="nonce" content="{{ Vite::cspNonce() }}">
        <title>Trash Panda</title>
        @vite(['resources/js/app.js'])
    </head>
    <body>
        <div>
            <h1>Welcome to Trash Panda</h1>
            <p>This is a demo application using Laravel and Vue.js.</p>

        </div>
        <div id="main"><element-trash-panda></element-trash-panda></div>
    </body>
</html>
