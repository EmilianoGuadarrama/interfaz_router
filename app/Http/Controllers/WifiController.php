<?php

namespace App\Http\Controllers;

use App\Services\WifiService;
use Illuminate\Http\Request;
use Exception;

class WifiController extends Controller
{
    /**
     * @var WifiService
     */
    protected $wifiService;

    /**
     * Constructor con inyección del servicio WifiService.
     *
     * @param WifiService $wifiService
     */
    public function __construct(WifiService $wifiService)
    {
        $this->wifiService = $wifiService;
    }

    /**
     * Muestra la vista de configuración del WiFi.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        try {
            $wifi = $this->wifiService->getWifiStatus();
            $connectedDevices = $this->wifiService->getConnectedDevices();
            return view('wifi.index', compact('wifi', 'connectedDevices'));
        } catch (Exception $e) {
            return back()->with('error', 'Error al cargar el estado de la red WiFi: ' . $e->getMessage());
        }
    }

    /**
     * Valida y actualiza el SSID de la red WiFi.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSSID(Request $request)
    {
        $request->validate([
            'ssid' => ['required', 'string', 'max:50'],
        ], [
            'ssid.required' => 'El nombre de la red (SSID) es obligatorio.',
            'ssid.max'      => 'El nombre de la red no puede exceder los 50 caracteres.',
        ]);

        try {
            $response = $this->wifiService->updateSSID($request->ssid);

            if (!$response['success']) {
                return back()->with('error', $response['message'])->withInput();
            }

            return back()->with('success', $response['message']);
        } catch (Exception $e) {
            return back()->with('error', 'Excepción al intentar actualizar el SSID: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Valida y actualiza la contraseña de la red WiFi.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ], [
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'La contraseña debe tener un mínimo de 8 caracteres.',
        ]);

        try {
            $response = $this->wifiService->updatePassword($request->password);

            if (!$response['success']) {
                return back()->with('error', $response['message']);
            }

            return back()->with('success', $response['message']);
        } catch (Exception $e) {
            return back()->with('error', 'Excepción al intentar actualizar la contraseña: ' . $e->getMessage());
        }
    }

    /**
     * Reinicia la red WiFi.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restart()
    {
        try {
            $response = $this->wifiService->restartWifi();

            if (!$response['success']) {
                return back()->with('error', $response['message']);
            }

            return back()->with('success', $response['message']);
        } catch (Exception $e) {
            return back()->with('error', 'Excepción al intentar reiniciar la red Wi-Fi: ' . $e->getMessage());
        }
    }

    /**
     * Escanea redes WiFi disponibles.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function scan()
    {
        try {
            $result = $this->wifiService->scanNetworks();
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Excepción en escaneo: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Conectarse a una nueva red WiFi desde el entorno STA.
     */
    public function connect(Request $request)
    {
        $request->validate([
            'ssid' => 'required|string',
            'password' => 'required|string|min:8',
            'network' => 'required|string',
        ]);

        try {
            $lockBssid = filter_var($request->lock_bssid, FILTER_VALIDATE_BOOLEAN);

            $result = $this->wifiService->connectToNetwork(
                $request->ssid,
                $request->password,
                $request->network,
                $request->bssid,
                $lockBssid
            );

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Excepción al intentar conectar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una interfaz WiFi específica.
     */
    public function deleteInterface(Request $request)
    {
        $request->validate([
            'interface_id' => 'required|string'
        ]);

        try {
            $result = $this->wifiService->deleteInterface($request->interface_id);
            if ($result['success']) {
                return back()->with('success', $result['message']);
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (Exception $e) {
            return back()->with('error', "Excepción al eliminar la interfaz: " . $e->getMessage());
        }
    }

    /**
     * Añade una nueva red WiFi.
     */
    public function addNetwork(Request $request)
    {
        $request->validate([
            'device' => 'required|string',
            'ssid' => 'required|string|max:50',
            'mode' => 'required|in:ap,sta',
            'network' => 'required|string',
            'encryption' => 'required|in:none,psk2',
            'password' => 'required_if:encryption,psk2|nullable|string|min:8',
            'macfilter' => 'required|in:disable,allow,deny',
            'maclist' => 'nullable|string',
            'radio_country' => 'nullable|string',
            'radio_distance' => 'nullable|string',
            'radio_frag' => 'nullable|string',
            'radio_rts' => 'nullable|string',
            'radio_beacon' => 'nullable|integer',
            'ifname' => 'nullable|string',
            'dtim_period' => 'nullable|integer',
            'wpa_group_rekey' => 'nullable|integer',
            'maxassoc' => 'nullable|integer',
            'max_listen_int' => 'nullable|integer',
        ]);

        try {
            $config = $request->all();
            
            // Asignación de valores por defecto (placeholders)
            $config['hidden'] = $request->has('hidden');
            $config['wmm'] = $request->has('wmm');
            
            // Radio Options
            $config['radio_mode'] = $request->input('radio_mode', $request->input('add_radio_mode'));
            $config['radio_channel'] = $request->input('radio_channel', $request->input('add_radio_channel'));
            $config['radio_bandwidth'] = $request->input('radio_bandwidth', $request->input('add_radio_bandwidth'));
            $config['radio_txpower'] = $request->input('radio_txpower', $request->input('add_radio_txpower'));
            
            $config['radio_legacy_rates'] = $request->has('radio_legacy_rates');
            $config['radio_distance'] = $request->input('radio_distance') ?: 'auto';
            $config['radio_frag'] = $request->input('radio_frag') ?: 'off';
            $config['radio_rts'] = $request->input('radio_rts') ?: 'off';
            $config['radio_force_40'] = $request->has('radio_force_40');
            $config['radio_beacon'] = $request->input('radio_beacon') ?: 100;

            // Interface Options
            $config['isolate'] = $request->has('isolate');
            $config['short_preamble'] = $request->has('short_preamble');
            $config['dtim_period'] = $request->input('dtim_period') ?: 2;
            $config['wpa_group_rekey'] = $request->input('wpa_group_rekey') ?: 600;
            $config['disassoc_low_ack'] = $request->has('disassoc_low_ack');
            $config['maxassoc'] = $request->input('maxassoc') ?: 300;
            $config['max_listen_int'] = $request->input('max_listen_int') ?: 65535;
            $config['disassoc_low_ack_check'] = $request->has('disassoc_low_ack_check');

            $result = $this->wifiService->addNetwork($config);

            if ($result['success']) {
                return back()->with('success', $result['message']);
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (Exception $e) {
            return back()->with('error', "Excepción al añadir red: " . $e->getMessage());
        }
    }

    /**
     * Editar una red WiFi.
     */
    public function editNetwork(Request $request)
    {
        $request->validate([
            'interface_id' => 'required|string',
            'ssid' => 'required|string|max:50',
            'mode' => 'required|in:ap,sta',
            'network' => 'required|string',
            'encryption' => 'required|in:none,psk2',
            'password' => 'nullable|string|min:8',
            'macfilter' => 'required|in:disable,allow,deny',
            'maclist' => 'nullable|string',
            'radio_country' => 'nullable|string',
            'radio_distance' => 'nullable|string',
            'radio_frag' => 'nullable|string',
            'radio_rts' => 'nullable|string',
            'radio_beacon' => 'nullable|integer',
            'ifname' => 'nullable|string',
            'dtim_period' => 'nullable|integer',
            'wpa_group_rekey' => 'nullable|integer',
            'maxassoc' => 'nullable|integer',
            'max_listen_int' => 'nullable|integer',
        ]);

        try {
            $config = $request->all();
            
            // Asignación de valores por defecto (placeholders)
            $config['hidden'] = $request->has('hidden');
            $config['wmm'] = $request->has('wmm');
            
            // Radio Options
            $config['radio_mode'] = $request->input('radio_mode', $request->input('add_radio_mode'));
            $config['radio_channel'] = $request->input('radio_channel', $request->input('add_radio_channel'));
            $config['radio_bandwidth'] = $request->input('radio_bandwidth', $request->input('add_radio_bandwidth'));
            $config['radio_txpower'] = $request->input('radio_txpower', $request->input('add_radio_txpower'));
            
            $config['radio_legacy_rates'] = $request->has('radio_legacy_rates');
            $config['radio_distance'] = $request->input('radio_distance') ?: 'auto';
            $config['radio_frag'] = $request->input('radio_frag') ?: 'off';
            $config['radio_rts'] = $request->input('radio_rts') ?: 'off';
            $config['radio_force_40'] = $request->has('radio_force_40');
            $config['radio_beacon'] = $request->input('radio_beacon') ?: 100;

            // Interface Options
            $config['isolate'] = $request->has('isolate');
            $config['short_preamble'] = $request->has('short_preamble');
            $config['dtim_period'] = $request->input('dtim_period') ?: 2;
            $config['wpa_group_rekey'] = $request->input('wpa_group_rekey') ?: 600;
            $config['disassoc_low_ack'] = $request->has('disassoc_low_ack');
            $config['maxassoc'] = $request->input('maxassoc') ?: 300;
            $config['max_listen_int'] = $request->input('max_listen_int') ?: 65535;
            $config['disassoc_low_ack_check'] = $request->has('disassoc_low_ack_check');

            $result = $this->wifiService->editNetwork($request->interface_id, $config);

            if ($result['success']) {
                return back()->with('success', $result['message']);
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (Exception $e) {
            return back()->with('error', "Excepción al editar red: " . $e->getMessage());
        }
    }
}
