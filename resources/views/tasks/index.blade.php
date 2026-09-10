<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JARA - Manajemen Tugas (SRS-004 & SRS-005)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">Daftar Tugas (JARA - Advanced Todo List)</h2>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            + Tambah Tugas Baru
        </button>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Judul & Deskripsi (SRS-004)</th>
                            <th>Prioritas (SRS-005)</th>
                            <th>Tenggat Waktu (SRS-005)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $index => $task)
                            @php
                                $isOverdue = strtotime($task->due_date) < time() && !$task->is_completed;
                            @endphp
                            <tr class="{{ $isOverdue ? 'table-danger' : '' }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $task->title }}</strong><br>
                                    <small class="text-muted">{{ $task->description }}</small>
                                </td>
                                <td>
                                    @php
                                        $badge = 'secondary';
                                        if($task->priority == 'High') $badge = 'danger';
                                        elseif($task->priority == 'Medium') $badge = 'warning text-dark';
                                        elseif($task->priority == 'Low') $badge = 'info';
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ $task->priority }}</span>
                                </td>
                                <td>
                                    {{ date('d M Y, H:i', strtotime($task->due_date)) }}
                                    @if($isOverdue)
                                        <br><span class="badge bg-danger mt-1">Terlambat!</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-warning btn-edit" 
                                                data-id="{{ $task->id }}" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editTaskModal">
                                            Edit
                                        </button>

                                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tugas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Belum ada tugas yang ditambahkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('tasks.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Tugas Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Judul Tugas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" required placeholder="Contoh: Desain UI Dashboard">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi (Opsional)</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Detail tambahan..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prioritas (SRS-005)</label>
                            <select class="form-select" name="priority">
                                <option value="Low">Rendah (Low)</option>
                                <option value="Medium" selected>Sedang (Medium)</option>
                                <option value="High">Tinggi (High)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tenggat Waktu / Due Date (SRS-005)</label>
                            <input type="datetime-local" class="form-control" name="due_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan Tugas</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editTaskForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Tugas (SRS-004)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Judul Tugas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi (Opsional)</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prioritas (SRS-005)</label>
                            <select class="form-select" id="edit_priority" name="priority">
                                <option value="Low">Rendah (Low)</option>
                                <option value="Medium">Sedang (Medium)</option>
                                <option value="High">Tinggi (High)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tenggat Waktu / Due Date (SRS-005)</label>
                            <input type="datetime-local" class="form-control" id="edit_due_date" name="due_date" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function () {
                let taskId = this.getAttribute('data-id');
                fetch(`/tasks/${taskId}/edit`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('edit_title').value = data.title;
                        document.getElementById('edit_description').value = data.description || '';
                        document.getElementById('edit_priority').value = data.priority;
                    
                        if(data.due_date) {
                            let formattedDate = data.due_date.replace(' ', 'T');
                            document.getElementById('edit_due_date').value = formattedDate;
                        }
                        
                        document.getElementById('editTaskForm').action = `/tasks/${taskId}`;
                    });
            });
        });
    </script>
</body>
</html>