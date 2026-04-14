@extends('layouts.dashboard')
@section('page-title', 'Tareas programadas')
@section('content')
    <div class="container-fluid">

        @if(session('result_title'))
            <div class="alert {{ session('result_success') ? 'alert-success' : 'alert-danger' }} d-flex align-items-center mb-4">
                <i class="bi {{ session('result_success') ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-2"></i>
                <strong>{{ session('result_title') }}</strong>
            </div>
        @endif
        <div class="panel-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Configuración de tareas programadas</h5>
            </div>

            <form action="{{ route('tasks.update') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="content" class="form-label">Contenido</label>
                    <textarea
                        name="content"
                        id="content"
                        rows="18"
                        class="form-control @error('content') is-invalid @enderror"
                        style="font-family: monospace;"
                    >{{ old('content', $content) }}</textarea>

                    @error('content')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-main">
                        <i class="bi bi-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
