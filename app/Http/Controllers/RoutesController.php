<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RouterSshService;
use Illuminate\Support\Facades\Log;

class RoutesController extends Controller
{
    protected RouterSshService $router;

    public function __construct(RouterSshService $router)
    {
        $this->router = $router;
    }

    /* =========================================================
       RUTAS IPv4
    ========================================================= */

    public function staticIpv4()
    {
        $routes = [];

        try {
            // OPTIMIZACIÓN: 1 sola llamada SSH para traer todo y procesarlo en memoria (super rápido)
            $result = $this->router->execute(["uci show network"]);
            
            if ($result['success'] && !empty($result['output'])) {
                $blocks = [];
                
                // 1. Agrupar todo el texto recibido por bloques (ej. @route[0] o cfg123)
                foreach (explode("\n", trim($result['output'])) as $line) {
                    if (strpos($line, '=') !== false) {
                        [$fullKey, $value] = explode('=', $line, 2);
                        $fullKey = trim($fullKey);
                        $value = trim($value, "'\" \r\n");

                        if (preg_match('/^network\.([^.]+)(?:\.(.+))?$/', $fullKey, $matches)) {
                            $blockName = $matches[1];
                            $propName = $matches[2] ?? 'TYPE'; // Si no tiene propiedad, es la definición (ej. =route)
                            $blocks[$blockName][$propName] = $value;
                        }
                    }
                }

                // 2. Filtrar solo los bloques que sean tipo 'route' (IPv4)
                foreach ($blocks as $key => $props) {
                    if (isset($props['TYPE']) && $props['TYPE'] === 'route') {
                        $routes[] = [
                            'key' => $key,
                            'interface' => $props['interface'] ?? '',
                            'target' => $props['target'] ?? '',
                            'netmask' => $props['netmask'] ?? '',
                            'gateway' => $props['gateway'] ?? '',
                            'metric' => $props['metric'] ?? '',
                            'mtu' => $props['mtu'] ?? '',
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error leyendo rutas IPv4: ' . $e->getMessage());
        }

        return view('network.rutas_estaticas.estatica', compact('routes'));
    }

    public function storeStaticIpv4(Request $request)
    {
        $validated = $request->validate([
            'interface' => 'required|string',
            'target' => 'required|string',
            'netmask' => 'nullable|string',
            'gateway' => 'nullable|string',
            'metric' => 'nullable|integer',
            'mtu' => 'nullable|integer',
        ]);

        try {
            $this->router->execute(["uci add network route 2>&1"]);
            $setCommands = [];
            foreach ($validated as $key => $value) {
                if (!empty($value)) {
                    $setCommands[] = "uci set network.@route[-1].{$key}='{$value}' 2>&1";
                }
            }
            $setCommands[] = "uci commit network 2>&1";
            $setCommands[] = "/etc/init.d/network restart 2>&1";

            $result = $this->router->execute($setCommands);
            return back()->with(['result_success' => $result['success'], 'result_output' => $result['output'], 'result_title' => $result['success'] ? 'Ruta IPv4 agregada' : 'Error al guardar ruta']);
        } catch (\Throwable $e) {
            return back()->with(['result_success' => false, 'result_output' => $e->getMessage(), 'result_title' => 'Error de ejecución']);
        }
    }

    public function destroyStaticIpv4(Request $request)
    {
        $validated = $request->validate(['route_key' => 'required|string']);
        try {
            $key = escapeshellarg($validated['route_key']);
            $result = $this->router->execute(["uci delete network.{$key} 2>&1", "uci commit network 2>&1", "/etc/init.d/network restart 2>&1"]);
            return back()->with(['result_success' => $result['success'], 'result_output' => $result['output'], 'result_title' => $result['success'] ? 'Ruta IPv4 eliminada' : 'Error al eliminar']);
        } catch (\Throwable $e) {
            return back()->with(['result_success' => false, 'result_output' => $e->getMessage(), 'result_title' => 'Error de ejecución']);
        }
    }

    /* =========================================================
       RUTAS IPv6
    ========================================================= */

    public function staticIpv6()
    {
        $routes = [];

        try {
            // OPTIMIZACIÓN: 1 sola llamada SSH
            $result = $this->router->execute(["uci show network"]);
            
            if ($result['success'] && !empty($result['output'])) {
                $blocks = [];

                foreach (explode("\n", trim($result['output'])) as $line) {
                    if (strpos($line, '=') !== false) {
                        [$fullKey, $value] = explode('=', $line, 2);
                        $fullKey = trim($fullKey);
                        $value = trim($value, "'\" \r\n");

                        if (preg_match('/^network\.([^.]+)(?:\.(.+))?$/', $fullKey, $matches)) {
                            $blockName = $matches[1];
                            $propName = $matches[2] ?? 'TYPE';
                            $blocks[$blockName][$propName] = $value;
                        }
                    }
                }

                // Filtrar solo los bloques que sean tipo 'route6' (IPv6)
                foreach ($blocks as $key => $props) {
                    if (isset($props['TYPE']) && $props['TYPE'] === 'route6') {
                        $routes[] = [
                            'key' => $key,
                            'interface' => $props['interface'] ?? '',
                            'target' => $props['target'] ?? '',
                            'gateway' => $props['gateway'] ?? '',
                            'metric' => $props['metric'] ?? '',
                            'mtu' => $props['mtu'] ?? '',
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error leyendo rutas IPv6: ' . $e->getMessage());
        }

        return view('network.rutas_estaticas.estatica_ipv6', compact('routes'));
    }

    public function storeStaticIpv6(Request $request)
    {
        $validated = $request->validate([
            'interface' => 'required|string',
            'target' => 'required|string', 
            'gateway' => 'nullable|string',
            'metric' => 'nullable|integer',
            'mtu' => 'nullable|integer',
        ]);

        try {
            $this->router->execute(["uci add network route6 2>&1"]);
            $setCommands = [];
            foreach ($validated as $key => $value) {
                if (!empty($value)) {
                    $setCommands[] = "uci set network.@route6[-1].{$key}='{$value}' 2>&1";
                }
            }
            $setCommands[] = "uci commit network 2>&1";
            $setCommands[] = "/etc/init.d/network restart 2>&1";

            $result = $this->router->execute($setCommands);
            return back()->with(['result_success' => $result['success'], 'result_output' => $result['output'], 'result_title' => $result['success'] ? 'Ruta IPv6 agregada' : 'Error al guardar ruta']);
        } catch (\Throwable $e) {
            return back()->with(['result_success' => false, 'result_output' => $e->getMessage(), 'result_title' => 'Error de ejecución']);
        }
    }

    public function destroyStaticIpv6(Request $request)
    {
        $validated = $request->validate(['route_key' => 'required|string']);
        try {
            $key = escapeshellarg($validated['route_key']);
            $result = $this->router->execute(["uci delete network.{$key} 2>&1", "uci commit network 2>&1", "/etc/init.d/network restart 2>&1"]);
            return back()->with(['result_success' => $result['success'], 'result_output' => $result['output'], 'result_title' => $result['success'] ? 'Ruta IPv6 eliminada' : 'Error al eliminar']);
        } catch (\Throwable $e) {
            return back()->with(['result_success' => false, 'result_output' => $e->getMessage(), 'result_title' => 'Error de ejecución']);
        }
    }

    /* =========================================================
       ESTADO DE CONEXIÓN
    ========================================================= */

    public function checkConnection()
    {
        try {
            // Un comando muy rápido que solo devuelve 'ok' si el router responde por SSH
            $result = $this->router->execute(["echo 'ok'"]);
            return response()->json(['connected' => $result['success']]);
        } catch (\Throwable $e) {
            return response()->json(['connected' => false]);
        }
    }
}