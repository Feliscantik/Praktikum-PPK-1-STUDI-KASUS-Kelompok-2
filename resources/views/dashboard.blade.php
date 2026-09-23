@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Halo, {{ auth()->user()->name }}</h3>
    <a href="{{ route('lists.create') }}" class="btn btn-primary">+ Buat Daftar Baru</a>
</div>

<h5 class="text-muted">Daftar Milik Saya</h5>
<div class="row g-3 mb-4">
    @forelse ($ownedLists as $list)
        <div class="col-md-4">
            @include('lists.partials.card', ['list' => $list, 'badge' => 'Owner'])
        </div>
    @empty
        <div class="col-12"><p class="text-muted">Belum ada daftar. Buat daftar pertama Anda.</p></div>
    @endforelse
</div>

{{-- SRS-003: daftar milik orang lain yang dibagikan ke pengguna ini --}}
<h5 class="text-muted">Dibagikan Kepada Saya</h5>
<div class="row g-3">
    @forelse ($sharedLists as $list)
        <div class="col-md-4">
            @include('lists.partials.card', [
                'list' => $list,
                'badge' => 'Collaborator · ' . $list->owner->name,
            ])
        </div>
    @empty
        <div class="col-12"><p class="text-muted">Belum ada daftar yang dibagikan kepada Anda.</p></div>
    @endforelse
</div>
@endsection