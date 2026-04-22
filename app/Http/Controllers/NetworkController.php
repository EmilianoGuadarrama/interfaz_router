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
            'name.alpha_dash' => 'El nombre solo puede contener letras, nÃºmeros, guiones y guiones bajos (sin espacios).',
            'name.max' => 'El nombre no debe superar los 20 caracteres.',
            'protocol.required' => 'El protocolo es obligatorio.',
            'protocol.in' => 'El protocolo seleccionado no es vÃ¡lido.'
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
                return back()->withInput()->withErrors(['createInterface' => 'Error estructural al configurar el router. Verifique la conexiÃ³n.'])->with('error', 'Hubo un error configurando la interfaz en el router.');
            }

        } catch (\Throwable $e) {
            Log::error('Error storeInterface: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Error al procesar la creaciÃ³n de la interfaz: ' . $e->getMessage());
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
            return back()->with('error', 'Nombre de interfaz invÃ¡lido.');
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
            return back()->with('error', 'Nombre de interfaz invÃ¡lido.');
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
            return back()->with('error', 'Nombre de interfaz invÃ¡lido.');
        }

        $lowerName = strtolower($safeName);
        $criticalInterfaces = ['lan', 'wan', 'br-lan'];

        if (in_array($lowerName, $criticalInterfaces)) {
            return back()->with('error', "No se permite eliminar directamente la interfaz troncal '{$safeName}' por protecciÃ³n del sistema.");
        }

        try {
            $cmds = [
                "uci delete network.{$lowerName}",
                "uci commit network",
                "/etc/init.d/network reload"
            ];
            $result = $this->router->execute($cmds);

            if ($result['success']) {
                return back()->with('success', "Interfaz '{$safeName}' eliminada y configuraciÃ³n recargada correctamente.");
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
            return back()->with('error', 'Nombre de interfaz invÃ¡lido.');
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
            
            // Borrar campos especÃ­ficos anteriores para limpiar la estructura
            $clearFields = ['ipaddr', 'netmask', 'gateway', 'broadcast', 'ip6assign', 'ip6addr', 'ip6gw', 'ip6prefix', 'ip6ifaceid', 'hostname', 'peerdns', 'defaultroute', 'clientid', 'vendorid', 'username', 'password', 'ac', 'service', 'device', 'metric', 'macaddr', 'mtu', 'lcp_echo_failure', 'lcp_echo_interval', 'demand'];
            foreach($clearFields as $cf) {
                $cmds[] = "uci -q delete network.{$lowerName}.{$cf} || true";
            }

            // Protocolo
            $proto = $request->input('proto', 'static');
            $cmds[] = "uci set network.{$lowerName}.proto='{$proto}'";

            // Campos activos bÃ¡sicos
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
            
            // Netmask (solo estÃ¡tico)
            if ($proto === 'static') {
                $val = $request->input('netmask');
                if ($val === 'custom') {
                    $val = $request->input('custom_netmask', '');
                }
                if (!empty($val)) {
                    $cmds[] = "uci set network.{$lowerName}.netmask='{$val}'";
                }
            }

            // Atributos lÃ³gicos (booleans)
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
            
            // ConfiguraciÃ³n fÃ­sica
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
            
            // ConfiguraciÃ³n Servidor DHCP (solo guardaremos lo bÃ¡sico)
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
            
            // Usaremos un pequeÃ±o script shell a ejecutar en el router para actualizar la zona del cortafuegos de forma atÃ³mica
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
                return back()->with('success', "ConfiguraciÃ³n general de '{$safeName}' actualizada correctamente en el router.");
            } else {
                Log::error('Error updating interface ' . $safeName . ': ' . $result['output']);
                session()->flash('reopen_modal', $lowerName);
                return back()->withInput()->withErrors(['updateInterface-'.$lowerName => "FallÃ³ al aplicar configuraciÃ³n."])->with('error', "No se pudieron aplicar los cambios en OpenWrt.");
            }
        } catch (\Throwable $e) {
            Log::error('Error en updateInterface: ' . $e->getMessage());
            return back()->withInput()->with('error', "Fallo de conexiÃ³n con el router.");
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
    | NOMBRES DE HOST
    |--------------------------------------------------------------------------
    */

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
            'name.regex' => 'Solo letras, nÃºmeros y guiones. No debe haber espacios.',
            'ip.required' => 'La direcciÃ³n IP es obligatoria.',
            'ip.ip' => 'Ingresa una direcciÃ³n IP vÃ¡lida.',
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
                'result_title' => 'Error de conexiÃ³n o ejecuciÃ³n',
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
                'result_title' => 'Error de conexiÃ³n o ejecuciÃ³n',
            ]);
        }
    }

}
