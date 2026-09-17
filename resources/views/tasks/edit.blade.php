@extends('layouts.app')

@section('title', 'Edit Tugas')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-1">Edit Tugas</h5>
                <p class="text-muted small">Daftar: {{ $taskList->name }}</p>

                <form action="{{ route('tasks.update', $task) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('tasks._form')
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="{{ route('lists.show', $taskList) }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
