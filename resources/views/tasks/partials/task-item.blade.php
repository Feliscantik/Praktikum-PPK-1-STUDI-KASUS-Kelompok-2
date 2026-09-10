{{-- SRS-006: Item tugas dengan toggle --}}
<div class="task-item border rounded-lg p-4 mb-3 flex items-start gap-3 
            {{ $task->is_completed ? 'bg-gray-100 opacity-75' : 'bg-white' }}"
     data-task-id="{{ $task->id }}">
    
    {{-- Toggle Checkbox --}}
    <form action="{{ route('tasks.toggle', $task) }}" method="POST" class="toggle-form">
        @csrf
        @method('PATCH')
        <button type="submit" 
                class="w-6 h-6 rounded-full border-2 flex items-center justify-center
                       {{ $task->is_completed 
                            ? 'bg-green-500 border-green-500 text-white' 
                            : 'border-gray-400 hover:border-green-500' }}">
            @if($task->is_completed)
                ✓
            @endif
        </button>
    </form>
    
    {{-- Konten Tugas --}}
    <div class="flex-1">
        <h3 class="font-semibold {{ $task->is_completed ? 'line-through text-gray-500' : '' }}">
            {{ $task->title }}
        </h3>
        
        @if($task->description)
            <p class="text-sm text-gray-600 mt-1">{{ $task->description }}</p>
        @endif
        
        <div class="flex gap-3 mt-2 text-xs">
            {{-- Priority Badge --}}
            <span class="px-2 py-1 rounded
                @if($task->priority === 'high') bg-red-100 text-red-700
                @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-700
                @else bg-green-100 text-green-700 @endif">
                {{ ucfirst($task->priority) }}
            </span>
            
            {{-- Due Date dengan highlight (SRS-005) --}}
            @if($task->due_date)
                <span class="px-2 py-1 rounded
                    @if($task->is_completed) bg-gray-100 text-gray-600
                    @elseif($task->due_date < now()) bg-red-100 text-red-700 font-bold
                    @elseif($task->due_date < now()->addDay()) bg-orange-100 text-orange-700
                    @else bg-blue-100 text-blue-700 @endif">
                    📅 {{ $task->due_date->format('d M Y, H:i') }}
                    @if(!$task->is_completed && $task->due_date < now())
                        (Terlambat!)
                    @endif
                </span>
            @endif
        </div>
    </div>
</div>