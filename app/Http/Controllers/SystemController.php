<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RouterSshService;
use Illuminate\Support\Facades\Log;

class SystemController extends Controller
{
    protected RouterSshService $router;

    public function __construct(RouterSshService $router)
    {
        $this->router = $router;
    }

    /* =========================
       LEDs
    ========================= */

    private array $ledNames = [
        'green:lan', 'green:wan', 'green:wlan', 'mt76-phy0', 'orange:wan',
    ];

    private array $disparadores = [
        'defaulton', 'netdev', 'none', 'phy0assoc', 'phy0radio',
        'phy0rx', 'phy0tpt', 'phy0tx', 'switch0', 'timer',
    ];

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
        return view('system.leds.leds_index', compact('leds'));
    }

    public function createLed()
    {
        return view('system.leds.leds_form', [
            'led'         => null,
            'ledNames'    => $this->ledNames,
            'disparadores' => $this->disparadores,
        ]);
    }

    public function storeLed(Request $request)
    {
        $request->validate([
            'nombre'     => ['required', 'string', 'max:50'],
            'led_name'   => ['required', 'string'],
            'disparador' => ['required', 'string'],
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
                $modo   = implode(' ', $request->input('modo_disparador', []));
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
        return view('system.leds.leds_form', [
            'led'          => $led,
            'ledNames'     => $this->ledNames,
            'disparadores' => $this->disparadores,
        ]);
    }

    public function updateLed(Request $request, $key)
    {
        $request->validate([
            'nombre'     => ['required', 'string', 'max:50'],
            'led_name'   => ['required', 'string'],
            'disparador' => ['required', 'string'],
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
                $modo   = implode(' ', $request->input('modo_disparador', []));
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