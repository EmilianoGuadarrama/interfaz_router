<?php

namespace App\Http\Controllers\Ruta_estatica;

use App\Http\Controllers\Controller;
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
       LECTURA DIRECTA, LIGERA Y A PRUEBA DE FALLOS
    ========================================================= */
    private function getAllRoutesFast()
    {
        $routes = ['ipv4' => [], 'ipv6' => []];
        try {
            // El truco para no saturar la memoria: le pedimos al router ÚNICAMENTE las rutas.
            $result = $this->router->execute(["uci show network | grep 'route'"]);

            if ($result['success'] && !empty($result['output'])) {
                $lines = explode("\n", trim($result['output']));
                $ipv4_keys = []; 
                $ipv6_keys = []; 
                $data = [];

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;

                    // Detectar si es la declaración de una ruta IPv4 (ej. network.@route[0]=route o network.miruta=route)
                    if (preg_match('/^network\.([^.]+)=route$/', $line, $match)) {
                        $ipv4_keys[$match[1]] = true;
                    } 
                    // Detectar si es IPv6
                    elseif (preg_match('/^network\.([^.]+)=route6$/', $line, $match)) {
                        $ipv6_keys[$match[1]] = true;
                    } 
                    // Detectar propiedades (ej. network.@route[0].target='192.168.50.0')
                    elseif (preg_match('/^network\.([^.]+)\.([^=]+)=(.*)$/', $line, $match)) {
                        $key = $match[1];
                        $prop = $match[2];
                        $val = trim($match[3], "'\"");
                        $data[$key][$prop] = $val;
                    }
                }

                // Empaquetar IPv4
                foreach ($ipv4_keys as $key => $true) {
                    $route = $data[$key] ?? [];
                    $route['key'] = $key;
                    // Si el router no manda interfaz, ponemos "Sin especificar" igual que en LuCI
                    if (empty($route['interface'])) {
                        $route['interface'] = 'Sin especificar';
                    }
                    $routes['ipv4'][] = $route;
                }

                // Empaquetar IPv6
                foreach ($ipv6_keys as $key => $true) {
                    $route = $data[$key] ?? [];
                    $route['key'] = $key;
                    if (empty($route['interface'])) {
                        $route['interface'] = 'Sin especificar';
                    }
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
        // Eliminado el caché. Ahora lee en tiempo real siempre (tarda milisegundos de todas formas)
        $allRoutes = $this->getAllRoutesFast();
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
                if ($key === 'onlink') $value = $value ? '1' : '0';
                
                // Si la interfaz viene como "Sin especificar", no la enviamos al router
                if ($key === 'interface' && strtolower($value) === 'sin especificar') {
                    continue; 
                }

                if ($value !== null && $value !== '') {
                    $commands[] = "uci set network.@route[-1].{$key}='{$value}'";
                }
            }
            $commands[] = "uci commit network";
            $commands[] = "ubus call network reload";

            $singleCommand = implode(' ; ', $commands);
            $result = $this->router->execute([$singleCommand]);

            return back()->with(['result_success' => $result['success'], 'result_output' => $result['output'], 'result_title' => $result['success'] ? 'Ruta IPv4 agregada' : 'Error al guardar']);
        } catch (\Throwable $e) {
            return back()->with(['result_success' => false, 'result_output' => $e->getMessage(), 'result_title' => 'Error']);
        }
    }

    public function updateStaticIpv4(Request $request)
    {
        $validated = $request->validate([
            'route_key' => 'required|string',
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
            $key = escapeshellarg($validated['route_key']);
            $commands = [];
            
            $fields = ['interface', 'target', 'netmask', 'gateway', 'metric', 'mtu', 'type', 'table', 'source'];
            foreach ($fields as $field) {
                $val = $validated[$field] ?? '';
                
                if ($field === 'interface' && strtolower($val) === 'sin especificar') {
                    $commands[] = "uci delete network.{$key}.{$field} > /dev/null 2>&1 || true";
                    continue;
                }

                if ($val !== '') {
                    $commands[] = "uci set network.{$key}.{$field}='{$val}'";
                } else {
                    $commands[] = "uci delete network.{$key}.{$field} > /dev/null 2>&1 || true"; 
                }
            }
            
            $onlinkVal = $request->boolean('onlink') ? '1' : '0';
            $commands[] = "uci set network.{$key}.onlink='{$onlinkVal}'";

            $commands[] = "uci commit network";
            $commands[] = "ubus call network reload";

            $singleCommand = implode(' ; ', $commands);
            $result = $this->router->execute([$singleCommand]);

            return back()->with(['result_success' => $result['success'], 'result_output' => $result['output'], 'result_title' => $result['success'] ? 'Ruta IPv4 actualizada exitosamente' : 'Error al actualizar']);
        } catch (\Throwable $e) {
            return back()->with(['result_success' => false, 'result_output' => $e->getMessage(), 'result_title' => 'Error al actualizar ruta']);
        }
    }

    public function destroyStaticIpv4(Request $request)
    {
        $validated = $request->validate(['route_key' => 'required|string']);
        try {
            $key = escapeshellarg($validated['route_key']);
            $singleCommand = implode(' ; ', ["uci delete network.{$key}", "uci commit network", "ubus call network reload"]);
            $result = $this->router->execute([$singleCommand]);

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
        $allRoutes = $this->getAllRoutesFast();
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
                if ($key === 'onlink') $value = $value ? '1' : '0';

                if ($key === 'interface' && strtolower($value) === 'sin especificar') {
                    continue; 
                }

                if ($value !== null && $value !== '') {
                    $commands[] = "uci set network.@route6[-1].{$key}='{$value}'";
                }
            }
            $commands[] = "uci commit network";
            $commands[] = "ubus call network reload";

            $singleCommand = implode(' ; ', $commands);
            $result = $this->router->execute([$singleCommand]);

            return back()->with(['result_success' => $result['success'], 'result_output' => $result['output'], 'result_title' => $result['success'] ? 'Ruta IPv6 agregada' : 'Error']);
        } catch (\Throwable $e) {
            return back()->with(['result_success' => false, 'result_output' => $e->getMessage(), 'result_title' => 'Error']);
        }
    }

    public function updateStaticIpv6(Request $request)
    {
        $validated = $request->validate([
            'route_key' => 'required|string',
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
            $key = escapeshellarg($validated['route_key']);
            $commands = [];
            
            $fields = ['interface', 'target', 'gateway', 'metric', 'mtu', 'type', 'table', 'source'];
            foreach ($fields as $field) {
                $val = $validated[$field] ?? '';

                if ($field === 'interface' && strtolower($val) === 'sin especificar') {
                    $commands[] = "uci delete network.{$key}.{$field} > /dev/null 2>&1 || true";
                    continue;
                }

                if ($val !== '') {
                    $commands[] = "uci set network.{$key}.{$field}='{$val}'";
                } else {
                    $commands[] = "uci delete network.{$key}.{$field} > /dev/null 2>&1 || true";
                }
            }
            
            $onlinkVal = $request->boolean('onlink') ? '1' : '0';
            $commands[] = "uci set network.{$key}.onlink='{$onlinkVal}'";

            $commands[] = "uci commit network";
            $commands[] = "ubus call network reload";

            $singleCommand = implode(' ; ', $commands);
            $result = $this->router->execute([$singleCommand]);

            return back()->with(['result_success' => $result['success'], 'result_output' => $result['output'], 'result_title' => $result['success'] ? 'Ruta IPv6 actualizada exitosamente' : 'Error al actualizar']);
        } catch (\Throwable $e) {
            return back()->with(['result_success' => false, 'result_output' => $e->getMessage(), 'result_title' => 'Error al actualizar ruta']);
        }
    }

    public function destroyStaticIpv6(Request $request)
    {
        $validated = $request->validate(['route_key' => 'required|string']);
        try {
            $key = escapeshellarg($validated['route_key']);
            $singleCommand = implode(' ; ', ["uci delete network.{$key}", "uci commit network", "ubus call network reload"]);
            $result = $this->router->execute([$singleCommand]);

            return back()->with(['result_success' => $result['success'], 'result_output' => $result['output'], 'result_title' => $result['success'] ? 'Ruta IPv6 eliminada' : 'Error']);
        } catch (\Throwable $e) {
            return back()->with(['result_success' => false, 'result_output' => $e->getMessage(), 'result_title' => 'Error']);
        }
    }

    /* =========================================================
       ESTADO DE CONEXIÓN E INTERNET
    ========================================================= */

    public function checkConnection()
    {
        return response()->json([
            'connected' => $this->router->isConnected()
        ]);
    }

    public function checkInternet()
    {
        try {
            $command = "ping -c 2 -W 2 8.8.8.8 > /dev/null 2>&1 && echo 'ONLINE' || echo 'OFFLINE'";
            $result = $this->router->execute([$command]);
            $isOnline = trim($result['output']) === 'ONLINE';

            return response()->json([
                'success' => true,
                'has_internet' => $isOnline,
                'message' => $isOnline ? 'Conectado a Internet' : 'Sin acceso a Internet'
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