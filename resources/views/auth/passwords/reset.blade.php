<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Kata sandi baru</title></head>
<body>
    <main>
        <h1>Buat kata sandi baru</h1>
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required>
            <label for="password">Kata sandi baru</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
            <label for="password_confirmation">Ulangi kata sandi baru</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            @error('email') <p role="alert">{{ $message }}</p> @enderror
            @error('password') <p role="alert">{{ $message }}</p> @enderror
            <button type="submit">Ubah kata sandi</button>
        </form>
    </main>
</body>
</html>
