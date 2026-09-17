{{-- SRS-004 & SRS-005: field bersama untuk create & edit --}}
<div class="mb-3">
    <label class="form-label">Judul Tugas <span class="text-danger">*</span></label>
    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
           value="{{ old('title', $task->title ?? '') }}" required>
    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="mb-3">
    <label class="form-label">Deskripsi <span class="text-muted">(opsional)</span></label>
    <textarea name="description" rows="3"
              class="form-control @error('description') is-invalid @enderror">{{ old('description', $task->description ?? '') }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Prioritas</label>
        <select name="priority" class="form-select @error('priority') is-invalid @enderror">
            @foreach (['high' => 'Tinggi', 'medium' => 'Sedang', 'low' => 'Rendah'] as $value => $label)
                <option value="{{ $value }}" @selected(old('priority', $task->priority ?? 'medium') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Tenggat Waktu <span class="text-muted">(opsional)</span></label>
        <input type="datetime-local" name="due_date"
               class="form-control @error('due_date') is-invalid @enderror"
               value="{{ old('due_date', isset($task) && $task->due_date ? $task->due_date->format('Y-m-d\TH:i') : '') }}">
        @error('due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
