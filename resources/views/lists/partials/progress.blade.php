{{-- SRS-007: Progress Tracking (Bootstrap 5) --}}
<div class="card shadow-sm mb-4" id="progress-container">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="card-title mb-0">📊 Progres Penyelesaian</h5>
            <span class="fs-3 fw-bold" id="progress-percentage">
                {{ $progress['percentage'] }}%
            </span>
        </div>

        {{-- Progress Bar --}}
        <div class="progress" style="height: 1rem;">
            <div id="progress-bar"
                 class="progress-bar bg-{{ $progress['color'] }}"
                 role="progressbar"
                 style="width: {{ $progress['percentage'] }}%"
                 aria-valuenow="{{ $progress['percentage'] }}"
                 aria-valuemin="0"
                 aria-valuemax="100">
            </div>
        </div>

        {{-- Statistik --}}
        <div class="d-flex gap-4 mt-3 small text-muted">
            <span>✅ Selesai: <strong id="completed-count">{{ $progress['completed'] }}</strong></span>
            <span>⏳ Belum: <strong id="pending-count">{{ $progress['pending'] }}</strong></span>
            <span>📋 Total: <strong id="total-count">{{ $progress['total'] }}</strong></span>
        </div>
    </div>
</div>