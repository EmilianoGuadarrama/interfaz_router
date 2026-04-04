<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RouterSshService;
use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SFTP;

class SystemController extends Controller
{
    protected RouterSshService $router;

    public function __construct(RouterSshService $router)
    {
        $this->router = $router;
    }

    //LEDS

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

        if (empty($leds)) {
            $leds = [
                ['key' => 'wlan', 'nombre' => 'wlan', 'led_name' => 'green:wlan', 'estado' => 'Apagado', 'disparador' => 'netdev',  'modo' => [], 'timer_on' => null, 'timer_off' => null],
                ['key' => 'wan',  'nombre' => 'wan',  'led_name' => 'orange:wan', 'estado' => 'Apagado', 'disparador' => 'switch0', 'modo' => [], 'timer_on' => null, 'timer_off' => null],
                ['key' => 'lan',  'nombre' => 'lan',  'led_name' => 'green:lan',  'estado' => 'Apagado', 'disparador' => 'switch0', 'modo' => [], 'timer_on' => null, 'timer_off' => null],
            ];
        }

        return view('system.leds.leds_index', compact('leds'));
    }

    public function createLed()
    {
        return view('system.leds.leds_form', [
            'led'          => null,
            'ledNames'     => $this->ledNames,
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

public function grabado()
{
    $mtdblocks = [];
    try {
        $result = $this->router->execute(["cat /proc/mtd"]);
        foreach (explode("\n", $result['output']) as $line) {
            if (preg_match('/^(mtd\d+):.+"(.+)"/', trim($line), $m)) {
                $mtdblocks[] = ['device' => $m[1], 'name' => $m[2]];
            }
        }
    } catch (\Throwable $e) {
        Log::error('Grabado mtd: ' . $e->getMessage());
    }
 
    if (empty($mtdblocks)) {
        $mtdblocks = [
            ['device' => 'mtd0', 'name' => 'boot'],
            ['device' => 'mtd1', 'name' => 'kernel'],
            ['device' => 'mtd2', 'name' => 'rootfs'],
            ['device' => 'mtd3', 'name' => 'rootfs_data'],
        ];
    }
 
    // Lista de archivos a resguardar
    $listaArchivos = '';
    try {
        $r = $this->router->execute(["cat /etc/sysupgrade.conf"]);
        if ($r['success']) $listaArchivos = $r['output'];
    } catch (\Throwable $e) {
        Log::error('Lista archivos: ' . $e->getMessage());
    }
 
    return view('system.grabado.grabado', compact('mtdblocks', 'listaArchivos'));
}
 
public function guardarLista(Request $request)
{
    $request->validate(['lista_contenido' => ['required', 'string']]);
 
    try {
        $contenido = addslashes($request->lista_contenido);
        $result    = $this->router->execute([
            "echo \"{$contenido}\" > /etc/sysupgrade.conf"
        ]);
        return back()->with([
            'result_success' => $result['success'],
            'result_title'   => $result['success'] ? 'Lista guardada correctamente' : 'Error al guardar la lista',
        ]);
    } catch (\Throwable $e) {
        Log::error('Guardar lista: ' . $e->getMessage());
        return back()->with(['result_success' => false, 'result_title' => 'Error de conexión']);
    }

}
// GET /reiniciar
public function reiniciar()
{
   return view('system.reiniciar.reiniciar');
}

// POST /reiniciar/run
public function reiniciarRun()
{
    try {
        $result = $this->router->execute(['reboot now']);
        return redirect()->route('reiniciar.index')
            ->with('success', 'El dispositivo se está reiniciando...');
    } catch (\Throwable $e) {
        Log::error('Reiniciar: ' . $e->getMessage());
        return redirect()->route('reiniciar.index')
            ->with('error', 'No se pudo enviar la orden de reinicio.');
    }
}
   
}