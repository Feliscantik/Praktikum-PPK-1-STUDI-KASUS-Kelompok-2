<!DOCTYPE html>
<html>
<head>
    <title>Buat List</title>
</head>
<body>

<h1>Buat List Baru</h1>

<form action="{{ route('lists.store') }}" method="POST">
    @csrf

    <label>Nama List</label>
    <br>
    <input type="text" name="name" required>

    <br><br>

    <label>Deskripsi</label>
    <br>
    <textarea name="description"></textarea>

    <br><br>

    <button type="submit">
        Buat List
    </button>
</form>

</body>
</html>