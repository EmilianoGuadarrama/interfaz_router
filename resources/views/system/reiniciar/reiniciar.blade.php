@extends('layouts.dashboard')
@section('page-title', 'Reiniciar')

@section('content')
<div class="page">

    <div class="page-header">
        <h1 class="page-title">Reiniciar</h1>
        <p class="page-subtitle">Reiniciar el sistema operativo de su dispositivo.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('reiniciar.run') }}"
          onsubmit="return confirm('¿Estás seguro que deseas reiniciar el dispositivo?')">
        @csrf
        <button type="submit" class="btn-reiniciar">REINICIAR</button>
    </form>

</div>
@endsection

@push('styles')
<style>
.page { color: #e2e8f0; }
.page-header { margin-bottom: 28px; }
.page-title { font-size: 26px; font-weight: 600; color: #fff; margin: 0 0 6px; }
.page-subtitle { font-size: 13px; color: #8899aa; margin: 0; }

.alert { padding: 10px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; border: 1px solid; }
.alert-success { background: rgba(34,197,94,0.12); border-color: rgba(34,197,94,0.35); color: #86efac; }
.alert-error   { background: rgba(239,68,68,0.12); border-color: rgba(239,68,68,0.35); color: #fca5a5; }

.btn-reiniciar {
    background: #2563eb;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 10px 24px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-reiniciar:hover { background: #1d4ed8; }
</style>
@endpush