<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Reset kata sandi</title></head>
<body>
    <main>
        <h1>Reset kata sandi</h1>
        @if (session('status')) <p role="status">{{ session('status') }}</p> @endif
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
            @error('email') <p role="alert">{{ $message }}</p> @enderror
            <button type="submit">Kirim tautan reset</button>
        </form>
        <a href="{{ route('login') }}">Kembali ke login</a>
    </main>
</body>
</html>
