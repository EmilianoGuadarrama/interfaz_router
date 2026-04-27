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
        ]);

        try {
            $hidden = $request->has('hidden');
            $wmm = $request->has('wmm');

            $result = $this->wifiService->addNetwork(
                $request->device,
                $request->ssid,
                $request->mode,
                $request->network,
                $request->encryption,
                $request->password,
                $hidden,
                $wmm,
                $request->macfilter,
                $request->maclist
            );

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
        ]);

        try {
            $hidden = $request->has('hidden');
            $wmm = $request->has('wmm');

            $result = $this->wifiService->editNetwork(
                $request->interface_id,
                $request->ssid,
                $request->mode,
                $request->network,
                $request->encryption,
                $request->password,
                $hidden,
                $wmm,
                $request->macfilter,
                $request->maclist
            );

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
