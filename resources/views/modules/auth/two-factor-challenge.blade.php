<div class="min-h-screen flex items-center justify-center">
    <form wire:submit="verify" class="w-full max-w-sm space-y-4">
        <h1 class="text-2xl font-bold">Verifikasi Dua Langkah</h1>
        <p class="text-sm">Masukkan kode dari aplikasi autentikator Anda.</p>
        <div>
            <label for="code">Kode</label>
            <input id="code" type="text" inputmode="numeric" autocomplete="one-time-code" wire:model="code" class="w-full border rounded px-3 py-2">
            @error('code') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded">Verifikasi</button>
    </form>
</div>
