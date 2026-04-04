@extends('layouts.dashboard')
@section('page-title', 'Reiniciar')

@section('content')

<p style="font-size:13px; color:var(--text-muted); margin-bottom:20px;">
    Reiniciar el sistema operativo de su dispositivo.
</p>

@if(session('success'))
    <div class="alert alert-success mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
@endif

<div class="panel-card" style="padding:28px;">
    <form method="POST" action="{{ route('reiniciar.run') }}"
          onsubmit="return confirm('¿Estás seguro que deseas reiniciar el dispositivo?')">
        @csrf
        <button type="submit" class="btn btn-main btn-sm">REINICIAR</button>
    </form>
</div>

@endsection