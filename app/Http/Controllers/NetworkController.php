<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RouterSshService;
use App\Services\RouterInterfaceService;
use Illuminate\Support\Facades\Log;

class NetworkController extends Controller
{
    protected RouterSshService $router;
    protected RouterInterfaceService $interfaceService;

    public function __construct(RouterSshService $router, RouterInterfaceService $interfaceService)
    {
        $this->router = $router;
        $this->interfaceService = $interfaceService;
    }
    /*
    |--------------------------------------------------------------------------
    | VISTAS PRINCIPALES
    |--------------------------------------------------------------------------
    */

    public function showSwitch()
    {
        return redirect()->route('network.switch.general');
    }

    public function updateSwitch(Request $request)
    {
        return back()->with('success', 'Configuración de conmutador actualizada correctamente.');
    }

    public function showDhcpDns()
    {
        return redirect()->route('network.dhcpdns.general');
    }

    public function updateDhcpDns(Request $request)
    {
        return back()->with('success', 'Configuración de DHCP y DNS actualizada correctamente.');
    }

    /* =========================
       INTERFACES
    ========================= */
    public function interfaces()
    {
        $interfaces = $this->interfaceService->getInterfaces();
        $devices = $this->interfaceService->getDevices();
        $uciConfig = $this->interfaceService->getUciConfig();
        $uciFirewallZones = $this->interfaceService->getUciFirewallZones();
        $uciDhcpConfig = $this->interfaceService->getUciDhcpConfig();

        return view('network.interfaces', compact('interfaces', 'devices', 'uciConfig', 'uciFirewallZones', 'uciDhcpConfig'));
    }

    public function storeInterface(Request $request)
    {
        $data = $request->validateWithBag('createInterface', [
            'name' => 'required|string|alpha_dash|max:20',
            'protocol' => 'required|in:dhcp,unmanaged,ppp,pppoe,static',
            'interface' => 'nullable|string',
            'bridge' => 'nullable|boolean'
        ], [
            'name.required' => 'El nombre de la interfaz es obligatorio.',
            'name.alpha_dash' => 'El nombre solo puede contener letras, números, guiones y guiones bajos (sin espacios).',
            'name.max' => 'El nombre no debe superar los 20 caracteres.',
            'protocol.required' => 'El protocolo es obligatorio.',
            'protocol.in' => 'El protocolo seleccionado no es válido.'
        ]);

        try {
            $name = strtolower($data['name']);
            $protocol = $data['protocol'];
            $device = $data['interface'] ?? '';
            $isBridge = $request->has('bridge');

            $cmds = [
                "uci set network.{$name}=interface",
                "uci set network.{$name}.proto='{$protocol}'"
            ];

            if ($isBridge) {
                $cmds[] = "uci set network.{$name}.type='bridge'";
            }

            if (!empty($device)) {
                $cmds[] = "uci set network.{$name}.device='{$device}'";
            }

            $cmds[] = "uci commit network";
            $cmds[] = "/etc/init.d/network reload";

            $result = $this->router->execute($cmds);

            if ($result['success']) {
                return redirect()->route('network.interfaces')->with('success', "Interfaz '{$name}' creada correctamente en el router.");
            } else {
                Log::error('Error from router executing UCI commands: ' . $result['output']);
                return back()->withInput()->withErrors(['createInterface' => 'Error estructural al configurar el router. Verifique la conexión.'])->with('error', 'Hubo un error configurando la interfaz en el router.');
            }

        } catch (\Throwable $e) {
            Log::error('Error storeInterface: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al procesar la creación de la interfaz: ' . $e->getMessage());
        }
    }

    private function sanitizeInterfaceName($name)
    {
        return preg_replace('/[^a-zA-Z0-9_\-@]/', '', $name);
    }

    public function restartInterface(Request $request, $name)
    {
        $safeName = $this->sanitizeInterfaceName($name);
        if (empty($safeName)) {
            return back()->with('error', 'Nombre de interfaz inválido.');
        }

        $lowerName = strtolower($safeName);

        try {
            $cmds = [
                "ifdown {$lowerName}",
                "ifup {$lowerName}"
            ];
            $result = $this->router->execute($cmds);

            if ($result['success']) {
                return back()->with('success', "Interfaz '{$safeName}' reiniciada correctamente.");
            } else {
                Log::error('Error restarting interface ' . $safeName . ': ' . $result['output']);
                return back()->with('error', "Error al reiniciar la interfaz '{$safeName}'.");
            }
        } catch (\Throwable $e) {
            Log::error('Error in restartInterface: ' . $e->getMessage());
            return back()->with('error', "No se pudo conectar con el router para reiniciar la interfaz.");
        }
    }

