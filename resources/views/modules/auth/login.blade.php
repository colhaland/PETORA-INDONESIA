<div class="min-h-screen flex items-center justify-center">
    <form wire:submit="authenticate" class="w-full max-w-sm space-y-4">
        <h1 class="text-2xl font-bold">Masuk PETORA</h1>
        <div>
            <label for="login">Email atau nomor telepon</label>
            <input id="login" type="text" wire:model="login" class="w-full border rounded px-3 py-2" autocomplete="username">
            @error('login') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="password">Kata sandi</label>
            <input id="password" type="password" wire:model="password" class="w-full border rounded px-3 py-2" autocomplete="current-password">
            @error('password') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded">Masuk</button>
        <a href="{{ route('password.request') }}">Lupa kata sandi?</a>
        @if (session('status')) <p role="status">{{ session('status') }}</p> @endif
    </form>
</div>
