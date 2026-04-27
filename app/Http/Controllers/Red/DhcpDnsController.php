<?php

namespace App\Http\Controllers\Red;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RouterSshService;
use Illuminate\Support\Facades\Log;

class DhcpDnsController extends Controller
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
        return redirect()->route('red.dhcpdns.general');
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN GENERAL (dnsmasq)
    |--------------------------------------------------------------------------
    */

    public function general()
    {
        $config = [
            'local_service' => '/lan/',
            'local_domain'  => 'lan',
            'dns_forwardings' => '',
            'domain_whitelist' => '',
            'require_domain' => true,
            'authoritative'  => true,
            'log_queries'    => false,
            'local_only'     => true,
        ];

        try {
            $result = $this->router->execute([
                "uci -q get dhcp.@dnsmasq[0].local",
                "uci -q get dhcp.@dnsmasq[0].domain",
                "uci -q get dhcp.@dnsmasq[0].server",
                "uci -q get dhcp.@dnsmasq[0].domainneeded",
                "uci -q get dhcp.@dnsmasq[0].authoritative",
                "uci -q get dhcp.@dnsmasq[0].logqueries",
                "uci -q get dhcp.@dnsmasq[0].localservice",
            ]);

            $lines = array_filter(explode("\n", $result['output']), function ($line) {
                return !empty(trim($line)) && !str_starts_with(trim($line), '>>>');
            });

            // Parse values from UCI output when available
            // The config will be populated from session or defaults for now
        } catch (\Throwable $e) {
            Log::warning('DhcpDns general: No se pudo leer config del router: ' . $e->getMessage());
        }

        // Merge with session data if previously saved
        $config = array_merge($config, session('dhcpdns_general', []));

        return view('network.dhcpdns.general', compact('config'));
    }

    public function updateGeneral(Request $request)
    {
        $data = $request->validate([
            'local_service'    => 'nullable|string|max:255',
            'local_domain'     => 'nullable|string|max:255|regex:/^[a-zA-Z0-9\.\-]+$/',
            'dns_forwardings'  => 'nullable|string|max:255',
            'domain_whitelist' => 'nullable|string|max:255',
        ], [
            'local_domain.regex' => 'El dominio local solo puede contener letras, números, puntos y guiones.',
        ]);

        $data['require_domain'] = $request->has('require_domain');
        $data['authoritative']  = $request->has('authoritative');
        $data['log_queries']    = $request->has('log_queries');
        $data['local_only']     = $request->has('local_only');

        try {
            $cmds = [];

            // Servidor local
            if (!empty($data['local_service'])) {
                $cmds[] = "uci set dhcp.@dnsmasq[0].local='{$data['local_service']}'";
            }

            // Dominio local
            if (!empty($data['local_domain'])) {
                $cmds[] = "uci set dhcp.@dnsmasq[0].domain='{$data['local_domain']}'";
            }

            // Reenvíos DNS
            $cmds[] = "uci -q delete dhcp.@dnsmasq[0].server || true";
            if (!empty($data['dns_forwardings'])) {
                $forwards = array_filter(explode("\n", str_replace(["\r\n", ",", " "], "\n", $data['dns_forwardings'])));
                foreach ($forwards as $fwd) {
                    $fwd = trim($fwd);
                    if (!empty($fwd)) {
                        $cmds[] = "uci add_list dhcp.@dnsmasq[0].server='{$fwd}'";
                    }
                }
            }

            // Lista blanca de dominios
            if (!empty($data['domain_whitelist'])) {
                $cmds[] = "uci set dhcp.@dnsmasq[0].boguspriv='{$data['domain_whitelist']}'";
            }

            // Booleans
            $cmds[] = "uci set dhcp.@dnsmasq[0].domainneeded='" . ($data['require_domain'] ? '1' : '0') . "'";
            $cmds[] = "uci set dhcp.@dnsmasq[0].authoritative='" . ($data['authoritative'] ? '1' : '0') . "'";
            $cmds[] = "uci set dhcp.@dnsmasq[0].logqueries='" . ($data['log_queries'] ? '1' : '0') . "'";
            $cmds[] = "uci set dhcp.@dnsmasq[0].localservice='" . ($data['local_only'] ? '1' : '0') . "'";

            $cmds[] = "uci commit dhcp";

            // Si el usuario eligió "Guardar y aplicar", reiniciar dnsmasq
            if ($request->input('submit_action') === 'apply') {
                $cmds[] = "/etc/init.d/dnsmasq restart";
            }

            $result = $this->router->execute($cmds);

            // Guardar en sesión para mantener valores
            session(['dhcpdns_general' => $data]);

            if ($result['success']) {
                $msg = $request->input('submit_action') === 'apply'
                    ? 'Configuración general guardada y aplicada correctamente.'
                    : 'Configuración general guardada correctamente.';
                return back()->with('success', $msg);
            } else {
                Log::error('DhcpDns updateGeneral error: ' . $result['output']);
                return back()->withInput()->with('error', 'Error al aplicar la configuración en el router.');
            }
        } catch (\Throwable $e) {
            Log::error('DhcpDns updateGeneral exception: ' . $e->getMessage());
            // Guardar en sesión incluso si falla SSH
            session(['dhcpdns_general' => $data]);
            return back()->withInput()->with('error', 'No se pudo conectar con el router: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ARCHIVOS RESOLV Y HOSTS
    |--------------------------------------------------------------------------
    */

    public function resolvHosts()
    {
        $config = session('dhcpdns_resolv', [
            'use_ethers'       => true,
            'ignore_resolv'    => false,
            'ignore_hosts'     => false,
            'lease_file'       => '/tmp/dhcp.leases',
            'resolv_file'      => '/tmp/resolv.conf.auto',
            'additional_hosts' => '',
        ]);

        return view('network.dhcpdns.resolv-hosts', compact('config'));
    }

    public function updateResolvHosts(Request $request)
    {
        $data = $request->validate([
            'lease_file'       => 'nullable|string|max:255',
            'resolv_file'      => 'nullable|string|max:255',
            'additional_hosts' => 'nullable|string|max:255',
        ]);

        $data['use_ethers']    = $request->has('use_ethers');
        $data['ignore_resolv'] = $request->has('ignore_resolv');
        $data['ignore_hosts']  = $request->has('ignore_hosts');

        try {
            $cmds = [];

            $cmds[] = "uci set dhcp.@dnsmasq[0].readethers='" . ($data['use_ethers'] ? '1' : '0') . "'";
            $cmds[] = "uci set dhcp.@dnsmasq[0].noresolv='" . ($data['ignore_resolv'] ? '1' : '0') . "'";
            $cmds[] = "uci set dhcp.@dnsmasq[0].nohosts='" . ($data['ignore_hosts'] ? '1' : '0') . "'";

            if (!empty($data['lease_file'])) {
                $cmds[] = "uci set dhcp.@dnsmasq[0].leasefile='{$data['lease_file']}'";
            }
            if (!empty($data['resolv_file'])) {
                $cmds[] = "uci set dhcp.@dnsmasq[0].resolvfile='{$data['resolv_file']}'";
            }
            if (!empty($data['additional_hosts'])) {
                $cmds[] = "uci set dhcp.@dnsmasq[0].addnhosts='{$data['additional_hosts']}'";
            }

            $cmds[] = "uci commit dhcp";

            if ($request->input('submit_action') === 'apply') {
                $cmds[] = "/etc/init.d/dnsmasq restart";
            }

            $result = $this->router->execute($cmds);
            session(['dhcpdns_resolv' => $data]);

            if ($result['success']) {
                $msg = $request->input('submit_action') === 'apply'
                    ? 'Configuración de Resolv y Hosts guardada y aplicada correctamente.'
                    : 'Configuración de Resolv y Hosts guardada correctamente.';
                return back()->with('success', $msg);
            } else {
                Log::error('DhcpDns updateResolvHosts error: ' . $result['output']);
                return back()->withInput()->with('error', 'Error al aplicar la configuración de Resolv/Hosts.');
            }
        } catch (\Throwable $e) {
            Log::error('DhcpDns updateResolvHosts exception: ' . $e->getMessage());
            session(['dhcpdns_resolv' => $data]);
            return back()->withInput()->with('error', 'No se pudo conectar con el router.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN TFTP
    |--------------------------------------------------------------------------
    */

    public function tftp()
    {
        $config = session('dhcpdns_tftp', [
            'enable_tftp' => false,
            'tftp_root' => '/srv/tftp',
            'network_boot_image' => '',
        ]);

        return view('network.dhcpdns.tftp', compact('config'));
    }

    public function updateTftp(Request $request)
    {
        $data = $request->validate([
            'tftp_root' => 'nullable|string|max:255',
            'network_boot_image' => 'nullable|string|max:255',
        ]);
        
        $data['enable_tftp'] = $request->has('enable_tftp');

        try {
            $cmds = [];
            $cmds[] = "uci set dhcp.@dnsmasq[0].enable_tftp='" . ($data['enable_tftp'] ? '1' : '0') . "'";

            if ($data['enable_tftp']) {
                $root = !empty($data['tftp_root']) ? $data['tftp_root'] : '/srv/tftp';
                $cmds[] = "uci set dhcp.@dnsmasq[0].tftp_root='{$root}'";
                
                if (!empty($data['network_boot_image'])) {
                    $cmds[] = "uci set dhcp.@dnsmasq[0].dhcp_boot='{$data['network_boot_image']}'";
                } else {
                    $cmds[] = "uci -q delete dhcp.@dnsmasq[0].dhcp_boot || true";
                }
            }

            $cmds[] = "uci commit dhcp";

            if ($request->input('submit_action') === 'apply') {
                $cmds[] = "/etc/init.d/dnsmasq restart";
            }

            $result = $this->router->execute($cmds);
            session(['dhcpdns_tftp' => $data]);

            if ($result['success']) {
                $msg = $request->input('submit_action') === 'apply'
                    ? 'Configuración TFTP guardada y aplicada correctamente.'
                    : 'Configuración TFTP guardada correctamente.';
                return back()->with('success', $msg);
            } else {
                Log::error('DhcpDns updateTftp error: ' . $result['output']);
                return back()->withInput()->with('error', 'Error al aplicar la configuración TFTP.');
            }
        } catch (\Throwable $e) {
            Log::error('DhcpDns updateTftp exception: ' . $e->getMessage());
            session(['dhcpdns_tftp' => $data]);
            return back()->withInput()->with('error', 'No se pudo conectar con el router.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN AVANZADA
    |--------------------------------------------------------------------------
    */

    public function advanced()
    {
        $config = session('dhcpdns_advanced', [
            'suppress_log'           => false,
            'bogus_filter'           => false,
            'sequential_ip'          => false,
            'localise_queries'       => true,
            'private_filter'         => true,
            'expand_hosts'           => true,
            'additional_servers_file' => '',
            'bogus_nxdomain'         => '67.215.65.132',
            'dns_port'               => 53,
            'dns_query_port'         => 'cualquiera',
            'dhcp_max'               => 'ilimitado',
            'edns_packet_max'        => 1280,
            'dns_forward_max'        => 150,
            'cache_size'             => 150,
        ]);

        return view('network.dhcpdns.advanced', compact('config'));
    }

    public function updateAdvanced(Request $request)
    {
        $data = $request->validate([
            'additional_servers_file' => 'nullable|string|max:255',
            'bogus_nxdomain'         => 'nullable|string|max:255',
            'dns_port'               => 'nullable|integer|min:1|max:65535',
            'dns_query_port'         => 'nullable|string|max:100',
            'dhcp_max'               => 'nullable|string|max:100',
            'edns_packet_max'        => 'nullable|integer|min:1',
            'dns_forward_max'        => 'nullable|integer|min:1',
            'cache_size'             => 'nullable|integer|min:0',
        ]);

        $data['suppress_log']     = $request->has('suppress_log');
        $data['bogus_filter']     = $request->has('bogus_filter');
        $data['sequential_ip']    = $request->has('sequential_ip');
        $data['localise_queries'] = $request->has('localise_queries');
        $data['private_filter']   = $request->has('private_filter');
        $data['expand_hosts']     = $request->has('expand_hosts');

        try {
            $cmds = [];

            // Booleans
            $cmds[] = "uci set dhcp.@dnsmasq[0].quietdhcp='" . ($data['suppress_log'] ? '1' : '0') . "'";
            $cmds[] = "uci set dhcp.@dnsmasq[0].boguspriv='" . ($data['bogus_filter'] ? '1' : '0') . "'";
            $cmds[] = "uci set dhcp.@dnsmasq[0].sequential_ip='" . ($data['sequential_ip'] ? '1' : '0') . "'";
            $cmds[] = "uci set dhcp.@dnsmasq[0].localise_queries='" . ($data['localise_queries'] ? '1' : '0') . "'";
            $cmds[] = "uci set dhcp.@dnsmasq[0].rebind_protection='" . ($data['private_filter'] ? '1' : '0') . "'";
            $cmds[] = "uci set dhcp.@dnsmasq[0].expandhosts='" . ($data['expand_hosts'] ? '1' : '0') . "'";

            // Campos de texto/número
            if (!empty($data['additional_servers_file'])) {
                $cmds[] = "uci set dhcp.@dnsmasq[0].serversfile='{$data['additional_servers_file']}'";
            }
            if (!empty($data['bogus_nxdomain'])) {
                $cmds[] = "uci -q delete dhcp.@dnsmasq[0].bogusnxdomain || true";
                $cmds[] = "uci add_list dhcp.@dnsmasq[0].bogusnxdomain='{$data['bogus_nxdomain']}'";
            }
            if (!empty($data['dns_port'])) {
                $cmds[] = "uci set dhcp.@dnsmasq[0].port='{$data['dns_port']}'";
            }
            if (!empty($data['edns_packet_max'])) {
                $cmds[] = "uci set dhcp.@dnsmasq[0].ednspacket_max='{$data['edns_packet_max']}'";
            }
            if (!empty($data['dns_forward_max'])) {
                $cmds[] = "uci set dhcp.@dnsmasq[0].dnsforwardmax='{$data['dns_forward_max']}'";
            }
            if (isset($data['cache_size'])) {
                $cmds[] = "uci set dhcp.@dnsmasq[0].cachesize='{$data['cache_size']}'";
            }

            $cmds[] = "uci commit dhcp";

            if ($request->input('submit_action') === 'apply') {
                $cmds[] = "/etc/init.d/dnsmasq restart";
            }

            $result = $this->router->execute($cmds);
            session(['dhcpdns_advanced' => $data]);

            if ($result['success']) {
                $msg = $request->input('submit_action') === 'apply'
                    ? 'Configuración avanzada guardada y aplicada correctamente.'
                    : 'Configuración avanzada guardada correctamente.';
                return back()->with('success', $msg);
            } else {
                Log::error('DhcpDns updateAdvanced error: ' . $result['output']);
                return back()->withInput()->with('error', 'Error al aplicar la configuración avanzada.');
            }
        } catch (\Throwable $e) {
            Log::error('DhcpDns updateAdvanced exception: ' . $e->getMessage());
            session(['dhcpdns_advanced' => $data]);
            return back()->withInput()->with('error', 'No se pudo conectar con el router.');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ASIGNACIONES ESTÁTICAS
    |--------------------------------------------------------------------------
    */

    public function staticLeases()
    {
        $staticEntries = [];
        $activeLeases  = [];

        try {
            // Leer asignaciones estáticas del router vía UCI
            $staticResult = $this->router->execute([
                "uci show dhcp | grep -E 'host\\['"
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
                    'name'      => $entry['name'] ?? '-',
                    'mac'       => $entry['mac'] ?? '-',
                    'ip'        => $entry['ip'] ?? '-',
                    'leasetime' => $entry['leasetime'] ?? '-',
                    'duid'      => $entry['duid'] ?? '-',
                    'hostid'    => $entry['hostid'] ?? '-',
                ];
            }

            // Leer arrendamientos DHCP activos
            $leasesResult = $this->router->execute([
                "cat /tmp/dhcp.leases"
            ]);

            foreach (explode("\n", $leasesResult['output']) as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '>>>'))
                    continue;

                $parts = preg_split('/\s+/', $line);
                if (count($parts) >= 4) {
                    $seconds   = (int) $parts[0] - time();
                    $remaining = $seconds > 0
                        ? sprintf('%dh %dm %ds', $seconds / 3600, ($seconds % 3600) / 60, $seconds % 60)
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
            Log::error('DhcpDns staticLeases error: ' . $e->getMessage());
        }

        return view('network.dhcpdns.static', compact('staticEntries', 'activeLeases'));
    }

    public function storeStaticLease(Request $request)
    {
        $data = $request->validate([
            'host_name'    => ['required', 'string', 'max:63', 'regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]*[a-zA-Z0-9])?$/'],
            'mac_address'  => ['required', 'string', 'regex:/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/'],
            'ipv4_address' => ['required', 'ip'],
            'lease_time'   => ['nullable', 'string', 'max:20'],
            'duid'         => ['nullable', 'string', 'max:255'],
            'ipv6_suffix'  => ['nullable', 'string', 'max:255'],
        ], [
            'host_name.required'   => 'El nombre de host es obligatorio.',
            'host_name.regex'      => 'El nombre solo puede contener letras, números y guiones.',
            'mac_address.required' => 'La dirección MAC es obligatoria.',
            'mac_address.regex'    => 'El formato de MAC debe ser AA:BB:CC:DD:EE:FF.',
            'ipv4_address.required' => 'La dirección IPv4 es obligatoria.',
            'ipv4_address.ip'      => 'Ingresa una dirección IP válida.',
        ]);

        try {
            $cmds = [
                "uci add dhcp host",
                "uci set dhcp.@host[-1].name='{$data['host_name']}'",
                "uci set dhcp.@host[-1].mac='{$data['mac_address']}'",
                "uci set dhcp.@host[-1].ip='{$data['ipv4_address']}'",
            ];

            if (!empty($data['lease_time'])) {
                $cmds[] = "uci set dhcp.@host[-1].leasetime='{$data['lease_time']}'";
            }
            if (!empty($data['duid'])) {
                $cmds[] = "uci set dhcp.@host[-1].duid='{$data['duid']}'";
            }
            if (!empty($data['ipv6_suffix'])) {
                $cmds[] = "uci set dhcp.@host[-1].hostid='{$data['ipv6_suffix']}'";
            }

            $cmds[] = "uci commit dhcp";
            $cmds[] = "/etc/init.d/dnsmasq restart";

            $result = $this->router->execute($cmds);

            if ($result['success']) {
                return redirect()->route('red.dhcpdns.static')
                    ->with('success', 'Asignación estática agregada correctamente.');
            } else {
                Log::error('DhcpDns storeStaticLease error: ' . $result['output']);
                return back()->withInput()->with('error', 'Error al agregar la asignación estática.');
            }
        } catch (\Throwable $e) {
            Log::error('DhcpDns storeStaticLease exception: ' . $e->getMessage());
            return back()->withInput()->with('error', 'No se pudo conectar con el router.');
        }
    }

    public function destroyStaticLease(Request $request, $index)
    {
        $index = (int) $index;

        try {
            $cmds = [
                "uci delete dhcp.@host[{$index}]",
                "uci commit dhcp",
                "/etc/init.d/dnsmasq restart",
            ];

            $result = $this->router->execute($cmds);

            if ($result['success']) {
                return redirect()->route('red.dhcpdns.static')
                    ->with('success', 'Asignación estática eliminada correctamente.');
            } else {
                Log::error('DhcpDns destroyStaticLease error: ' . $result['output']);
                return back()->with('error', 'Error al eliminar la asignación estática.');
            }
        } catch (\Throwable $e) {
            Log::error('DhcpDns destroyStaticLease exception: ' . $e->getMessage());
            return back()->with('error', 'No se pudo conectar con el router.');
        }
    }
}
