<div class="mx-auto flex max-w-7xl flex-col gap-8">
    <header>
        <p class="text-sm font-semibold uppercase tracking-wide text-teal-700">Cabang aktif</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Laporan operasional</h1>
        <p class="mt-2 text-slate-600">Ringkasan sepanjang waktu untuk order lunas dan selesai. Order draft, belum lunas, void, dan refund tidak dihitung sebagai omzet.</p>
    </header>

    <section aria-label="Ringkasan laporan" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-medium text-slate-600">Order terbayar</h2>
            <p class="mt-3 text-2xl font-semibold text-slate-950">{{ number_format($sales['total_orders']) }}</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-medium text-slate-600">Omzet terbayar</h2>
            <p class="mt-3 text-2xl font-semibold text-slate-950">Rp {{ number_format($sales['total_revenue'], 0, ',', '.') }}</p>
        </article>
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-medium text-slate-600">Pelanggan bertransaksi</h2>
            <p class="mt-3 text-2xl font-semibold text-slate-950">{{ number_format($customers['total_customers']) }}</p>
        </article>
    </section>
</div>
