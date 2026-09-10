<!DOCTYPE html>
<html>
<head>
    <title>JARA - Lists</title>
</head>
<body>

<h1>JARA - Daftar List</h1>

<a href="{{ route('lists.create') }}">
    + Buat List Baru
</a>

@if(session('success'))
    <p>{{ session('success') }}</p>
@endif

<hr>

@forelse($lists as $list)

    <h2>
        <a href="{{ route('lists.show', $list) }}">
            {{ $list->name }}
        </a>
    </h2>

    <p>
        {{ $list->description }}
    </p>

    <p>
        Owner: {{ auth()->user()->name }}
    </p>

    <hr>

@empty

    <p>Belum ada list.</p>

@endforelse

</body>
</html>