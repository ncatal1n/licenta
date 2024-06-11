<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>imgVault @yield('title')</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')

</head>
<body class="flex flex-col h-fit w-full base-100">
    <div class="navbar h-[5vh] bg-base-300 ">
        <div class="flex-1">
            <x-application-logo />
        </div>
        <div class="flex-none gap-2">
            <a href="{{route("ui.upload")}}" wire:navigate>
                <button class="btn btn-sm btn-primary">Upload</button>
            </a>
            <livewire:navigation-buttons />
        </div>
    </div>
    {{ $slot }}
    <x-toaster-hub />
    <footer class="footer footer-center p-4 bg-base-300 text-base-content">
        <aside>
            <p>Copyright © 2024 - All right reserved by imgVault</p>
        </aside>
    </footer>
</body>
</html>
