<!DOCTYPE html>
<html>
<head>
    <title>{{ $taskList->name }}</title>
</head>
<body>

<a href="{{ route('lists.index') }}">
    ← Kembali
</a>

<h1>{{ $taskList->name }}</h1>

<p>{{ $taskList->description }}</p>

<p>
    Owner: {{ $taskList->owner->name }}
</p>

<hr>

<h2>Anggota / Collaborator</h2>

@if($taskList->owner_id === auth()->id())

    <hr>

    <h2>Tambah Collaborator</h2>

    <form
        action="{{ route('lists.members.add', $taskList) }}"
        method="POST"
    >
        @csrf

        <label>Email pengguna:</label>

        <input
            type="email"
            name="email"
            placeholder="contoh@email.com"
            required
        >

        <button type="submit">
            Tambahkan
        </button>
    </form>

@endif

@if($taskList->members->count() > 0)

    <ul>
        @foreach($taskList->members as $member)
            <li>
                {{ $member->user->name }}
                ({{ $member->user->email }})

                @if($taskList->owner_id === auth()->id())

                    <form
                        action="{{ route('lists.members.remove', [$taskList, $member->user]) }}"
                        method="POST"
                        style="display:inline;"
                    >
                        @csrf
                        @method('DELETE')

                        <button type="submit">
                            Hapus
                        </button>
                    </form>

                @endif
            </li>
        @endforeach
    </ul>

@else

    <p>Belum ada collaborator.</p>

@endif

<hr>

<h2>Edit List</h2>

<form action="{{ route('lists.update', $taskList) }}" method="POST">
    @csrf
    @method('PUT')

    <input
        type="text"
        name="name"
        value="{{ $taskList->name }}"
        required
    >

    <br><br>

    <textarea name="description">{{ $taskList->description }}</textarea>

    <br><br>

    <button type="submit">
        Simpan Perubahan
    </button>
</form>

<hr>

<form
    action="{{ route('lists.destroy', $taskList) }}"
    method="POST"
>
    @csrf
    @method('DELETE')

    <button type="submit">
        Hapus List
    </button>
</form>

</body>
</html>