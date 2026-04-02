<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RouterSshService;
use Illuminate\Support\Facades\Log;

class NetworkController extends Controller
{
    protected RouterSshService $router;

    public function __construct(RouterSshService $router)
    {
        $this->router = $router;
    }

    /* =========================
       CONMUTADOR
    ========================= */

    public function switchGeneral()
    {
        return view('network.switch.general');
    }

    public function switchVlans()
    {
        return view('network.switch.vlans');
    }

    public function updateSwitchVlans(Request $request)
    {
        $data = $request->validate([
            'enable_vlan' => ['nullable'],
        ]);

        try {
            $enabled = $request->boolean('enable_vlan') ? '1' : '0';

            $commands = [
                "uci set network.@switch[0].enable_vlan='{$enabled}' 2>&1",
                "uci commit network 2>&1",
                "/etc/init.d/network restart 2>&1",
            ];

            $result = $this->router->execute($commands);

            return back()->with([
                'result_success' => $result['success'],
                'result_output' => $result['output'],
                'result_title' => $result['success'] ? 'Conmutador actualizado' : 'Error al actualizar conmutador',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error conmutador: ' . $e->getMessage());

            return back()->with([
                'result_success' => false,
                'result_output' => $e->getMessage(),
                'result_title' => 'Error de conexión o ejecución',
            ]);
        }
    }

    /* =========================
       DHCP Y DNS
    ========================= */

    public function dhcpDnsGeneral()
    {
        return view('network.dhcpdns.general');
    }

    public function dhcpDnsResolvHosts()
    {
        return view('network.dhcpdns.resolv-hosts');
    }

    public function dhcpDnsTftp()
    {
        return view('network.dhcpdns.tftp');
    }

    public function dhcpDnsAdvanced()
    {
        return view('network.dhcpdns.advanced');
    }

    public function dhcpDnsStatic()
{
    $staticEntries = [];
    $activeLeases  = [];

    try {
        // Asignaciones estáticas UCI
        $staticResult = $this->router->execute([
            "uci show dhcp | grep -E 'host\['"
        ]);

        $lines = explode("\n", $staticResult['output']);
        $temp  = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match("/dhcp\.@host\[(\d+)\]\.(name|mac|ip|leasetime|duid|hostid)='(.+)'/", $line, $m)) {
                $temp[$m[1]][$m[2]] = $m[3];
            }
        }

        foreach ($temp as $i => $entry) {
            $staticEntries[] = [
                'index'     => $i,
                'name'      => $entry['name']      ?? '-',
                'mac'       => $entry['mac']       ?? '-',
                'ip'        => $entry['ip']        ?? '-',
                'leasetime' => $entry['leasetime'] ?? '-',
                'duid'      => $entry['duid']      ?? '-',
                'hostid'    => $entry['hostid']    ?? '-',
            ];
        }

        // Arrendamientos activos
        $leasesResult = $this->router->execute([
            "cat /tmp/dhcp.leases"
        ]);

        foreach (explode("\n", $leasesResult['output']) as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '>>>')) continue;

            $parts = preg_split('/\s+/', $line);
            if (count($parts) >= 4) {
                $seconds = (int) $parts[0] - time();
                $remaining = $seconds > 0
                    ? sprintf('%dh %dm %ds', $seconds/3600, ($seconds%3600)/60, $seconds%60)
                    : 'Expirado';

                $activeLeases[] = [
                    'name' => $parts[3] ?? '-',
                    'ip'   => $parts[2] ?? '-',
                    'mac'  => $parts[1] ?? '-',
                    'time' => $remaining,
                ];
            }
        }

    } catch (\Throwable $e) {
        Log::error('Error DHCP static: ' . $e->getMessage());
    }

    return view('network.dhcpdns.static', compact('staticEntries', 'activeLeases'));
}

    public function updateDhcpDnsGeneral(Request $request)
    {
        return back()->with('result_title', 'Sección pendiente de conectar al router.');
    }

    public function updateDhcpDnsResolvHosts(Request $request)
    {
        return back()->with('result_title', 'Sección pendiente de conectar al router.');
    }

    public function updateDhcpDnsTftp(Request $request)
    {
        return back()->with('result_title', 'Sección pendiente de conectar al router.');
    }

    public function updateDhcpDnsAdvanced(Request $request)
    {
        return back()->with('result_title', 'Sección pendiente de conectar al router.');
    }

    public function updateDhcpDnsStatic(Request $request)
    {
        return back()->with('result_title', 'Sección pendiente de conectar al router.');
    }
    /* =========================
    NOMBRES DE HOST (DNS local)
    ========================= */
    public function hostEntries()
    {
    try {
        $result = $this->router->execute([
            "uci show dhcp | grep -E '@domain|name|ip'"
        ]);

        $entries = [];
        $lines = explode("\n", $result['output']);
        $temp = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match("/dhcp\.@domain\[(\d+)\]\.(name|ip)='(.+)'/", $line, $m)) {
                $index = $m[1];
                $key   = $m[2];
                $value = $m[3];
                $temp[$index][$key] = $value;
            }
        }

        foreach ($temp as $i => $entry) {
            if (isset($entry['name'], $entry['ip'])) {
                $entries[] = [
                    'index' => $i,
                    'name'  => $entry['name'],
                    'ip'    => $entry['ip'],
                ];
            }
        }

    } catch (\Throwable $e) {
        Log::error('Error listando host entries: ' . $e->getMessage());
        $entries = [];
    }

    return view('network.hostname', compact('entries'));
}

