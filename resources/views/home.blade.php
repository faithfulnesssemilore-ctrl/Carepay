<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test </title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @vite(['resources/css/bootstrap.css'])
@vite(['resources/css/custom.css'])
    @livewireStyles
</head>
<body>
    @livewire('Frontpages')
    @livewireScripts
</body>
</html>
