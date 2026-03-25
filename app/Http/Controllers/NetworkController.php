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
        return view('network.dhcpdns.static');
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
}
