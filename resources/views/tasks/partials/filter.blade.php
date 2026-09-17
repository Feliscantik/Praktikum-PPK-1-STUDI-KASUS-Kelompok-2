{{-- SRS-006: Penyaringan tugas --}}
<form method="GET" action="{{ route('lists.show', $taskList) }}" class="card card-body shadow-sm mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small mb-1">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="all" @selected(request('status', 'all') === 'all')>Semua</option>
                <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                <option value="pending" @selected(request('status') === 'pending')>Belum Selesai</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Prioritas</label>
            <select name="priority" class="form-select form-select-sm">
                <option value="">Semua Prioritas</option>
                <option value="high" @selected(request('priority') === 'high')>Tinggi</option>
                <option value="medium" @selected(request('priority') === 'medium')>Sedang</option>
                <option value="low" @selected(request('priority') === 'low')>Rendah</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small mb-1">Tenggat Waktu</label>
            <select name="due" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="overdue" @selected(request('due') === 'overdue')>Terlambat</option>
                <option value="today" @selected(request('due') === 'today')>Hari Ini</option>
                <option value="week" @selected(request('due') === 'week')>Minggu Ini</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary">Terapkan Filter</button>
            <a href="{{ route('lists.show', $taskList) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>
