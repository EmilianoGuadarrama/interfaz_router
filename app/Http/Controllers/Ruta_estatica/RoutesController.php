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

    /* =========================================================
       LECTURA ULTRA RÁPIDA Y SEGURA
    ========================================================= */
    private function getAllRoutesFast()
    {
        $routes = ['ipv4' => [], 'ipv6' => []];
        try {
            // Un solo comando puro que no falla sin importar cómo se crearon las rutas
            $result = $this->router->execute(["uci show network"]);

            if ($result['success'] && !empty($result['output'])) {
                $lines = explode("\n", trim($result['output']));
                $ipv4_keys = [];
                $ipv6_keys = [];
                $data = [];

                // Paso 1: Encontrar las llaves que sean de tipo route o route6
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (preg_match('/^network\.([a-zA-Z0-9_@\[\]\-]+)=route$/', $line, $match)) {
                        $ipv4_keys[$match[1]] = true;
                    } elseif (preg_match('/^network\.([a-zA-Z0-9_@\[\]\-]+)=route6$/', $line, $match)) {
                        $ipv6_keys[$match[1]] = true;
                    }
                }

                // Paso 2: Extraer propiedades solo de las llaves encontradas
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (preg_match('/^network\.([a-zA-Z0-9_@\[\]\-]+)\.([a-zA-Z0-9_]+)=(.+)$/', $line, $match)) {
                        $key = $match[1];
                        $prop = $match[2];
                        $val = trim($match[3], "'\"");

                        if (isset($ipv4_keys[$key]) || isset($ipv6_keys[$key])) {
                            $data[$key][$prop] = $val;
                        }
                    }
                }

                // Paso 3: Empaquetar
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
        // Botón Refrescar
        if ($request->query('refresh')) {
            Cache::forget('all_network_routes');
            return redirect()->route('red.routes.static.ipv4');
        }

        $allRoutes = Cache::remember('all_network_routes', 86400, function () {
            return $this->getAllRoutesFast();
        });

        $routes = $allRoutes['ipv4'] ?? [];
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
            'type' => 'nullable|string',
            'table' => 'nullable|string',
            'source' => 'nullable|string',
            'onlink' => 'nullable|boolean',
        ]);

        try {
            $commands = ["uci add network route"];
            foreach ($validated as $key => $value) {
                if ($key === 'onlink')
                    $value = $value ? '1' : '0';
                if ($value !== null && $value !== '')
                    $commands[] = "uci set network.@route[-1].{$key}='{$value}'";
            }
            $commands[] = "uci commit network";
            $commands[] = "ubus call network reload";

            $singleCommand = implode(' ; ', $commands);
            $result = $this->router->execute([$singleCommand]);

            Cache::forget('all_network_routes');

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

            Cache::forget('all_network_routes');

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
            return redirect()->route('red.routes.static.ipv6');
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
            'interface' => 'required|string',
            'target' => 'required|string',
            'gateway' => 'nullable|string',
            'metric' => 'nullable|integer',
            'mtu' => 'nullable|integer',
            'type' => 'nullable|string',
            'table' => 'nullable|string',
            'source' => 'nullable|string',
            'onlink' => 'nullable|boolean',
        ]);

        try {
            $commands = ["uci add network route6"];
            foreach ($validated as $key => $value) {
                if ($key === 'onlink')
                    $value = $value ? '1' : '0';
                if ($value !== null && $value !== '')
                    $commands[] = "uci set network.@route6[-1].{$key}='{$value}'";
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
            'connected' => Cache::remember('router_status', 60, function () {
                return $this->router->isConnected();
            })
        ]);
    }

    /* =========================================================
       ESTADO DE INTERNET
    ========================================================= */

    public function checkInternet()
    {
        try {
            $command = "ping -c 2 -W 2 8.8.8.8 > /dev/null 2>&1 && echo 'ONLINE' || echo 'OFFLINE'";
            $result = $this->router->execute([$command]);

            $isOnline = trim($result['output']) === 'ONLINE';

            return response()->json([
                'success' => true,
                'has_internet' => $isOnline,
                'message' => $isOnline
                    ? 'Conectado a Internet'
                    : 'Sin acceso a Internet'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'has_internet' => false,
                'message' => 'Error al comunicarse con el router.'
            ]);
        }
    }
}