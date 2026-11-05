<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>PETORA</title></head>
<body>
    <h1>Dashboard PETORA</h1>
    <p>Selamat datang, {{ auth()->user()->name }}</p>
    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
</body>
</html>
