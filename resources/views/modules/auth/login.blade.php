<div class="min-h-screen flex items-center justify-center">
    <form wire:submit="authenticate" class="w-full max-w-sm space-y-4">
        <h1 class="text-2xl font-bold">Masuk PETORA</h1>
        <div>
            <label for="email">Email</label>
            <input id="email" type="email" wire:model="email" class="w-full border rounded px-3 py-2">
            @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="password">Kata sandi</label>
            <input id="password" type="password" wire:model="password" class="w-full border rounded px-3 py-2">
        </div>
        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded">Masuk</button>
    </form>
</div>
