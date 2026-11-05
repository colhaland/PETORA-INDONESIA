<main class="mx-auto max-w-xl space-y-6 p-6">
    <h1 class="text-2xl font-semibold">Aktifkan autentikasi dua faktor</h1>
    @if (auth()->user()->two_factor_confirmed_at)
        <p>Autentikasi dua faktor sudah aktif.</p>
    @else
        <p>Tambahkan akun ini ke aplikasi authenticator menggunakan secret di bawah, lalu verifikasi kode 6 digit.</p>
        <p class="break-all rounded bg-gray-100 p-3 font-mono" data-testid="two-factor-secret">{{ $secret }}</p>
        <p class="break-all text-sm">URI provisioning: {{ $provisioningUri }}</p>
        <form wire:submit="confirm" class="space-y-3">
            <label for="code">Kode authenticator</label>
            <input id="code" type="text" inputmode="numeric" autocomplete="one-time-code" wire:model="code" maxlength="6" required>
            @error('code') <p role="alert">{{ $message }}</p> @enderror
            <button type="submit">Verifikasi dan aktifkan</button>
        </form>
    @endif
    @if ($recoveryCodes !== [])
        <section>
            <h2 class="font-semibold">Kode pemulihan (tampilkan sekali)</h2>
            <ul>@foreach ($recoveryCodes as $recoveryCode)<li class="font-mono">{{ $recoveryCode }}</li>@endforeach</ul>
            <p>Simpan kode ini di tempat aman. Setiap kode hanya dapat digunakan sekali.</p>
        </section>
    @endif
</main>
