<div class="card h-100 shadow-sm">
    <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title text-truncate mb-0">
                <a href="{{ route('lists.show', $list) }}" class="text-decoration-none text-dark">
                    {{ $list->name }}
                </a>
            </h5>
            <span class="badge bg-secondary ms-1">{{ $badge }}</span>
        </div>

        <p class="card-text text-muted small flex-grow-1">
            {{ Str::limit($list->description ?? 'Tidak ada deskripsi', 80) }}
        </p>

        <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
            <small class="text-muted">
                {{ $list->tasks_count ?? 0 }} Tugas
            </small>

            @if ($list->owner_id === auth()->id())
                <form action="{{ route('lists.destroy', $list) }}" method="POST"
                      onsubmit="return confirm('Hapus daftar ini beserta SELURUH tugas dan keanggotaan collaborator terkait?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-link text-danger p-0 text-decoration-none">
                        Hapus
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>