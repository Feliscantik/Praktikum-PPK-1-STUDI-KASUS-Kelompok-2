{{-- SRS-007: Progress Tracking --}}
<div class="bg-white rounded-lg shadow p-4 mb-4" id="progress-container">
    <div class="flex justify-between items-center mb-2">
        <h3 class="font-semibold">📊 Progres Penyelesaian</h3>
        <span class="text-2xl font-bold" id="progress-percentage">
            {{ $progress['percentage'] }}%
        </span>
    </div>
    
    {{-- Progress Bar --}}
    <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
        <div id="progress-bar"
             class="h-4 rounded-full transition-all duration-500
                    @if($progress['color'] === 'green') bg-green-500
                    @elseif($progress['color'] === 'yellow') bg-yellow-500
                    @elseif($progress['color'] === 'orange') bg-orange-500
                    @else bg-red-500 @endif"
             style="width: {{ $progress['percentage'] }}%">
        </div>
    </div>
    
    {{-- Statistik --}}
    <div class="flex gap-4 mt-3 text-sm">
        <span class="text-gray-600">
            ✅ Selesai: <strong id="completed-count">{{ $progress['completed'] }}</strong>
        </span>
        <span class="text-gray-600">
            ⏳ Belum: <strong id="pending-count">{{ $progress['pending'] }}</strong>
        </span>
        <span class="text-gray-600">
            📋 Total: <strong id="total-count">{{ $progress['total'] }}</strong>
        </span>
    </div>
</div>