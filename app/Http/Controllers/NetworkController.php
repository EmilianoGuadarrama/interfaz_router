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
       INTERFACES
    ========================= */
    public function interfaces()
    {
        $interfaces = [];
        try {
            $rawJson = $this->router->getRaw('ubus call network.interface dump');
            $decoded = json_decode($rawJson, true);
            if (is_array($decoded) && isset($decoded['interface'])) {
                $interfaces = $decoded['interface'];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error fetching interfaces from router: ' . $e->getMessage());
            session()->flash('error', 'Error al conectar con el router para obtener las interfaces reales.');
        }

        return view('network.interfaces', compact('interfaces'));
    }

    public function storeInterface(Request $request)
    {
        $request->validateWithBag('createInterface', [
            'name' => 'required|string|alpha_dash|max:20',
            'protocol' => 'required|in:dhcp,unmanaged,ppp,pppoe,static',
            'interface' => 'nullable|string'
        ], [
            'name.required' => 'El nombre de la interfaz es obligatorio.',
            'name.alpha_dash' => 'El nombre solo puede contener letras, números, guiones y guiones bajos (sin espacios).',
            'name.max' => 'El nombre no debe superar los 20 caracteres.',
            'protocol.required' => 'El protocolo es obligatorio.',
            'protocol.in' => 'El protocolo seleccionado no es válido.'
        ]);

        return redirect()->route('network.interfaces')->with('success', 'Interfaz añadida correctamente (Simulado).');
    }

    public function updateLanInterface(Request $request)
    {
        $request->validateWithBag('updateLan', [
            'lan_ipv4_address' => 'required|ipv4',
            'lan_ipv4_netmask' => 'required|string|regex:/^(((255\.){3}(255|254|252|248|240|224|192|128|0+))|((255\.){2}(255|254|252|248|240|224|192|128|0+)\.0)|((255\.)(255|254|252|248|240|224|192|128|0+)(\.0+){2})|((255|254|252|248|240|224|192|128|0+)(\.0+){3}))$/',
            'lan_ipv4_gateway' => 'nullable|ipv4',
            'lan_mac' => 'nullable|mac_address',
            'lan_mtu' => 'nullable|integer|min:576|max:9000',
            'lan_dhcp_limit' => 'nullable|integer|min:1',
            'lan_dhcp_leasetime' => 'nullable|string'
        ], [
            'lan_ipv4_address.required' => 'La dirección IPv4 es obligatoria.',
            'lan_ipv4_address.ipv4' => 'Debe ser una dirección IPv4 válida.',
            'lan_ipv4_netmask.required' => 'La máscara de red es obligatoria.',
            'lan_ipv4_netmask.regex' => 'Formato de máscara de red inválido.',
            'lan_ipv4_gateway.ipv4' => 'Debe ser una dirección IPv4 válida.',
            'lan_mac.mac_address' => 'El formato de la dirección MAC no es válido.',
            'lan_mtu.integer' => 'El MTU debe ser un número entero.',
            'lan_mtu.min' => 'El MTU no puede ser menor a 576.',
            'lan_mtu.max' => 'El MTU no puede exceder 9000.',
            'lan_dhcp_limit.integer' => 'El límite debe ser un entero.',
        ]);

        return redirect()->route('network.interfaces')->with('success', 'Interfaz LAN actualizada correctamente (Simulado).');
    }

    public function updateWanInterface(Request $request)
    {
        $request->validateWithBag('updateWan', [
            'wan_mac' => 'nullable|mac_address',
            'wan_mtu' => 'nullable|integer|min:576|max:9000',
            'wan_metric' => 'nullable|integer|min:0'
        ], [
            'wan_mac.mac_address' => 'El formato de la dirección MAC no es válido.',
            'wan_mtu.integer' => 'El MTU debe ser un número entero.',
            'wan_mtu.min' => 'El MTU no puede ser menor a 576.',
            'wan_mtu.max' => 'El MTU no puede exceder 9000.',
            'wan_metric.integer' => 'La métrica debe ser un número entero.'
        ]);

        return redirect()->route('network.interfaces')->with('success', 'Interfaz WAN actualizada correctamente (Simulado).');
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
        $activeLeases = [];

        try {
            // Asignaciones estáticas UCI
            $staticResult = $this->router->execute([
                "uci show dhcp | grep -E 'host\['"
            ]);

            $lines = explode("\n", $staticResult['output']);
            $temp = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (preg_match("/dhcp\.@host\[(\d+)\]\.(name|mac|ip|leasetime|duid|hostid)='(.+)'/", $line, $m)) {
                    $temp[$m[1]][$m[2]] = $m[3];
                }
            }

            foreach ($temp as $i => $entry) {
                $staticEntries[] = [
                    'index' => $i,
                    'name' => $entry['name'] ?? '-',
                    'mac' => $entry['mac'] ?? '-',
                    'ip' => $entry['ip'] ?? '-',
                    'leasetime' => $entry['leasetime'] ?? '-',
                    'duid' => $entry['duid'] ?? '-',
                    'hostid' => $entry['hostid'] ?? '-',
                ];
            }

            // Arrendamientos activos
            $leasesResult = $this->router->execute([
                "cat /tmp/dhcp.leases"
            ]);

            foreach (explode("\n", $leasesResult['output']) as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '>>>'))
                    continue;

                $parts = preg_split('/\s+/', $line);
                if (count($parts) >= 4) {
                    $seconds = (int) $parts[0] - time();
                    $remaining = $seconds > 0
                        ? sprintf('%dh %dm %ds', $seconds / 3600, ($seconds % 3600) / 60, $seconds % 60)
                        : 'Expirado';

                    $activeLeases[] = [
                        'name' => $parts[3] ?? '-',
                        'ip' => $parts[2] ?? '-',
                        'mac' => $parts[1] ?? '-',
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
                    $key = $m[2];
                    $value = $m[3];
                    $temp[$index][$key] = $value;
                }
            }

            foreach ($temp as $i => $entry) {
                if (isset($entry['name'], $entry['ip'])) {
                    $entries[] = [
                        'index' => $i,
                        'name' => $entry['name'],
                        'ip' => $entry['ip'],
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
            'ip' => ['required', 'ip'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.regex' => 'Solo letras, números y guiones. No debe haber espacios.',
            'ip.required' => 'La dirección IP es obligatoria.',
            'ip.ip' => 'Ingresa una dirección IP válida.',
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
                'result_title' => $result['success'] ? 'Entrada agregada correctamente' : 'Error al agregar entrada',
            ]);

        } catch (\Throwable $e) {
            Log::error('Error agregando host entry: ' . $e->getMessage());
            return back()->with([
                'result_success' => false,
                'result_title' => 'Error de conexión o ejecución',
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
                'result_title' => $result['success'] ? 'Entrada eliminada correctamente' : 'Error al eliminar entrada',
            ]);

        } catch (\Throwable $e) {
            Log::error('Error eliminando host entry: ' . $e->getMessage());
            return back()->with([
                'result_success' => false,
                'result_title' => 'Error de conexión o ejecución',
            ]);
        }
    }

}
