@extends('layouts.app')

@section('content')
    <div class="mx-auto flex max-w-7xl flex-col gap-8">
        <section class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">{{ $branch_name }}</p>
                <h1 class="text-3xl font-bold tracking-tight text-slate-950">Dashboard PETORA</h1>
                <p class="mt-1 text-slate-600">Selamat datang, {{ auth()->user()->name }}.</p>
            </div>
            <p class="text-sm text-slate-500">Ringkasan transaksi hari ini</p>
        </section>

        <section aria-label="Ringkasan operasional" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-medium text-slate-600">Omzet hari ini</h2>
                <p class="mt-3 text-2xl font-semibold text-slate-950">Rp {{ number_format($revenue_today, 0, ',', '.') }}</p>
                <p class="mt-1 text-sm text-slate-500">Order lunas dan selesai</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-medium text-slate-600">Order hari ini</h2>
                <p class="mt-3 text-2xl font-semibold text-slate-950">{{ number_format($orders_today) }}</p>
                <p class="mt-1 text-sm text-slate-500">Pembayaran berhasil di cabang aktif</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-medium text-slate-600">Pelanggan cabang</h2>
                <p class="mt-3 text-2xl font-semibold text-slate-950">{{ number_format($customers) }}</p>
                <p class="mt-1 text-sm text-slate-500">Pelanggan yang memiliki transaksi</p>
            </article>
        </section>

        <section class="flex flex-col gap-3">
            <h2 class="text-lg font-semibold text-slate-950">Akses cepat</h2>
            <nav aria-label="Akses cepat" class="flex flex-wrap gap-3">
                @can('pos.create')
                    <a class="rounded-lg bg-teal-700 px-4 py-2 font-medium text-white hover:bg-teal-800" href="{{ route('pos.create') }}">Buat transaksi</a>
                @endcan
                @can('bookings.create')
                    <a class="rounded-lg border border-slate-300 bg-white px-4 py-2 font-medium text-slate-700 hover:bg-slate-100" href="{{ route('booking.create') }}">Buat booking</a>
                @endcan
                @can('customers.create')
                    <a class="rounded-lg border border-slate-300 bg-white px-4 py-2 font-medium text-slate-700 hover:bg-slate-100" href="{{ route('customers.create') }}">Tambah pelanggan</a>
                @endcan
                @can('reports.view')
                    <a class="rounded-lg border border-slate-300 bg-white px-4 py-2 font-medium text-slate-700 hover:bg-slate-100" href="{{ route('reports.index') }}">Lihat laporan</a>
                @endcan
            </nav>
        </section>
    </div>
@endsection