    public function stopInterface(Request $request, $name)
    {
        $safeName = $this->sanitizeInterfaceName($name);
        if (empty($safeName)) {
            return back()->with('error', 'Nombre de interfaz inválido.');
        }

        $lowerName = strtolower($safeName);

        try {
            $cmds = [
                "ifdown {$lowerName}"
            ];
            $result = $this->router->execute($cmds);

            if ($result['success']) {
                return back()->with('success', "Interfaz '{$safeName}' detenida correctamente.");
            } else {
                Log::error('Error stopping interface ' . $safeName . ': ' . $result['output']);
                return back()->with('error', "Error al detener la interfaz '{$safeName}'.");
            }
        } catch (\Throwable $e) {
            Log::error('Error in stopInterface: ' . $e->getMessage());
            return back()->with('error', "No se pudo conectar con el router para detener la interfaz.");
        }
    }

    public function destroyInterface(Request $request, $name)
    {
        $safeName = $this->sanitizeInterfaceName($name);
        if (empty($safeName)) {
            return back()->with('error', 'Nombre de interfaz inválido.');
        }

        $lowerName = strtolower($safeName);
        $criticalInterfaces = ['lan', 'wan', 'br-lan'];

        if (in_array($lowerName, $criticalInterfaces)) {
            return back()->with('error', "No se permite eliminar directamente la interfaz troncal '{$safeName}' por protección del sistema.");
        }

        try {
            $cmds = [
                "uci delete network.{$lowerName}",
                "uci commit network",
                "/etc/init.d/network reload"
            ];
            $result = $this->router->execute($cmds);

            if ($result['success']) {
                return back()->with('success', "Interfaz '{$safeName}' eliminada y configuración recargada correctamente.");
            } else {
                Log::error('Error destroying interface ' . $safeName . ': ' . $result['output']);
                return back()->with('error', "Error al eliminar la interfaz '{$safeName}'. Es posible que no exista.");
            }
        } catch (\Throwable $e) {
            Log::error('Error in destroyInterface: ' . $e->getMessage());
            return back()->with('error', "No se pudo conectar con el router para eliminar la interfaz.");
        }
    }

