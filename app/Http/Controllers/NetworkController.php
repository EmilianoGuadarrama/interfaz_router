<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NetworkController extends Controller
{
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
        $config = session('dhcpdns_general', [
            'local_service' => '/lan/',
            'local_domain' => 'lan',
            'dns_forwardings' => '/example.org/10.1.2.3',
            'domain_whitelist' => 'ihost.netflix.com',
            'require_domain' => true,
            'authoritative' => true,
            'log_queries' => false,
            'local_only' => true,
        ]);

        return view('network.dhcpdns.general', compact('config'));
    }

    public function updateDhcpDnsGeneral(Request $request)
    {
        $data = $request->validate([
            'local_service' => 'nullable|string|max:255',
            'local_domain' => 'nullable|string|max:255',
            'dns_forwardings' => 'nullable|string|max:255',
            'domain_whitelist' => 'nullable|string|max:255',
        ]);

        $data['require_domain'] = $request->has('require_domain');
        $data['authoritative'] = $request->has('authoritative');
        $data['log_queries'] = $request->has('log_queries');
        $data['local_only'] = $request->has('local_only');

        session(['dhcpdns_general' => $data]);

        return back()->with('success', $this->getSuccessMessage($request, 'Configuración general guardada'));
    }

    /*
    |--------------------------------------------------------------------------
    | DHCP Y DNS - ARCHIVOS RESOLV Y HOSTS
    |--------------------------------------------------------------------------
    */

    public function dhcpDnsResolvHosts()
    {
        $config = session('dhcpdns_resolv', [
            'use_ethers' => true,
            'ignore_resolv' => false,
            'ignore_hosts' => false,
            'lease_file' => '/tmp/dhcp.leases',
            'resolv_file' => '/tmp/resolv.conf.auto',
            'additional_hosts' => '',
        ]);

        return view('network.dhcpdns.resolv-hosts', compact('config'));
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

    public function dhcpDnsStatic()
    {
        $staticAssignments = session('dhcp_static_assignments', []);

        $activeLeases = session('dhcp_active_leases', [
            [
                'host_name' => 'Susu',
                'ipv4_address' => '192.168.10.180',
                'mac_address' => '50:EB:F6:D1:96:1E',
                'remaining_time' => '11h 56m 51s',
            ]
        ]);

        return view('network.dhcpdns.static', compact('staticAssignments', 'activeLeases'));
    }

    public function updateDhcpDnsStatic(Request $request)
    {
        return back()->with('success', $this->getSuccessMessage($request, 'Configuración de asignaciones estáticas guardada'));
    }

    public function storeDhcpDnsStatic(Request $request)
    {
        $data = $request->validate([
            'host_name' => 'required|string|max:100',
            'mac_address' => ['required', 'regex:/^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/'],
            'ipv4_address' => 'required|ip',
            'lease_time' => 'nullable|string|max:50',
            'duid' => 'nullable|string|max:100',
            'ipv6_suffix' => 'nullable|string|max:100',
        ]);

        $assignments = session('dhcp_static_assignments', []);

        $assignments[] = [
            'host_name' => $data['host_name'],
            'mac_address' => strtoupper($data['mac_address']),
            'ipv4_address' => $data['ipv4_address'],
            'lease_time' => $data['lease_time'] ?? '',
            'duid' => $data['duid'] ?? '',
            'ipv6_suffix' => $data['ipv6_suffix'] ?? '',
        ];

        session(['dhcp_static_assignments' => $assignments]);

        return redirect()
            ->route('network.dhcpdns.static')
            ->with('success', 'Asignación estática agregada correctamente.');
    }

    public function destroyDhcpDnsStatic($index)
    {
        $assignments = session('dhcp_static_assignments', []);

        if (isset($assignments[$index])) {
            unset($assignments[$index]);
            $assignments = array_values($assignments);
            session(['dhcp_static_assignments' => $assignments]);
        }

        return redirect()
            ->route('network.dhcpdns.static')
            ->with('success', 'Asignación estática eliminada correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | NOMBRES DE HOST
    |--------------------------------------------------------------------------
    */

    public function hostEntries()
    {
        $hosts = session('host_entries', []);

        return view('network.hostentries', compact('hosts'));
    }

    public function storeHostEntry(Request $request)
    {
        $data = $request->validate([
            'host_name' => 'required|string|max:100',
            'ip_address' => 'required|ip',
        ]);

        $hosts = session('host_entries', []);
        $hosts[] = $data;

        session(['host_entries' => $hosts]);

        return back()->with('success', 'Nombre de host agregado correctamente.');
    }

    public function destroyHostEntry(Request $request)
    {
        $index = $request->input('index');
        $hosts = session('host_entries', []);

        if (isset($hosts[$index])) {
            unset($hosts[$index]);
            $hosts = array_values($hosts);
            session(['host_entries' => $hosts]);
        }

        return back()->with('success', 'Nombre de host eliminado correctamente.');
    }

    /*
    |--------------------------------------------------------------------------
    | MÉTODO AUXILIAR
    |--------------------------------------------------------------------------
    */

    private function getSuccessMessage(Request $request, string $defaultMessage): string
    {
        return $request->input('submit_action') === 'apply'
            ? $defaultMessage . ' y aplicada correctamente.'
            : $defaultMessage . '.';
    }
}
