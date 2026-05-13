@extends('layouts.dashboard')

@section('title', 'Diagnóstico de Red')
@section('page-title', 'Utilidades de red')

@section('content')
<div class="content-area">
    <div class="panel-card">
        <div class="row g-4">
            
            <!-- PING -->
            <div class="col-md-4">
                <div class="stats-card p-4 h-100">
                    <h5><i class="bi bi-activity"></i> PING</h5>
                    <p style="color: var(--text-soft); font-size: 0.9rem;">Verifica la conectividad.</p>
                    <input type="text" id="pingHost" class="form-control mb-3" placeholder="Ej: google.com">
                    <button class="btn btn-main w-100" onclick="runPing()">Ejecutar PING</button>
                </div>
            </div>

            <!-- TRACEROUTE -->
            <div class="col-md-4">
                <div class="stats-card p-4 h-100">
                    <h5><i class="bi bi-signpost-split"></i> TRACEROUTE</h5>
                    <p style="color: var(--text-soft); font-size: 0.9rem;">Rastrea la ruta de los paquetes.</p>
                    <input type="text" id="traceHost" class="form-control mb-3" placeholder="Ej: google.com">
                    <button class="btn btn-main w-100" onclick="runTraceroute()">Ejecutar TRACEROUTE</button>
                </div>
            </div>

            <!-- NSLOOKUP -->
            <div class="col-md-4">
                <div class="stats-card p-4 h-100">
                    <h5><i class="bi bi-globe2"></i> NSLOOKUP</h5>
                    <p style="color: var(--text-soft); font-size: 0.9rem;">Consulta nombres de dominio.</p>
                    <input type="text" id="dnsHost" class="form-control mb-3" placeholder="Ej: google.com">
                    <button class="btn btn-main w-100" onclick="runNslookup()">Ejecutar NSLOOKUP</button>
                </div>
            </div>

        </div>

        <hr style="border-color: var(--border-soft); margin: 30px 0;">

        <h5>Resultado:</h5>
        <div class="p-3 mt-3" style="min-height: 200px; background: rgba(0,0,0,0.2); border: 1px solid var(--border-soft); border-radius: 12px; overflow-x: auto;">
            <pre id="result" class="m-0" style="color: var(--text-soft); font-family: monospace;">Aquí aparecerá el resultado...</pre>
        </div>
    </div>
</div>

<script>
function ejecutar(url, host){
    if (!host) {
        alert("Ingresa un host o IP");
        return;
    }

    document.getElementById("result").innerText = "Ejecutando...";

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ host: host })
    })
    .then(res => {
        if (!res.ok) {
            throw new Error("Error en la petición");
        }
        return res.text();
    })
    .then(data => {
        document.getElementById("result").innerText = data;
    })
    .catch(error => {
        document.getElementById("result").innerText = "Error: " + error;
    });
}

// FUNCIONES
function runPing(){
    ejecutar('{{ route('red.diagnostico.ping') }}', document.getElementById("pingHost").value);
}

function runTraceroute(){
    ejecutar('{{ route('red.diagnostico.traceroute') }}', document.getElementById("traceHost").value);
}

function runNslookup(){
    ejecutar('{{ route('red.diagnostico.nslookup') }}', document.getElementById("dnsHost").value);
}
</script>
@endsection