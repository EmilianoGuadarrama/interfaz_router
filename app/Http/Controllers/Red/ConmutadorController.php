<?php

namespace App\Http\Controllers\Red;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RouterSshService;
use Illuminate\Support\Facades\Log;

class ConmutadorController extends Controller
{
    protected RouterSshService $router;

    public function __construct(RouterSshService $router)
    {
        $this->router = $router;
    }

    /*
    |--------------------------------------------------------------------------
    | ÍNDICE — Redirige a configuración general
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        return redirect()->route('red.conmutador.general');
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN GENERAL DEL CONMUTADOR
    |--------------------------------------------------------------------------
    */

    public function general()
    {
        $config = [
            'enable_vlan' => true,
        ];

        try {
            $result = $this->router->execute([
                "uci -q get network.switch0.enable_vlan",
            ]);

            $lines = array_filter(explode("\n", $result['output']), function ($line) {
                return !empty(trim($line)) && !str_starts_with(trim($line), '>>>');
            });

            foreach ($lines as $line) {
                $val = trim($line);
                if ($val === '0' || $val === '1') {
                    $config['enable_vlan'] = ($val === '1');
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Conmutador general: No se pudo leer config: ' . $e->getMessage());
        }

        // Merge with session data if previously saved
        $config = array_merge($config, session('switch_general_config', []));

        return view('network.switch.general', compact('config'));
    }

    public function updateGeneral(Request $request)
    {
        $enableVlan = $request->has('enable_vlan');

        try {
            $cmds = [
                "uci set network.switch0=switch",
                "uci set network.switch0.name='switch0'",
                "uci set network.switch0.reset='1'",
                "uci set network.switch0.enable_vlan='" . ($enableVlan ? '1' : '0') . "'",
                "uci commit network",
            ];

            if ($request->input('submit_action') === 'apply') {
                $cmds[] = "/etc/init.d/network reload";
            }

            $result = $this->router->execute($cmds);

            session(['switch_general_config' => ['enable_vlan' => $enableVlan]]);

            if ($result['success']) {
                $msg = $request->input('submit_action') === 'apply'
                    ? 'Configuración del conmutador guardada y aplicada correctamente.'
                    : 'Configuración del conmutador guardada correctamente.';
                return back()->with('success', $msg);
            } else {
                Log::error('Conmutador updateGeneral error: ' . $result['output']);
                return back()->with('error', 'Error al aplicar la configuración del conmutador.');
            }
        } catch (\Throwable $e) {
            Log::error('Conmutador updateGeneral exception: ' . $e->getMessage());
            session(['switch_general_config' => ['enable_vlan' => $enableVlan]]);
            return back()->with('error', 'No se pudo conectar con el router.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VLANs
    |--------------------------------------------------------------------------
    */

    public function vlans()
    {
        $vlans = [];

        try {
            // Leer VLANs configuradas del router
            $result = $this->router->execute([
                "uci show network | grep 'switch_vlan'"
            ]);

            $lines = explode("\n", $result['output']);
            $temp  = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (preg_match("/network\.@switch_vlan\[(\d+)\]\.(device|vlan|vid|ports)='(.+)'/", $line, $m)) {
                    $temp[$m[1]][$m[2]] = $m[3];
                }
            }

            foreach ($temp as $i => $entry) {
                $vlans[] = [
                    'index'  => $i,
                    'device' => $entry['device'] ?? 'switch0',
                    'vlan'   => $entry['vlan'] ?? $i,
                    'vid'    => $entry['vid'] ?? ($entry['vlan'] ?? $i),
                    'ports'  => $entry['ports'] ?? '',
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Conmutador vlans: No se pudo leer VLANs: ' . $e->getMessage());
        }

        // Fallback a sesión si no hay datos del router
        if (empty($vlans)) {
            $vlans = session('switch_vlans', [
                [
                    'index'  => 0,
                    'device' => 'switch0',
                    'vlan'   => 1,
                    'vid'    => 1,
                    'ports'  => '0 1 2 3 5t',
                ],
                [
                    'index'  => 1,
                    'device' => 'switch0',
                    'vlan'   => 2,
                    'vid'    => 2,
                    'ports'  => '4 6t',
                ],
            ]);
        }

        // Puertos disponibles del switch (típicos de un router OpenWrt)
        $availablePorts = [
            ['number' => 0, 'label' => 'Puerto 1 (LAN)'],
            ['number' => 1, 'label' => 'Puerto 2 (LAN)'],
            ['number' => 2, 'label' => 'Puerto 3 (LAN)'],
            ['number' => 3, 'label' => 'Puerto 4 (LAN)'],
            ['number' => 4, 'label' => 'Puerto 5 (WAN)'],
            ['number' => 5, 'label' => 'CPU (eth0)'],
            ['number' => 6, 'label' => 'CPU (eth1)'],
        ];

        return view('network.switch.vlans', compact('vlans', 'availablePorts'));
    }

    public function storeVlan(Request $request)
    {
        $data = $request->validate([
            'vlan_id' => 'required|integer|min:1|max:4094',
            'ports'   => 'nullable|string|max:100',
        ], [
            'vlan_id.required' => 'El ID de VLAN es obligatorio.',
            'vlan_id.integer'  => 'El ID de VLAN debe ser un número entero.',
            'vlan_id.min'      => 'El ID de VLAN mínimo es 1.',
            'vlan_id.max'      => 'El ID de VLAN máximo es 4094.',
        ]);

        try {
            $vlanId = $data['vlan_id'];
            $ports  = $data['ports'] ?? '';

            $cmds = [
                "uci add network switch_vlan",
                "uci set network.@switch_vlan[-1].device='switch0'",
                "uci set network.@switch_vlan[-1].vlan='{$vlanId}'",
                "uci set network.@switch_vlan[-1].vid='{$vlanId}'",
            ];

            if (!empty($ports)) {
                $cmds[] = "uci set network.@switch_vlan[-1].ports='{$ports}'";
            }

            $cmds[] = "uci commit network";
            $cmds[] = "/etc/init.d/network reload";

            $result = $this->router->execute($cmds);

            if ($result['success']) {
                return redirect()->route('red.conmutador.vlans')
                    ->with('success', "VLAN {$vlanId} agregada correctamente.");
            } else {
                Log::error('Conmutador storeVlan error: ' . $result['output']);
                return back()->withInput()->with('error', 'Error al agregar la VLAN.');
            }
        } catch (\Throwable $e) {
            Log::error('Conmutador storeVlan exception: ' . $e->getMessage());
            return back()->withInput()->with('error', 'No se pudo conectar con el router.');
        }
    }

    public function updateVlan(Request $request, $index)
    {
        $data = $request->validate([
            'ports' => 'nullable|string|max:100',
        ]);

        $index = (int) $index;

        try {
            $ports = $data['ports'] ?? '';

            $cmds = [
                "uci set network.@switch_vlan[{$index}].ports='{$ports}'",
                "uci commit network",
            ];

            if ($request->input('submit_action') === 'apply') {
                $cmds[] = "/etc/init.d/network reload";
            }

            $result = $this->router->execute($cmds);

            if ($result['success']) {
                return redirect()->route('red.conmutador.vlans')
                    ->with('success', 'VLAN actualizada correctamente.');
            } else {
                Log::error('Conmutador updateVlan error: ' . $result['output']);
                return back()->with('error', 'Error al actualizar la VLAN.');
            }
        } catch (\Throwable $e) {
            Log::error('Conmutador updateVlan exception: ' . $e->getMessage());
            return back()->with('error', 'No se pudo conectar con el router.');
        }
    }

    public function destroyVlan(Request $request, $index)
    {
        $index = (int) $index;

        try {
            $cmds = [
                "uci delete network.@switch_vlan[{$index}]",
                "uci commit network",
                "/etc/init.d/network reload",
            ];

            $result = $this->router->execute($cmds);

            if ($result['success']) {
                return redirect()->route('red.conmutador.vlans')
                    ->with('success', 'VLAN eliminada correctamente.');
            } else {
                Log::error('Conmutador destroyVlan error: ' . $result['output']);
                return back()->with('error', 'Error al eliminar la VLAN.');
            }
        } catch (\Throwable $e) {
            Log::error('Conmutador destroyVlan exception: ' . $e->getMessage());
            return back()->with('error', 'No se pudo conectar con el router.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN DE PUERTOS
    |--------------------------------------------------------------------------
    */

    public function updatePorts(Request $request)
    {
        $data = $request->validate([
            'vlan_index' => 'required|integer|min:0',
            'port_config' => 'required|array',
            'port_config.*' => 'in:untagged,tagged,off',
        ], [
            'vlan_index.required' => 'El índice de VLAN es obligatorio.',
            'port_config.required' => 'La configuración de puertos es obligatoria.',
        ]);

        $index = (int) $data['vlan_index'];

        try {
            // Construir string de puertos a partir de la configuración
            $portParts = [];
            foreach ($data['port_config'] as $portNum => $mode) {
                $portNum = (int) $portNum;
                switch ($mode) {
                    case 'untagged':
                        $portParts[] = (string) $portNum;
                        break;
                    case 'tagged':
                        $portParts[] = $portNum . 't';
                        break;
                    // 'off' → no se incluye en la lista
                }
            }

            $portsStr = implode(' ', $portParts);

            $cmds = [
                "uci set network.@switch_vlan[{$index}].ports='{$portsStr}'",
                "uci commit network",
                "/etc/init.d/network reload",
            ];

            $result = $this->router->execute($cmds);

            if ($result['success']) {
                return redirect()->route('red.conmutador.vlans')
                    ->with('success', 'Configuración de puertos actualizada correctamente.');
            } else {
                Log::error('Conmutador updatePorts error: ' . $result['output']);
                return back()->with('error', 'Error al actualizar la configuración de puertos.');
            }
        } catch (\Throwable $e) {
            Log::error('Conmutador updatePorts exception: ' . $e->getMessage());
            return back()->with('error', 'No se pudo conectar con el router.');
        }
    }
}
