{{-- SRS-006: Form Filter --}}
<form method="GET" action="{{ route('tasks.index', $list) }}" 
      class="bg-gray-50 p-4 rounded-lg mb-4 flex flex-wrap gap-3 items-end">
    
    {{-- Filter Status --}}
    <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <select name="status" class="border rounded px-3 py-2">
            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>
                Semua
            </option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                ✅ Selesai
            </option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                ⏳ Belum Selesai
            </option>
        </select>
    </div>
    
    {{-- Filter Priority --}}
    <div>
        <label class="block text-sm font-medium mb-1">Prioritas</label>
        <select name="priority" class="border rounded px-3 py-2">
            <option value="">Semua Prioritas</option>
            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>
                🔴 Tinggi
            </option>
            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>
                🟡 Sedang
            </option>
            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>
                🟢 Rendah
            </option>
        </select>
    </div>
    
    {{-- Filter Due Date --}}
    <div>
        <label class="block text-sm font-medium mb-1">Tenggat Waktu</label>
        <select name="due" class="border rounded px-3 py-2">
            <option value="">Semua</option>
            <option value="overdue" {{ request('due') == 'overdue' ? 'selected' : '' }}>
                ⚠️ Terlambat
            </option>
            <option value="today" {{ request('due') == 'today' ? 'selected' : '' }}>
                📅 Hari Ini
            </option>
            <option value="week" {{ request('due') == 'week' ? 'selected' : '' }}>
                📆 Minggu Ini
            </option>
        </select>
    </div>
    
    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
        🔍 Filter
    </button>
    
    <a href="{{ route('tasks.index', $list) }}" 
       class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
        Reset
    </a>
</form>

{{-- Info jumlah hasil --}}
@if(request()->anyFilled(['status', 'priority', 'due']))
    <div class="text-sm text-gray-600 mb-3">
        Menampilkan <strong>{{ $tasks->count() }}</strong> tugas
        @if(request('status') == 'completed') dengan status <strong>Selesai</strong> @endif
        @if(request('status') == 'pending') dengan status <strong>Belum Selesai</strong> @endif
        @if(request('priority')) dengan prioritas <strong>{{ request('priority') }}</strong> @endif
    </div>
@endif