    public function updateInterface(Request $request, $name)
    {
        $safeName = $this->sanitizeInterfaceName($name);
        if (empty($safeName)) {
            return back()->with('error', 'Nombre de interfaz inválido.');
        }
        $lowerName = strtolower($safeName);

        $request->validateWithBag('updateInterface-'.$lowerName, [
            'proto' => 'required|string',
            'auto' => 'nullable|boolean',
            'ipaddr' => 'nullable|string',
            'netmask' => 'nullable|string',
            'gateway' => 'nullable|string',
            'broadcast' => 'nullable|string',
            'dns' => 'nullable|string',
            'ip6assign' => 'nullable|string',
            'ip6addr' => 'nullable|string',
            'ip6gw' => 'nullable|string',
            'ip6prefix' => 'nullable|string',
            'ip6ifaceid' => 'nullable|string',
            
            // DHCP Client
            'hostname' => 'nullable|string',
            'peerdns' => 'nullable|boolean',
            'defaultroute' => 'nullable|boolean',
            'clientid' => 'nullable|string',
            'vendorid' => 'nullable|string',
            
            // PPP/PPPoE
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'ac' => 'nullable|string',
            'service' => 'nullable|string',
            'device' => 'nullable|string',
            'lcp_echo_failure' => 'nullable|integer|min:0',
            'lcp_echo_interval' => 'nullable|integer|min:1',
            'demand' => 'nullable|integer|min:0',

            // Advanced
            'metric' => 'nullable|integer',
            'delegate' => 'nullable|boolean',
            'force_link' => 'nullable|boolean',
            'macaddr' => 'nullable|string',
            'mtu' => 'nullable|integer',

            // Physical
            'type' => 'nullable|string',
            'ifname' => 'nullable|array',

            // Firewall
            'firewall_zone' => 'nullable|string',

            // DHCP Server
            'dhcp_ignore' => 'nullable|boolean',
            'dhcp_start' => 'nullable|integer',
            'dhcp_limit' => 'nullable|integer',
            'dhcp_leasetime' => 'nullable|string',
            'dhcp_dynamic' => 'nullable|boolean'
        ]);

        try {
            $cmds = [];
            
            // Borrar campos específicos anteriores para limpiar la estructura
            $clearFields = ['ipaddr', 'netmask', 'gateway', 'broadcast', 'ip6assign', 'ip6addr', 'ip6gw', 'ip6prefix', 'ip6ifaceid', 'hostname', 'peerdns', 'defaultroute', 'clientid', 'vendorid', 'username', 'password', 'ac', 'service', 'device', 'metric', 'macaddr', 'mtu', 'lcp_echo_failure', 'lcp_echo_interval', 'demand'];
            foreach($clearFields as $cf) {
                $cmds[] = "uci -q delete network.{$lowerName}.{$cf} || true";
            }

            // Protocolo
            $proto = $request->input('proto', 'static');
            $cmds[] = "uci set network.{$lowerName}.proto='{$proto}'";

            // Campos activos básicos
            $activeFields = ['ip6assign', 'ip6addr', 'ip6gw', 'ip6prefix', 'ip6ifaceid', 'metric', 'macaddr', 'mtu'];
            if ($proto === 'static') {
                array_push($activeFields, 'ipaddr', 'gateway', 'broadcast');
            } elseif ($proto === 'dhcp') {
                array_push($activeFields, 'hostname', 'clientid', 'vendorid');
            } elseif ($proto === 'ppp' || $proto === 'pppoe') {
                array_push($activeFields, 'username', 'password', 'ac', 'service', 'device', 'lcp_echo_failure', 'lcp_echo_interval', 'demand');
            }

            foreach ($activeFields as $field) {
                $val = $request->input($field);
                if (!empty($val) || $val === '0') {
                    $cmds[] = "uci set network.{$lowerName}.{$field}='{$val}'";
                }
            }
            
            // Netmask (solo estático)
            if ($proto === 'static') {
                $val = $request->input('netmask');
                if ($val === 'custom') {
                    $val = $request->input('custom_netmask', '');
                }
                if (!empty($val)) {
                    $cmds[] = "uci set network.{$lowerName}.netmask='{$val}'";
                }
            }

            // Atributos lógicos (booleans)
            $booleans = [
                'auto' => '1',
                'delegate' => '1',
                'force_link' => '1',
            ];
            
            if ($proto === 'dhcp') {
                $booleans['peerdns'] = '1';
                $booleans['defaultroute'] = '1';
                $booleans['broadcast'] = '1'; // en dhcp, broadcast es un flag booleano
            } elseif ($proto === 'ppp' || $proto === 'pppoe') {
                $booleans['peerdns'] = '1';
                $booleans['defaultroute'] = '1';
            }

            foreach($booleans as $bf => $defaultVal) {
                if ($request->has($bf)) {
                    $cmds[] = "uci set network.{$lowerName}.{$bf}='1'";
                } else {
                    $cmds[] = "uci set network.{$lowerName}.{$bf}='0'";
                }
            }

            // Lista de DNS
            $dns = $request->input('dns');
            $cmds[] = "uci -q delete network.{$lowerName}.dns || true";
            if (!empty($dns)) {
                $dnsList = array_filter(explode(' ', $dns));
                foreach ($dnsList as $d) {
                    $cmds[] = "uci add_list network.{$lowerName}.dns='{$d}'";
                }
            }
            
            // Configuración física
            $type = $request->input('type');
            if ($type === 'bridge') {
                 $cmds[] = "uci set network.{$lowerName}.type='bridge'";
                 $ifnames = $request->input('ifname');
                 $ifnamesArray = is_array($ifnames) ? $ifnames : [];
                 if(($idx = array_search('custom', $ifnamesArray)) !== false) {
                      unset($ifnamesArray[$idx]);
                      if($custom = $request->input('custom_ifname')) {
                          $ifnamesArray[] = $custom;
                      }
                 }
                 if (!empty($ifnamesArray)) {
                     $ifacesStr = implode(' ', $ifnamesArray);
                     $cmds[] = "uci set network.{$lowerName}.ifname='{$ifacesStr}'";
                 }
            } else {
                 $cmds[] = "uci -q delete network.{$lowerName}.type || true";
            }
            
            // Configuración Servidor DHCP (solo guardaremos lo básico)
            $cmds[] = "uci show dhcp.{$lowerName} >/dev/null 2>&1 || uci set dhcp.{$lowerName}=dhcp";
            $cmds[] = "uci set dhcp.{$lowerName}.interface='{$lowerName}'";
            $dhcpIgnore = $request->has('dhcp_ignore') ? '1' : '0';
            $dhcpDynamic = $request->has('dhcp_dynamic') ? '1' : '0';
            $cmds[] = "uci set dhcp.{$lowerName}.ignore='{$dhcpIgnore}'";
            $cmds[] = "uci set dhcp.{$lowerName}.dynamic='{$dhcpDynamic}'";
            if($val = $request->input('dhcp_start')) $cmds[] = "uci set dhcp.{$lowerName}.start='{$val}'";
            if($val = $request->input('dhcp_limit')) $cmds[] = "uci set dhcp.{$lowerName}.limit='{$val}'";
            if($val = $request->input('dhcp_leasetime')) $cmds[] = "uci set dhcp.{$lowerName}.leasetime='{$val}'";
            
            // Zona del cortafuegos
            $fz = $request->input('firewall_zone');
            if ($fz === 'custom') {
                $fz = $request->input('custom_firewall_zone', '');
            }
            
            // Usaremos un pequeño script shell a ejecutar en el router para actualizar la zona del cortafuegos de forma atómica
            $fwScript = "
                IFACE='{$lowerName}'
                FZ='{$fz}'
                for zone in $(uci show firewall | grep '\\.name=' | cut -d. -f2); do
                    NETS=$(uci -q get firewall.\$zone.network)
                    NEW_NETS=$(echo \$NETS | sed \"s/\\b\$IFACE\\b//g\" | xargs)
                    uci set firewall.\$zone.network=\"\$NEW_NETS\"
                done
                if [ -n \"\$FZ\" ]; then
                    ZIDX=\"\"
                    for zone in $(uci show firewall | grep '\\.name=' | cut -d. -f2); do
                         ZNAME=$(uci get firewall.\$zone.name)
                         if [ \"\$ZNAME\" = \"\$FZ\" ]; then ZIDX=\$zone; break; fi
                    done
                    if [ -n \"\$ZIDX\" ]; then
                         NETS=$(uci -q get firewall.\$ZIDX.network)
                         uci set firewall.\$ZIDX.network=\"\$NETS \$IFACE\"
                    else
                         uci add firewall zone
                         uci set firewall.@zone[-1].name=\"\$FZ\"
                         uci set firewall.@zone[-1].network=\"\$IFACE\"
                         uci set firewall.@zone[-1].input=\"ACCEPT\"
                         uci set firewall.@zone[-1].output=\"ACCEPT\"
                         uci set firewall.@zone[-1].forward=\"REJECT\"
                    fi
                fi
                uci commit firewall
            ";
            $cmds[] = $fwScript;
            
            $cmds[] = "uci commit network";
            $cmds[] = "uci commit dhcp";
            $cmds[] = "/etc/init.d/network reload";
            $cmds[] = "/etc/init.d/dnsmasq restart";
            $cmds[] = "/etc/init.d/firewall restart >/dev/null 2>&1 &";

            $result = $this->router->execute($cmds);

            if ($result['success']) {
                session()->flash('reopen_modal', $lowerName);
                return back()->with('success', "Configuración general de '{$safeName}' actualizada correctamente en el router.");
            } else {
                Log::error('Error updating interface ' . $safeName . ': ' . $result['output']);
                session()->flash('reopen_modal', $lowerName);
                return back()->withInput()->withErrors(['updateInterface-'.$lowerName => "Falló al aplicar configuración."])->with('error', "No se pudieron aplicar los cambios en OpenWrt.");
            }
        } catch (\Throwable $e) {
            Log::error('Error en updateInterface: ' . $e->getMessage());
            return back()->withInput()->with('error', "Fallo de conexión con el router.");
        }
    }

    public function updateLanInterface(Request $request)
    {
        return $this->updateInterface($request, 'lan');
    }

    public function updateWanInterface(Request $request)
    {
        return $this->updateInterface($request, 'wan');
    }

    /*
    |--------------------------------------------------------------------------
    | CONMUTADOR
    |--------------------------------------------------------------------------
    */

    public function switchGeneral()
    {
        $config = session('switch_general', [
            'nombre' => 'Switch principal',
            'ip_gestion' => '192.168.10.2',
            'mascara' => '255.255.255.0',
            'gateway' => '192.168.10.1',
            'descripcion' => 'Conmutador de red local',
        ]);

        return view('network.switch.general', compact('config'));
    }

    public function switchVlans()
    {
        $vlans = session('switch_vlans', [
            [
                'id' => 10,
                'nombre' => 'Administracion',
                'puertos' => '1-4',
            ],
            [
                'id' => 20,
                'nombre' => 'Usuarios',
                'puertos' => '5-12',
            ],
        ]);

        return view('network.switch.vlans', compact('vlans'));
    }

    public function updateSwitchVlans(Request $request)
    {
        $data = $request->validate([
            'vlan_id' => 'nullable|integer|min:1|max:4094',
            'vlan_nombre' => 'nullable|string|max:100',
            'puertos' => 'nullable|string|max:100',
        ]);

        $vlans = session('switch_vlans', []);

        if (!empty($data['vlan_id']) && !empty($data['vlan_nombre'])) {
            $vlans[] = [
                'id' => $data['vlan_id'],
                'nombre' => $data['vlan_nombre'],
                'puertos' => $data['puertos'] ?? '',
            ];

            session(['switch_vlans' => $vlans]);

            return back()->with('success', 'VLAN agregada correctamente.');
        }

        return back()->with('success', 'Configuración de VLAN actualizada correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | DHCP Y DNS - CONFIGURACIÓN GENERAL
    |--------------------------------------------------------------------------
    */

    public function dhcpDnsGeneral()
    {
        return view('network.dhcpdns.general');
    }

    public function dhcpDnsResolvHosts()
    {
        return view('network.dhcpdns.resolv-hosts');
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
        $data = $request->validate([
            'lease_file' => 'nullable|string|max:255',
            'resolv_file' => 'nullable|string|max:255',
            'additional_hosts' => 'nullable|string|max:255',
        ]);

        $data['use_ethers'] = $request->has('use_ethers');
        $data['ignore_resolv'] = $request->has('ignore_resolv');
        $data['ignore_hosts'] = $request->has('ignore_hosts');

        session(['dhcpdns_resolv' => $data]);

        return back()->with('success', $this->getSuccessMessage($request, 'Configuración de archivos Resolv y Hosts guardada'));
    }

    /*
    |--------------------------------------------------------------------------
    | DHCP Y DNS - TFTP
    |--------------------------------------------------------------------------
    */

    public function dhcpDnsTftp()
    {
        $config = session('dhcpdns_tftp', [
            'enable_tftp' => false,
        ]);

        return view('network.dhcpdns.tftp', compact('config'));
    }

    public function updateDhcpDnsTftp(Request $request)
    {
        $data = [
            'enable_tftp' => $request->has('enable_tftp'),
        ];

        session(['dhcpdns_tftp' => $data]);

        return back()->with('success', $this->getSuccessMessage($request, 'Configuración TFTP guardada'));
    }

    /*
    |--------------------------------------------------------------------------
    | DHCP Y DNS - CONFIGURACIÓN AVANZADA
    |--------------------------------------------------------------------------
    */

    public function dhcpDnsAdvanced()
    {
        $config = session('dhcpdns_advanced', [
            'suppress_log' => false,
            'bogus_filter' => false,
            'sequential_ip' => false,
            'localise_queries' => true,
            'private_filter' => true,
            'expand_hosts' => true,
            'additional_servers_file' => '',
            'bogus_nxdomain' => '67.215.65.132',
            'dns_port' => 53,
            'dns_query_port' => 'cualquiera',
            'dhcp_max' => 'ilimitado',
            'edns_packet_max' => 1280,
            'dns_forward_max' => 150,
            'cache_size' => 150,
        ]);

        return view('network.dhcpdns.advanced', compact('config'));
    }

    public function updateDhcpDnsAdvanced(Request $request)
    {
        $data = $request->validate([
            'additional_servers_file' => 'nullable|string|max:255',
            'bogus_nxdomain' => 'nullable|string|max:255',
            'dns_port' => 'nullable|integer|min:1|max:65535',
            'dns_query_port' => 'nullable|string|max:100',
            'dhcp_max' => 'nullable|string|max:100',
            'edns_packet_max' => 'nullable|integer|min:1',
            'dns_forward_max' => 'nullable|integer|min:1',
            'cache_size' => 'nullable|integer|min:0',
        ]);

        $data['suppress_log'] = $request->has('suppress_log');
        $data['bogus_filter'] = $request->has('bogus_filter');
        $data['sequential_ip'] = $request->has('sequential_ip');
        $data['localise_queries'] = $request->has('localise_queries');
        $data['private_filter'] = $request->has('private_filter');
        $data['expand_hosts'] = $request->has('expand_hosts');

        session(['dhcpdns_advanced' => $data]);

        return back()->with('success', $this->getSuccessMessage($request, 'Configuración avanzada guardada'));
    }

    /*
    |--------------------------------------------------------------------------
    | DHCP Y DNS - ASIGNACIONES ESTÁTICAS
    |--------------------------------------------------------------------------
    */

    public function updateDhcpDnsStatic(Request $request)
    {
        return back()->with('success', $this->getSuccessMessage($request, 'Configuración de asignaciones estáticas guardada'));
    }

    public function storeDhcpDnsStatic(Request $request)
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