public function storeHostEntry(Request $request)
{
    $data = $request->validate([
        'name' => ['required', 'string', 'max:63', 'regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]*[a-zA-Z0-9])?$/'],
        'ip'   => ['required', 'ip'],
    ], [
        'name.required' => 'El nombre es obligatorio.',
        'name.regex'    => 'Solo letras, números y guiones. No debe haber espacios.',
        'ip.required'   => 'La dirección IP es obligatoria.',
        'ip.ip'         => 'Ingresa una dirección IP válida.',
    ]);

    try {
        $commands = [
            "uci add dhcp domain",
            "uci set dhcp.@domain[-1].name='{$data['name']}'",
            "uci set dhcp.@domain[-1].ip='{$data['ip']}'",
            "uci commit dhcp",
            "/etc/init.d/dnsmasq restart",
        ];

        $result = $this->router->execute($commands);

        return back()->with([
            'result_success' => $result['success'],
            'result_title'   => $result['success'] ? 'Entrada agregada correctamente' : 'Error al agregar entrada',
        ]);

    } catch (\Throwable $e) {
        Log::error('Error agregando host entry: ' . $e->getMessage());
        return back()->with([
            'result_success' => false,
            'result_title'   => 'Error de conexión o ejecución',
        ]);
    }
}

public function destroyHostEntry(Request $request)
{
    $request->validate([
        'index' => ['required', 'integer', 'min:0'],
    ]);

    try {
        $index = $request->input('index');

        $commands = [
            "uci delete dhcp.@domain[{$index}]",
            "uci commit dhcp",
            "/etc/init.d/dnsmasq restart",
        ];

        $result = $this->router->execute($commands);

        return back()->with([
            'result_success' => $result['success'],
            'result_title'   => $result['success'] ? 'Entrada eliminada correctamente' : 'Error al eliminar entrada',
        ]);

    } catch (\Throwable $e) {
        Log::error('Error eliminando host entry: ' . $e->getMessage());
        return back()->with([
            'result_success' => false,
            'result_title'   => 'Error de conexión o ejecución',
        ]);
    }
}
    //LEDS
     private array $ledNames     = ['green:lan','green:wan','green:wlan','mt76-phy0','orange:wan'];
    private array $disparadores = ['defaulton','netdev','none','phy0assoc','phy0radio','phy0rx','phy0tpt','phy0tx','switch0','timer'];

    public function leds()
    {
        $leds = [];
        try {
            $result = $this->router->execute(["uci show system | grep -E 'led'"]);
            $temp   = [];
            foreach (explode("\n", $result['output']) as $line) {
                if (preg_match("/system\.led_(\w+)\.(name|sysfs|default|trigger|mode|delayon|delayoff)='(.+)'/", trim($line), $m)) {
                    $temp[$m[1]][$m[2]] = $m[3];
                }
            }
            foreach ($temp as $key => $e) {
                $leds[] = [
                    'key'        => $key,
                    'nombre'     => $e['name']     ?? $key,
                    'led_name'   => $e['sysfs']    ?? '-',
                    'estado'     => ($e['default'] ?? '0') === '1' ? 'Encendido' : 'Apagado',
                    'disparador' => $e['trigger']  ?? 'defaulton',
                    'modo'       => isset($e['mode']) ? explode(' ', $e['mode']) : [],
                    'timer_on'   => $e['delayon']  ?? null,
                    'timer_off'  => $e['delayoff'] ?? null,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('LEDs: ' . $e->getMessage());
        }
        return view('network.leds.leds_index', compact('leds'));
    }

    public function createLed()
    {
        return view('network.leds.leds_form', [
            'led' => null, 'ledNames' => $this->ledNames, 'disparadores' => $this->disparadores,
        ]);
    }

    public function storeLed(Request $request)
    {
        $request->validate([
            'nombre'     => ['required','string','max:50'],
            'led_name'   => ['required','string'],
            'disparador' => ['required','string'],
        ]);

        try {
            $key     = strtolower(preg_replace('/\s+/', '_', $request->nombre));
            $default = $request->boolean('estado_predeterminado') ? '1' : '0';

            $cmds = [
                "uci set system.led_{$key}=led",
                "uci set system.led_{$key}.name='{$request->nombre}'",
                "uci set system.led_{$key}.sysfs='{$request->led_name}'",
                "uci set system.led_{$key}.default='{$default}'",
                "uci set system.led_{$key}.trigger='{$request->disparador}'",
            ];
            if ($request->disparador === 'netdev') {
                $modo = implode(' ', $request->input('modo_disparador', []));
                $cmds[] = "uci set system.led_{$key}.mode='{$modo}'";
            }
            if ($request->disparador === 'timer') {
                $cmds[] = "uci set system.led_{$key}.delayon='{$request->timer_on}'";
                $cmds[] = "uci set system.led_{$key}.delayoff='{$request->timer_off}'";
            }
            $cmds[] = "uci commit system";
            $cmds[] = "/etc/init.d/led restart";

            $result = $this->router->execute($cmds);
            return redirect()->route('leds.index')->with([
                'result_success' => $result['success'],
                'result_title'   => $result['success'] ? 'LED creado' : 'Error al crear LED',
            ]);
        } catch (\Throwable $e) {
            Log::error('storeLed: ' . $e->getMessage());
            return back()->with(['result_success' => false, 'result_title' => 'Error de conexión']);
        }
    }

    public function editLed($key)
    {
        $led = null;
        try {
            $result = $this->router->execute(["uci show system.led_{$key}"]);
            $e = [];
            foreach (explode("\n", $result['output']) as $line) {
                if (preg_match("/system\.led_{$key}\.(name|sysfs|default|trigger|mode|delayon|delayoff)='(.+)'/", trim($line), $m)) {
                    $e[$m[1]] = $m[2];
                }
            }
            if ($e) {
                $led = [
                    'key'        => $key,
                    'nombre'     => $e['name']     ?? $key,
                    'led_name'   => $e['sysfs']    ?? '',
                    'estado'     => ($e['default'] ?? '0') === '1',
                    'disparador' => $e['trigger']  ?? 'defaulton',
                    'modo'       => isset($e['mode']) ? explode(' ', $e['mode']) : [],
                    'timer_on'   => $e['delayon']  ?? 500,
                    'timer_off'  => $e['delayoff'] ?? 500,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('editLed: ' . $e->getMessage());
        }
        return view('network.leds.leds_form', [
            'led' => $led, 'ledNames' => $this->ledNames, 'disparadores' => $this->disparadores,
        ]);
    }

    public function updateLed(Request $request, $key)
    {
        $request->validate([
            'nombre'     => ['required','string','max:50'],
            'led_name'   => ['required','string'],
            'disparador' => ['required','string'],
        ]);

        try {
            $default = $request->boolean('estado_predeterminado') ? '1' : '0';
            $cmds = [
                "uci set system.led_{$key}.name='{$request->nombre}'",
                "uci set system.led_{$key}.sysfs='{$request->led_name}'",
                "uci set system.led_{$key}.default='{$default}'",
                "uci set system.led_{$key}.trigger='{$request->disparador}'",
            ];
            if ($request->disparador === 'netdev') {
                $modo = implode(' ', $request->input('modo_disparador', []));
                $cmds[] = "uci set system.led_{$key}.mode='{$modo}'";
            }
            if ($request->disparador === 'timer') {
                $cmds[] = "uci set system.led_{$key}.delayon='{$request->timer_on}'";
                $cmds[] = "uci set system.led_{$key}.delayoff='{$request->timer_off}'";
            }
            $cmds[] = "uci commit system";
            $cmds[] = "/etc/init.d/led restart";

            $result = $this->router->execute($cmds);
            return redirect()->route('leds.index')->with([
                'result_success' => $result['success'],
                'result_title'   => $result['success'] ? 'LED actualizado' : 'Error al actualizar',
            ]);
        } catch (\Throwable $e) {
            Log::error('updateLed: ' . $e->getMessage());
            return back()->with(['result_success' => false, 'result_title' => 'Error de conexión']);
        }
    }

    public function destroyLed($key)
    {
        try {
            $result = $this->router->execute([
                "uci delete system.led_{$key}",
                "uci commit system",
                "/etc/init.d/led restart",
            ]);
            return redirect()->route('leds.index')->with([
                'result_success' => $result['success'],
                'result_title'   => $result['success'] ? 'LED eliminado' : 'Error al eliminar',
            ]);
        } catch (\Throwable $e) {
            Log::error('destroyLed: ' . $e->getMessage());
            return back()->with(['result_success' => false, 'result_title' => 'Error de conexión']);
        }
    }
}
