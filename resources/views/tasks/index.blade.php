@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">{{ $list->name }}</h1>
        <a href="{{ route('tasks.create', $list) }}" class="bg-blue-500 text-white px-4 py-2 rounded">
            + Tambah Tugas
        </a>
    </div>
    
    {{-- SRS-007: Progress Bar --}}
    @include('lists.partials.progress', ['progress' => $progress])
    
    {{-- SRS-006: Filter --}}
    @include('tasks.partials.filter')
    
    {{-- SRS-006: Daftar Tugas --}}
    <div id="task-list">
        @forelse($tasks as $task)
            @include('tasks.partials.task-item', ['task' => $task])
        @empty
            <div class="text-center text-gray-500 py-8">
                Belum ada tugas. <a href="{{ route('tasks.create', $list) }}" class="text-blue-500">Tambah sekarang</a>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
// SRS-006 & SRS-007: Toggle tanpa reload + update progress real-time
document.querySelectorAll('.toggle-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const taskId = form.closest('.task-item').dataset.taskId;
        const button = form.querySelector('button');
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update tampilan task
                const taskItem = document.querySelector(`[data-task-id="${taskId}"]`);
                taskItem.classList.toggle('bg-gray-100', data.is_completed);
                taskItem.classList.toggle('opacity-75', data.is_completed);
                
                // Update tombol
                if (data.is_completed) {
                    button.classList.add('bg-green-500', 'border-green-500', 'text-white');
                    button.innerHTML = '✓';
                } else {
                    button.classList.remove('bg-green-500', 'border-green-500', 'text-white');
                    button.innerHTML = '';
                }
                
                // SRS-007: Update progress bar real-time
                updateProgress(data.progress);
            }
        } catch (error) {
            console.error('Error:', error);
            form.submit(); // Fallback ke submit biasa
        }
    });
});

// SRS-007: Update progress bar
function updateProgress(progress) {
    document.getElementById('progress-percentage').textContent = progress.percentage + '%';
    document.getElementById('completed-count').textContent = progress.completed;
    document.getElementById('pending-count').textContent = progress.pending;
    document.getElementById('total-count').textContent = progress.total;
    
    const bar = document.getElementById('progress-bar');
    bar.style.width = progress.percentage + '%';
    
    // Update warna
    bar.className = 'h-4 rounded-full transition-all duration-500 ' + 
        (progress.percentage >= 80 ? 'bg-green-500' :
         progress.percentage >= 50 ? 'bg-yellow-500' :
         progress.percentage >= 25 ? 'bg-orange-500' : 'bg-red-500');
}
</script>
@endpush