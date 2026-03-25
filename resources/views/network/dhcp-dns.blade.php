@extends('layouts.dashboard')

@section('title', 'DHCP y DNS')

@section('content')
    <div class="container-fluid">
        <h2 class="page-title mb-4">DHCP y DNS</h2>

        <div class="panel-card">
            <form action="{{ route('network.dhcpdns.update') }}" method="POST">
                @csrf

                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label text-light">Inicio del rango DHCP</label>
                        <input type="number" name="start" class="form-control" value="{{ old('start', 100) }}" required>
                        @error('start') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-light">Límite de clientes</label>
                        <input type="number" name="limit" class="form-control" value="{{ old('limit', 150) }}" required>
                        @error('limit') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-light">Tiempo de concesión</label>
                        <input type="text" name="leasetime" class="form-control" value="{{ old('leasetime', '12h') }}" placeholder="Ej. 12h" required>
                        @error('leasetime') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-light">Dominio local</label>
                        <input type="text" name="domain" class="form-control" value="{{ old('domain', 'lan') }}" placeholder="Ej. lan">
                        @error('domain') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-light">DNS principal</label>
                        <input type="text" name="dns1" class="form-control" value="{{ old('dns1', '8.8.8.8') }}" required>
                        @error('dns1') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-light">DNS secundario</label>
                        <input type="text" name="dns2" class="form-control" value="{{ old('dns2', '8.8.4.4') }}">
                        @error('dns2') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-main" type="submit">Guardar cambios</button>
                </div>
            </form>
        </div>

        @if(session('result_title'))
            <div class="panel-card mt-4">
                <h4 class="{{ session('result_success') ? 'text-success' : 'text-danger' }}">
                    {{ session('result_title') }}
                </h4>
                <p class="text-light">
                    {{ session('result_success') ? 'Los cambios fueron enviados correctamente al router.' : 'Se detectaron errores al ejecutar los comandos en el router.' }}
                </p>
                <pre class="p-3 rounded" style="background:#0f172a;color:#e2e8f0;white-space:pre-wrap;">{{ session('result_output') }}</pre>
            </div>
        @endif
    </div>
@endsection
