<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'PETORA') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    @auth
        <div class="min-h-screen md:flex">
            <aside class="w-full border-b bg-white p-4 md:min-h-screen md:w-64 md:border-b-0 md:border-r">
                <a class="text-xl font-bold" href="{{ route('dashboard') }}">PETORA</a>
                <nav aria-label="Navigasi utama" class="mt-6 flex flex-wrap gap-3 md:flex-col">
                    @can('dashboard.view') <a href="{{ route('dashboard') }}">Dashboard</a> @endcan
                    @can('customers.view') <a href="{{ route('customers.index') }}">Pelanggan</a> @endcan
                    @can('pets.view') <a href="{{ route('pets.index') }}">Hewan</a> @endcan
                    @can('bookings.view') <a href="{{ route('booking.index') }}">Booking</a> @endcan
                    @can('veterinary.view') <a href="{{ route('veterinary.index') }}">Veteriner</a> @endcan
                    @can('grooming.view') <a href="{{ route('grooming.index') }}">Grooming</a> @endcan
                    @can('pos.view') <a href="{{ route('pos.index') }}">POS</a> @endcan
                    @can('inventory.view') <a href="{{ route('inventory.index') }}">Inventaris</a> @endcan
                    @can('finance.view') <a href="{{ route('finance.index') }}">Keuangan</a> @endcan
                    @can('staff.view') <a href="{{ route('staff.index') }}">Staf</a> @endcan
                    @can('audit.view') <a href="{{ route('audit.index') }}">Audit Log</a> @endcan
                    @can('settings.view') <a href="{{ route('settings.index') }}">Pengaturan</a> @endcan
                </nav>
            </aside>
            <div class="min-w-0 flex-1">
                <header class="flex items-center justify-between border-b bg-white px-6 py-4">
                    <span>{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Keluar</button>
                    </form>
                </header>
                <main class="p-6">
                    @isset($slot) {{ $slot }} @endisset
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        <main class="min-h-screen">
            @isset($slot) {{ $slot }} @endisset
            @yield('content')
        </main>
    @endauth
    @livewireScripts
</body>
</html>
