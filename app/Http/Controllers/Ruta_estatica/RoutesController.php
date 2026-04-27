<?php

namespace App\Http\Controllers\Ruta_estatica;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RouterSshService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RoutesController extends Controller
{
    protected RouterSshService $router;

    public function __construct(RouterSshService $router)
    {
        $this->router = $router;
    }

    private function getAllRoutesFast()
    {
        $routes = ['ipv4' => [], 'ipv6' => []];
        try {
            $cmd = "keys=\$(uci show network | grep -E '=(route|route6)$' | cut -d'=' -f1); [ -n \"\$keys\" ] && uci show \$keys";
            $result = $this->router->execute([$cmd]);

            if ($result['success'] && !empty($result['output'])) {
                $lines = explode("\n", trim($result['output']));
                $ipv4_keys = []; $ipv6_keys = []; $data = [];

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    $parts = explode('=', $line, 2);
                    if (count($parts) < 2) continue;

                    $left = str_replace('network.', '', $parts[0]);
                    $val = trim($parts[1], "'\"");

                    if (strpos($left, '.') === false) {
                        if ($val === 'route') $ipv4_keys[$left] = true;
                        elseif ($val === 'route6') $ipv6_keys[$left] = true;
                    } else {
                        $propParts = explode('.', $left, 2);
                        if (count($propParts) === 2) {
                            $data[$propParts[0]][$propParts[1]] = $val;
                        }
                    }
                }

                foreach ($ipv4_keys as $key => $true) {
                    $route = $data[$key] ?? [];
                    $route['key'] = $key;
                    $routes['ipv4'][] = $route;
                }
                foreach ($ipv6_keys as $key => $true) {
                    $route = $data[$key] ?? [];
                    $route['key'] = $key;
                    $routes['ipv6'][] = $route;
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error en lectura de rutas: ' . $e->getMessage());
        }
        return $routes;
    }

    /* =========================================================
       RUTAS IPv4
    ========================================================= */

    public function staticIpv4(Request $request)
    {
        // Si el usuario presionó el botón Refrescar, borramos la memoria
        if ($request->query('refresh')) {
            Cache::forget('all_network_routes');
            return redirect()->route('network.routes.static.ipv4');
        }

        // Caché de 24 horas (86400 segundos) para que navegue ultra rápido
        $allRoutes = Cache::remember('all_network_routes', 86400, function () {
            return $this->getAllRoutesFast();
        });

        $routes = $allRoutes['ipv4'] ?? [];
        return view('network.rutas_estaticas.estatica', compact('routes'));
    }

    public function storeStaticIpv4(Request $request)
    {
        $validated = $request->validate([
            'interface' => 'required|string', 'target' => 'required|string', 'netmask' => 'nullable|string', 'gateway' => 'nullable|string', 'metric' => 'nullable|integer', 'mtu' => 'nullable|integer', 'type' => 'nullable|string', 'table' => 'nullable|string', 'source' => 'nullable|string', 'onlink' => 'nullable|boolean',
        ]);

        try {
            $commands = ["uci add network route"];
            foreach ($validated as $key => $value) {
                if ($key === 'onlink') $value = $value ? '1' : '0';
                if ($value !== null && $value !== '') $commands[] = "uci set network.@route[-1].{$key}='{$value}'";
            }
            $commands[] = "uci commit network";
            $commands[] = "ubus call network reload";

            $singleCommand = implode(' ; ', $commands);
            $result = $this->router->execute([$singleCommand]);

            Cache::forget('all_network_routes'); // Forzar lectura en la próxima recarga

            return back()->with(['result_success' => $result['success'], 'result_output' => $result['output'], 'result_title' => $result['success'] ? 'Ruta IPv4 agregada' : 'Error al guardar']);
        } catch (\Throwable $e) {
            return back()->with(['result_success' => false, 'result_output' => $e->getMessage(), 'result_title' => 'Error']);
        }
    }

    public function destroyStaticIpv4(Request $request)
    {
        $validated = $request->validate(['route_key' => 'required|string']);
        try {
            $key = escapeshellarg($validated['route_key']);
            $singleCommand = implode(' ; ', ["uci delete network.{$key}", "uci commit network", "ubus call network reload"]);
            $result = $this->router->execute([$singleCommand]);

            Cache::forget('all_network_routes'); // Forzar lectura

            return back()->with(['result_success' => $result['success'], 'result_output' => $result['output'], 'result_title' => $result['success'] ? 'Ruta IPv4 eliminada' : 'Error']);
        } catch (\Throwable $e) {
            return back()->with(['result_success' => false, 'result_output' => $e->getMessage(), 'result_title' => 'Error']);
        }
    }

    /* =========================================================
       RUTAS IPv6
    ========================================================= */

    public function staticIpv6(Request $request)
    {
        // Botón Refrescar
        if ($request->query('refresh')) {
            Cache::forget('all_network_routes');
            return redirect()->route('network.routes.static.ipv6');
        }

        $allRoutes = Cache::remember('all_network_routes', 86400, function () {
            return $this->getAllRoutesFast();
        });

        $routes = $allRoutes['ipv6'] ?? [];
        return view('network.rutas_estaticas.estatica_ipv6', compact('routes'));
    }

    public function storeStaticIpv6(Request $request)
    {
        $validated = $request->validate([
            'interface' => 'required|string', 'target' => 'required|string', 'gateway' => 'nullable|string', 'metric' => 'nullable|integer', 'mtu' => 'nullable|integer', 'type' => 'nullable|string', 'table' => 'nullable|string', 'source' => 'nullable|string', 'onlink' => 'nullable|boolean',
        ]);

        try {
            $commands = ["uci add network route6"];
            foreach ($validated as $key => $value) {
                if ($key === 'onlink') $value = $value ? '1' : '0';
                if ($value !== null && $value !== '') $commands[] = "uci set network.@route6[-1].{$key}='{$value}'";
            }
            $commands[] = "uci commit network";
            $commands[] = "ubus call network reload";

            $singleCommand = implode(' ; ', $commands);
            $result = $this->router->execute([$singleCommand]);

            Cache::forget('all_network_routes');

            return back()->with(['result_success' => $result['success'], 'result_output' => $result['output'], 'result_title' => $result['success'] ? 'Ruta IPv6 agregada' : 'Error']);
        } catch (\Throwable $e) {
            return back()->with(['result_success' => false, 'result_output' => $e->getMessage(), 'result_title' => 'Error']);
        }
    }

    public function destroyStaticIpv6(Request $request)
    {
        $validated = $request->validate(['route_key' => 'required|string']);
        try {
            $key = escapeshellarg($validated['route_key']);
            $singleCommand = implode(' ; ', ["uci delete network.{$key}", "uci commit network", "ubus call network reload"]);
            $result = $this->router->execute([$singleCommand]);

            Cache::forget('all_network_routes');

            return back()->with(['result_success' => $result['success'], 'result_output' => $result['output'], 'result_title' => $result['success'] ? 'Ruta IPv6 eliminada' : 'Error']);
        } catch (\Throwable $e) {
            return back()->with(['result_success' => false, 'result_output' => $e->getMessage(), 'result_title' => 'Error']);
        }
    }

    /* =========================================================
       ESTADO DE CONEXIÓN
    ========================================================= */

    public function checkConnection()
    {
        return response()->json([
            // Extendemos este caché a 60s para que no bloquee tu servidor en segundo plano
            'connected' => Cache::remember('router_status', 60, function () {
                return $this->router->isConnected();
            })
        ]);
    }
}