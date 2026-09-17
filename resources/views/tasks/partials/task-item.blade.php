{{-- SRS-004, SRS-005, SRS-006 --}}
<div class="list-group-item d-flex gap-3 align-items-start task-item {{ $task->is_completed ? 'bg-light opacity-75' : '' }}"
     data-task-id="{{ $task->id }}">

    {{-- SRS-006: tandai selesai / belum selesai --}}
    <form action="{{ route('tasks.toggle', $task) }}" method="POST" class="toggle-form pt-1">
        @csrf
        @method('PATCH')
        <button type="submit"
                class="btn btn-sm rounded-circle {{ $task->is_completed ? 'btn-success' : 'btn-outline-secondary' }}"
                style="width: 32px; height: 32px;"
                title="{{ $task->is_completed ? 'Tandai belum selesai' : 'Tandai selesai' }}">
            <span class="toggle-icon">{{ $task->is_completed ? '✓' : '' }}</span>
        </button>
    </form>

    <div class="flex-grow-1">
        <div class="task-title fw-semibold {{ $task->is_completed ? 'text-decoration-line-through text-muted' : '' }}">
            {{ $task->title }}
        </div>

        @if ($task->description)
            <div class="small text-muted">{{ $task->description }}</div>
        @endif

        <div class="d-flex flex-wrap gap-2 mt-2">
            {{-- SRS-005: prioritas --}}
            @php
                $priorityBadge = ['high' => 'danger', 'medium' => 'warning text-dark', 'low' => 'info text-dark'][$task->priority];
                $priorityLabel = ['high' => 'Tinggi', 'medium' => 'Sedang', 'low' => 'Rendah'][$task->priority];
            @endphp
            <span class="badge bg-{{ $priorityBadge }}">Prioritas: {{ $priorityLabel }}</span>

            {{-- SRS-005: penanda tenggat waktu --}}
            @if ($task->due_date)
                @php
                    $dueClass = match (true) {
                        $task->is_completed => 'bg-secondary',
                        $task->isOverdue() => 'bg-danger',
                        $task->isDueSoon() => 'bg-warning text-dark',
                        default => 'bg-light text-dark border',
                    };
                @endphp
                <span class="badge {{ $dueClass }}">
                    {{ $task->due_date->format('d M Y, H:i') }}
                    @if ($task->isOverdue()) · Terlambat!
                    @elseif ($task->isDueSoon()) · Segera jatuh tempo
                    @endif
                </span>
            @endif

            <span class="badge bg-light text-dark border">Dibuat oleh {{ $task->user?->name ?? '-' }}</span>
        </div>
    </div>

    {{-- SRS-004 --}}
    <div class="d-flex gap-1">
        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-warning">Edit</a>
        <form action="{{ route('tasks.destroy', $task) }}" method="POST"
              onsubmit="return confirm('Hapus tugas ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
        </form>
    </div>
</div>
