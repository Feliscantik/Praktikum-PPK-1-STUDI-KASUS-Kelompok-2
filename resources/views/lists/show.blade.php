@extends('layouts.app')

@section('title', $taskList->name)

@section('content')
@php $isOwner = $taskList->owner_id === auth()->id(); @endphp

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h3 class="mb-1">{{ $taskList->name }}</h3>
        <p class="text-muted mb-1">{{ $taskList->description }}</p>
        <span class="badge bg-secondary">Owner: {{ $taskList->owner->name }}</span>
        @unless ($isOwner)
            <span class="badge bg-info text-dark">Anda: Collaborator</span>
        @endunless
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('tasks.create', $taskList) }}" class="btn btn-primary">+ Tambah Tugas</a>
        <a href="{{ route('lists.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>
</div>

{{-- SRS-007 --}}
@include('lists.partials.progress', ['progress' => $progress])

<div class="row g-4">
    <div class="col-lg-8">
        {{-- SRS-006 --}}
        @include('tasks.partials.filter', ['taskList' => $taskList])

        <div class="list-group shadow-sm" id="task-list">
            @forelse ($tasks as $task)
                @include('tasks.partials.task-item', ['task' => $task])
            @empty
                <div class="list-group-item text-center text-muted py-4">
                    Tidak ada tugas yang cocok dengan filter.
                </div>
            @endforelse
        </div>
    </div>

    <div class="col-lg-4">
        {{-- SRS-003 --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h6 class="card-title">Anggota / Collaborator</h6>

                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span>{{ $taskList->owner->name }} <small class="text-muted">({{ $taskList->owner->email }})</small></span>
                        <span class="badge bg-dark">Owner</span>
                    </li>
                    @forelse ($taskList->collaborators as $member)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>{{ $member->name }} <small class="text-muted">({{ $member->email }})</small></span>
                            @if ($isOwner)
                                <form action="{{ route('lists.members.remove', [$taskList, $member]) }}" method="POST"
                                      onsubmit="return confirm('Cabut akses collaborator ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="list-group-item px-0 text-muted small">Belum ada collaborator.</li>
                    @endforelse
                </ul>

                @if ($isOwner)
                    <form action="{{ route('lists.members.add', $taskList) }}" method="POST">
                        @csrf
                        <label class="form-label small">Tambah collaborator (email)</label>
                        <div class="input-group input-group-sm">
                            <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                            <button class="btn btn-primary">Tambah</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        {{-- SRS-002: hanya owner yang boleh mengubah/menghapus daftar --}}
        @if ($isOwner)
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Pengaturan Daftar</h6>
                    <form action="{{ route('lists.update', $taskList) }}" method="POST" class="mb-3">
                        @csrf
                        @method('PUT')
                        <div class="mb-2">
                            <label class="form-label small">Nama</label>
                            <input type="text" name="name" class="form-control form-control-sm"
                                   value="{{ old('name', $taskList->name) }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">Deskripsi</label>
                            <textarea name="description" rows="2"
                                      class="form-control form-control-sm">{{ old('description', $taskList->description) }}</textarea>
                        </div>
                        <button class="btn btn-sm btn-primary w-100">Simpan Perubahan</button>
                    </form>

                    {{-- SRS-002 & SRS-003: Pesan konfirmasi mencakup seluruh tugas dan akses collaborator --}}
                    <form action="{{ route('lists.destroy', $taskList) }}" method="POST"
                          onsubmit="return confirm('Hapus daftar ini beserta SELURUH tugas dan keanggotaan collaborator terkait?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger w-100">Hapus Daftar</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// SRS-006 + SRS-007: toggle tanpa reload, progres diperbarui langsung.
document.querySelectorAll('.toggle-form').forEach((form) => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const item = form.closest('.task-item');

        try {
            const response = await fetch(form.action, {
                method: 'POST', // _method=PATCH dikirim lewat FormData
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });

            if (!response.ok) throw new Error('Gagal memperbarui tugas.');

            const data = await response.json();

            // Perbarui tampilan item tugas
            item.classList.toggle('bg-light', data.is_completed);
            item.classList.toggle('opacity-75', data.is_completed);
            item.querySelector('.task-title')
                .classList.toggle('text-decoration-line-through', data.is_completed);
            item.querySelector('.task-title')
                .classList.toggle('text-muted', data.is_completed);

            const button = form.querySelector('button');
            button.classList.toggle('btn-success', data.is_completed);
            button.classList.toggle('btn-outline-secondary', !data.is_completed);
            button.querySelector('.toggle-icon').textContent = data.is_completed ? '✓' : '';

            // SRS-007: perbarui progres secara real-time
            const p = data.progress;
            document.getElementById('progress-percentage').textContent = p.percentage + '%';
            const bar = document.getElementById('progress-bar');
            bar.style.width = p.percentage + '%';
            bar.className = 'progress-bar bg-' + p.color;
            document.getElementById('completed-count').textContent = p.completed;
            document.getElementById('pending-count').textContent = p.pending;
            document.getElementById('total-count').textContent = p.total;
        } catch (error) {
            // Fallback: kirim form secara normal bila AJAX gagal.
            form.submit();
        }
    });
});
</script>
@endpush