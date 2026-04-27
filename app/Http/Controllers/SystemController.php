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
            $temp = [];
            foreach (explode("\n", $result['output']) as $line) {
                if (preg_match("/system\.led_(\w+)\.(name|sysfs|default|trigger|mode|delayon|delayoff)='(.+)'/", trim($line), $m)) {
                    $temp[$m[1]][$m[2]] = $m[3];
                }
            }
            foreach ($temp as $key => $e) {
                $leds[] = [
                    'key' => $key,
                    'nombre' => $e['name'] ?? $key,
                    'led_name' => $e['sysfs'] ?? '-',
                    'estado' => ($e['default'] ?? '0') === '1' ? 'Encendido' : 'Apagado',
                    'disparador' => $e['trigger'] ?? 'defaulton',
                    'modo' => isset($e['mode']) ? explode(' ', $e['mode']) : [],
                    'timer_on' => $e['delayon'] ?? null,
                    'timer_off' => $e['delayoff'] ?? null,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('LEDs: ' . $e->getMessage());
        }

        if (empty($leds)) {
            $leds = [
                ['key' => 'wlan', 'nombre' => 'wlan', 'led_name' => 'green:wlan', 'estado' => 'Apagado', 'disparador' => 'netdev', 'modo' => [], 'timer_on' => null, 'timer_off' => null],
                ['key' => 'wan', 'nombre' => 'wan', 'led_name' => 'orange:wan', 'estado' => 'Apagado', 'disparador' => 'switch0', 'modo' => [], 'timer_on' => null, 'timer_off' => null],
                ['key' => 'lan', 'nombre' => 'lan', 'led_name' => 'green:lan', 'estado' => 'Apagado', 'disparador' => 'switch0', 'modo' => [], 'timer_on' => null, 'timer_off' => null],
            ];
        }

        return view('system.leds.leds_index', compact('leds'));
    }

    public function createLed()
    {
        return view('system.leds.leds_form', [
            'led' => null,
            'ledNames' => $this->ledNames,
            'disparadores' => $this->disparadores,
        ]);
    }

    public function storeLed(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'led_name' => ['required', 'string'],
            'disparador' => ['required', 'string'],
        ]);

        try {
            $key = strtolower(preg_replace('/\s+/', '_', $request->nombre));
            $default = $request->boolean('estado_predeterminado') ? '1' : '0';

            $cmds = [
                "uci set system.led_{$key}=led",
                "uci set system.led_{$key}.name='{$request->nombre}'",
                "uci set system.led_{$key}.sysfs='{$request->led_name}'",
                "uci set system.led_{$key}.default='{$default}'",
                "uci set system.led_{$key}.trigger='{$request->disparador}'",
            ];
            if ($request->disparador === 'netdev') {
                $modo = implode(' ', $request->input('modo_disparador', []));
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
                'result_title' => $result['success'] ? 'LED creado' : 'Error al crear LED',
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
                    'key' => $key,
                    'nombre' => $e['name'] ?? $key,
                    'led_name' => $e['sysfs'] ?? '',
                    'estado' => ($e['default'] ?? '0') === '1',
                    'disparador' => $e['trigger'] ?? 'defaulton',
                    'modo' => isset($e['mode']) ? explode(' ', $e['mode']) : [],
                    'timer_on' => $e['delayon'] ?? 500,
                    'timer_off' => $e['delayoff'] ?? 500,
                ];
            }
        } catch (\Throwable $e) {
            Log::error('editLed: ' . $e->getMessage());
        }
        return view('system.leds.leds_form', [
            'led' => $led,
            'ledNames' => $this->ledNames,
            'disparadores' => $this->disparadores,
        ]);
    }

    public function updateLed(Request $request, $key)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
            'led_name' => ['required', 'string'],
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
                $modo = implode(' ', $request->input('modo_disparador', []));
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
                'result_title' => $result['success'] ? 'LED actualizado' : 'Error al actualizar',
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
                'result_title' => $result['success'] ? 'LED eliminado' : 'Error al eliminar',
            ]);
        } catch (\Throwable $e) {
            Log::error('destroyLed: ' . $e->getMessage());
            return back()->with(['result_success' => false, 'result_title' => 'Error de conexión']);
        }
    }

   //GRABADO DE IMAGEN

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

        $listaArchivos = '';
        try {
            $r = $this->router->execute(["cat /etc/sysupgrade.conf"]);
            if ($r['success']) $listaArchivos = $r['output'];
        } catch (\Throwable $e) {
            Log::error('Lista archivos: ' . $e->getMessage());
        }

        if (empty($listaArchivos)) {
            $listaArchivos = "## This file contains files and directories that should\n## be preserved during an upgrade.\n\n# /etc/example.conf\n# /etc/openvpn/";
        }

        return view('system.grabado.grabado', compact('mtdblocks', 'listaArchivos'));
    }

    public function descargarBackup()
    {
        try {
            // Paso 1: crear el backup
            $this->router->execute(["sysupgrade --create-backup /tmp/backup.tar.gz"]);

            // Paso 2: leerlo con cat
            $result = $this->router->execute(["cat /tmp/backup.tar.gz"]);

            if ($result['success'] && !empty($result['output'])) {
                return response($result['output'], 200, [
                    'Content-Type'        => 'application/x-tar',
                    'Content-Disposition' => 'attachment; filename="backup.tar.gz"',
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Backup: ' . $e->getMessage());
        }
        return back()->with(['result_success' => false, 'result_title' => 'Error al generar backup']);
    }

    public function restaurarBackup(Request $request)
    {
        $request->validate(['backup' => ['required', 'file']]);

        try {
            $file       = $request->file('backup');
            $localPath  = $file->getPathname();
            $remotePath = '/tmp/backup.tar.gz';

            $sftp = new SFTP(env('ROUTER_HOST', '192.168.10.1'), (int) env('ROUTER_PORT', 22));
            if (!$sftp->login(env('ROUTER_USER', 'root'), env('ROUTER_PASSWORD', ''))) {
                throw new \Exception('Error de autenticación SFTP.');
            }
            $sftp->put($remotePath, $localPath, SFTP::SOURCE_LOCAL_FILE);

            $result = $this->router->execute([
                "sysupgrade -r {$remotePath}",
                "rm -f {$remotePath}",
            ]);

            return back()->with([
                'result_success' => $result['success'],
                'result_title'   => $result['success'] ? 'Backup restaurado correctamente' : 'Error al restaurar backup',
            ]);

        } catch (\Throwable $e) {
            Log::error('Restaurar backup: ' . $e->getMessage());
            return back()->with(['result_success' => false, 'result_title' => 'Error al restaurar backup']);
        }
    }

    public function restablecerFabrica()
    {
        try {
            $result = $this->router->execute(["firstboot -y && reboot now"]);
            return back()->with([
                'result_success' => $result['success'],
                'result_title'   => $result['success'] ? 'Restablecimiento iniciado' : 'Error al restablecer',
            ]);
        } catch (\Throwable $e) {
            Log::error('Restablecer fábrica: ' . $e->getMessage());
            return back()->with(['result_success' => false, 'result_title' => 'Error de conexión']);
        }
    }

    public function descargarMtdblock(Request $request)
    {
        $request->validate(['mtdblock' => ['required', 'string']]);
        try {
            $device = $request->mtdblock;

            // Lee el mtdblock directamente con cat
            $result = $this->router->execute(["cat /dev/{$device}"]);

            if ($result['success'] && !empty($result['output'])) {
                return response($result['output'], 200, [
                    'Content-Type'        => 'application/octet-stream',
                    'Content-Disposition' => "attachment; filename=\"{$device}.bin\"",
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Descargar mtdblock: ' . $e->getMessage());
        }
        return back()->with(['result_success' => false, 'result_title' => 'Error al descargar mtdblock']);
    }

    public function grabarImagen(Request $request)
    {
        $request->validate(['imagen' => ['required', 'file']]);

        try {
            $file       = $request->file('imagen');
            $localPath  = $file->getPathname();
            $remotePath = '/tmp/firmware.bin';

            $sftp = new SFTP(env('ROUTER_HOST', '192.168.10.1'), (int) env('ROUTER_PORT', 22));
            if (!$sftp->login(env('ROUTER_USER', 'root'), env('ROUTER_PASSWORD', ''))) {
                throw new \Exception('Error de autenticación SFTP.');
            }
            $sftp->put($remotePath, $localPath, SFTP::SOURCE_LOCAL_FILE);

            $this->router->execute(["sysupgrade -v {$remotePath}"]);

            return back()->with([
                'result_success' => true,
                'result_title'   => 'Imagen enviada. El router se reiniciará en unos momentos.',
            ]);

        } catch (\Throwable $e) {
            Log::error('Grabar imagen: ' . $e->getMessage());
            return back()->with(['result_success' => false, 'result_title' => 'Error al grabar imagen']);
        }
    }

    public function guardarLista(Request $request)
    {
        $request->validate(['lista_contenido' => ['required', 'string']]);
        try {
            $contenido = $request->lista_contenido;
            $result    = $this->router->execute([
                "cat > /etc/sysupgrade.conf << 'EOF'\n{$contenido}\nEOF"
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

    // ARRANQUE Y TAREAS PROGRAMADAS

    private function limpiarSalidaExecute(string $output, array $commands = []): string
    {
        $clean = $output;

        $clean = preg_replace('/>>> Comando \d+:\s*/', '', $clean);

        foreach ($commands as $command) {
            $pattern = '/^' . preg_quote($command, '/') . '\s*$/m';
            $clean = preg_replace($pattern, '', $clean);
        }

        $clean = preg_replace('/^\[sin salida\]\s*$/m', '', $clean);
        $clean = preg_replace('/^__START__\s*$/m', '', $clean);
        $clean = preg_replace('/^__END__\s*$/m', '', $clean);
        $clean = preg_replace("/\n{3,}/", "\n\n", $clean);

        return trim($clean);
    }



    public function testConnection()
    {
        try {
            $commands = ['echo conectado'];
            $result = $this->router->execute($commands);
            $output = $this->limpiarSalidaExecute($result['output'], $commands);

            return response('<pre>' . e($output) . '</pre>');
        } catch (\Throwable $e) {
            Log::error('Test router: ' . $e->getMessage());
            return response('Error de conexión con el router: ' . $e->getMessage(), 500);
        }
    }

    public function startup(Request $request)
    {
        try {
            $scriptsCommands = [
                "for s in /etc/init.d/*; do [ -f \"\$s\" ] || continue; name=\$(basename \"\$s\"); enabled=0; prio='--'; for link in /etc/rc.d/S??\$name; do [ -e \"\$link\" ] || continue; enabled=1; base=\$(basename \"\$link\"); prio=\$(echo \"\$base\" | sed 's/^S\\([0-9][0-9]\\).*/\\1/'); break; done; echo \"\$prio|\$name|\$enabled\"; done | sort"
            ];

            $scriptsResult = $this->router->execute($scriptsCommands);
            $scriptsOutput = $this->limpiarSalidaExecute($scriptsResult['output'], $scriptsCommands);

            $scripts = [];

            foreach (preg_split('/\r\n|\r|\n/', $scriptsOutput) as $line) {
                $line = trim($line);

                if ($line === '') {
                    continue;
                }

                $parts = explode('|', $line);

                if (count($parts) !== 3) {
                    continue;
                }

                $scripts[] = [
                    'priority' => $parts[0],
                    'name' => $parts[1],
                    'enabled' => $parts[2] === '1',
                ];
            }

            $localCommands = [
                "[ -f /etc/rc.local ] && cat /etc/rc.local || printf 'exit 0\n'"
            ];

            $localResult = $this->router->execute($localCommands);
            $content = $this->limpiarSalidaExecute($localResult['output'], $localCommands);

            return view('system.arranque.arranque', [
                'scripts' => $scripts,
                'content' => $content,
                'activeTab' => $request->get('tab', 'scripts'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Startup: ' . $e->getMessage());

            return redirect()->route('startup', ['tab' => 'scripts'])->with([
                'result_success' => false,
                'result_title' => 'No se pudo cargar el módulo de arranque.',
            ]);
        }
    }

    public function startupScriptAction($script, $action)
    {
        $allowedActions = ['enable', 'disable', 'start', 'restart', 'stop'];

        if (!in_array($action, $allowedActions, true)) {
            return redirect()->route('startup', ['tab' => 'scripts'])->with([
                'result_success' => false,
                'result_title' => 'Acción no válida.',
            ]);
        }

        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $script)) {
            return redirect()->route('startup', ['tab' => 'scripts'])->with([
                'result_success' => false,
                'result_title' => 'Script no válido.',
            ]);
        }

        try {
            $commands = [
                "/etc/init.d/{$script} {$action}"
            ];

            $result = $this->router->execute($commands);

            return redirect()->route('startup', ['tab' => 'scripts'])->with([
                'result_success' => $result['success'],
                'result_title' => $result['success']
                    ? "Acción '{$action}' ejecutada sobre {$script}"
                    : "No se pudo ejecutar '{$action}' sobre {$script}",
            ]);
        } catch (\Throwable $e) {
            Log::error('Startup script action: ' . $e->getMessage());

            return redirect()->route('startup', ['tab' => 'scripts'])->with([
                'result_success' => false,
                'result_title' => 'Error de conexión',
            ]);
        }
    }

    public function updateStartup(Request $request)
    {
        $request->validate([
            'content' => ['required', 'string'],
        ]);

        try {
            $content = $request->input('content');

            $commands = [
                "cat > /etc/rc.local << 'EOF'\n{$content}\nEOF",
                "chmod +x /etc/rc.local"
            ];

            $result = $this->router->execute($commands);

            return redirect()->route('startup', ['tab' => 'local'])->with([
                'result_success' => $result['success'],
                'result_title' => $result['success']
                    ? 'Arranque local actualizado correctamente'
                    : 'Error al actualizar arranque local',
            ]);
        } catch (\Throwable $e) {
            Log::error('Update startup: ' . $e->getMessage());

            return redirect()->route('startup', ['tab' => 'local'])->with([
                'result_success' => false,
                'result_title' => 'Error de conexión',
            ]);
        }
    }

    public function scheduledTasks()
    {
        try {
            $prepareCommands = [
                'mkdir -p /etc/crontabs',
                'touch /etc/crontabs/root'
            ];

            $this->router->execute($prepareCommands);

            $readCommands = [
                'cat /etc/crontabs/root'
            ];

            $result = $this->router->execute($readCommands);
            $content = $this->limpiarSalidaExecute($result['output'], $readCommands);

            return view('system.tareas_programadas.tareas_programadas', compact('content'));
        } catch (\Throwable $e) {
            Log::error('Scheduled tasks: ' . $e->getMessage());

            return redirect()->route('tasks')->with([
                'result_success' => false,
                'result_title' => 'No se pudo leer el archivo de tareas programadas.',
            ]);
        }
    }

    public function updateScheduledTasks(Request $request)
    {
        $request->validate([
            'content' => ['nullable', 'string'],
        ]);

        try {
            $content = $request->input('content', '');

            $commands = [
                'mkdir -p /etc/crontabs',
                "cat > /etc/crontabs/root << 'EOF'\n{$content}\nEOF",
                '/etc/init.d/cron restart'
            ];

            $result = $this->router->execute($commands);

            return redirect()->route('tasks')->with([
                'result_success' => $result['success'],
                'result_title' => $result['success']
                    ? 'Tareas programadas actualizadas correctamente'
                    : 'Error al actualizar tareas programadas',
            ]);
        } catch (\Throwable $e) {
            Log::error('Update scheduled tasks: ' . $e->getMessage());

            return redirect()->route('tasks')->with([
                'result_success' => false,
                'result_title' => 'Error de conexión',
            ]);
        }
    }

    ////////////
    ///
    ///

    // --- SISTEMA GENERAL (Todas las pestañas) ---

    public function general()
    {
        // Valores por defecto
        $data = [
            'hostname' => 'NuupNet', 'timezone' => 'UTC', 'description' => '', 'notes' => '',
            'log_size' => '64', 'log_ip' => '', 'log_port' => '514', 'log_proto' => 'udp',
            'log_file' => '/tmp/system.log', 'conloglevel' => '8', 'cronloglevel' => '8',
            'ntp_client' => true, 'ntp_server' => false, 'ntp_dhcp' => true,
            'ntp_servers' => ['0.openwrt.pool.ntp.org', '1.openwrt.pool.ntp.org', '2.openwrt.pool.ntp.org', '3.openwrt.pool.ntp.org'],
            'lang' => 'auto', 'theme' => 'material'
        ];

        try {
            // Leemos la configuración de sistema y luci en una sola llamada
            $result = $this->router->execute(["uci show system", "uci show luci 2>/dev/null"]);
            $output = $result['output'];

            // Expresiones regulares para extraer cada valor
            if (preg_match("/system\.@system\[0\]\.hostname='(.+)'/", $output, $m)) $data['hostname'] = $m[1];
            if (preg_match("/system\.@system\[0\]\.timezone='(.+)'/", $output, $m)) $data['timezone'] = $m[1];
            if (preg_match("/system\.@system\[0\]\.description='(.+)'/", $output, $m)) $data['description'] = $m[1];
            if (preg_match("/system\.@system\[0\]\.notes='(.+)'/", $output, $m)) $data['notes'] = $m[1];

            // Logs
            if (preg_match("/system\.@system\[0\]\.log_size='(\d+)'/", $output, $m)) $data['log_size'] = $m[1];
            if (preg_match("/system\.@system\[0\]\.log_ip='(.+)'/", $output, $m)) $data['log_ip'] = $m[1];
            if (preg_match("/system\.@system\[0\]\.log_port='(\d+)'/", $output, $m)) $data['log_port'] = $m[1];
            if (preg_match("/system\.@system\[0\]\.log_proto='(.+)'/", $output, $m)) $data['log_proto'] = $m[1];
            if (preg_match("/system\.@system\[0\]\.log_file='(.+)'/", $output, $m)) $data['log_file'] = $m[1];
            if (preg_match("/system\.@system\[0\]\.conloglevel='(\d+)'/", $output, $m)) $data['conloglevel'] = $m[1];
            if (preg_match("/system\.@system\[0\]\.cronloglevel='(\d+)'/", $output, $m)) $data['cronloglevel'] = $m[1];

            // NTP
            if (preg_match("/system\.ntp\.enable_server='(1)'/", $output)) $data['ntp_server'] = true;
            if (preg_match("/system\.ntp\.use_dhcp='(0)'/", $output)) $data['ntp_dhcp'] = false;

            // Extraer lista de servidores NTP
            if (preg_match_all("/system\.ntp\.server='(.+)'/", $output, $matches)) {
                $data['ntp_servers'] = $matches[1];
            }

            // LuCI (Idioma y Tema)
            if (preg_match("/luci\.main\.lang='(.+)'/", $output, $m)) $data['lang'] = $m[1];
            if (preg_match("/luci\.main\.mediaurlbase='.*(material|bootstrap|openwrt).*'/", $output, $m)) $data['theme'] = $m[1];

        } catch (\Throwable $e) {
            Log::error('System General View: ' . $e->getMessage());
        }

        return view('system.general.index', $data);
    }

    public function updateGeneral(Request $request)
    {
        // Validamos la avalancha de datos para asegurar el router
        $request->validate([
            'hostname' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\-]+$/'],
            'timezone' => ['required', 'string'],
            'log_size' => ['nullable', 'integer'],
            'log_ip' => ['nullable', 'ip'],
            'log_port' => ['nullable', 'integer'],
            'log_proto' => ['nullable', 'in:udp,tcp'],
            'log_file' => ['nullable', 'string'],
            'ntp_servers' => ['array'],
            'ntp_servers.*' => ['nullable', 'string'],
        ]);

        try {
            $cmds = [
                // 1. Configuración General
                "uci set system.@system[0].hostname='{$request->hostname}'",
                "uci set system.@system[0].timezone='{$request->timezone}'",
                "uci set system.@system[0].description='{$request->description}'",
                "uci set system.@system[0].notes='{$request->notes}'",

                // 2. Logs ("Inicio de sesión")
                "uci set system.@system[0].log_size='{$request->log_size}'",
                "uci set system.@system[0].log_port='{$request->log_port}'",
                "uci set system.@system[0].log_proto='{$request->log_proto}'",
                "uci set system.@system[0].log_file='{$request->log_file}'",
                "uci set system.@system[0].conloglevel='{$request->conloglevel}'",
                "uci set system.@system[0].cronloglevel='{$request->cronloglevel}'",
            ];

            if ($request->filled('log_ip')) {
                $cmds[] = "uci set system.@system[0].log_ip='{$request->log_ip}'";
            } else {
                // AGREGAMOS -q AQUI
                $cmds[] = "uci -q delete system.@system[0].log_ip";
            }

            // 3. NTP (Sincronización horaria)
// ... (código anterior de logs)

            // 3. NTP (Sincronización horaria)
            $ntp_server_val = $request->boolean('ntp_server') ? '1' : '0';
            $ntp_dhcp_val = $request->boolean('ntp_dhcp') ? '1' : '0';
            $cmds[] = "uci set system.ntp.enable_server='{$ntp_server_val}'";
            $cmds[] = "uci set system.ntp.use_dhcp='{$ntp_dhcp_val}'";

            $cmds[] = "uci -q delete system.ntp.server";

            // CORRECCIÓN 1: Forzamos la separación por espacios por si vienen agrupados
            if ($request->has('ntp_servers')) {
                foreach ($request->ntp_servers as $srvInput) {
                    // Limpiamos comillas basura y separamos por espacios
                    $servers = explode(' ', str_replace(['\'', '"'], '', $srvInput));
                    foreach($servers as $srv) {
                        if (!empty(trim($srv))) {
                            $cmds[] = "uci add_list system.ntp.server='" . trim($srv) . "'";
                        }
                    }
                }
            }

            // 4. Idioma y Estilo
            $cmds[] = "uci -q set luci.main.lang='{$request->lang}'";
            $cmds[] = "uci -q set luci.main.mediaurlbase='/luci-static/{$request->theme}'";

            // Confirmar todos los cambios
            $cmds[] = "uci commit";

            // Si el botón presionado fue "GUARDAR Y APLICAR", reiniciamos los servicios
            if ($request->input('action') === 'save_apply') {
                $cmds[] = "/etc/init.d/system reload";

                // CORRECCIÓN 2: Eliminamos /etc/init.d/log restart porque no existe en tu router.
                // El system reload ya se encarga de aplicar los cambios del log.

                $cmds[] = "/etc/init.d/sysntpd restart";
                $cmds[] = "echo '{$request->hostname}' > /proc/sys/kernel/hostname";

                if ($request->boolean('ntp_client')) {
                    $cmds[] = "/etc/init.d/sysntpd enable";
                    $cmds[] = "/etc/init.d/sysntpd start";
                } else {
                    $cmds[] = "/etc/init.d/sysntpd disable";
                    $cmds[] = "/etc/init.d/sysntpd stop";
                }
            }

            $result = $this->router->execute($cmds);

            // LOG DE DEPURACIÓN
            if (!$result['success']) {
                Log::error('Fallo en comandos SSH: ' . $result['output']);
            }

            return back()->with([
                'result_success' => $result['success'],
                'result_title' => $result['success'] ? 'Configuración de sistema actualizada' : 'Error al guardar la configuración',
            ]);

        } catch (\Throwable $e) {
// ...
            Log::error('Update System General exception: ' . $e->getMessage());
            return back()->with(['result_success' => false, 'result_title' => 'Error de conexión']);
        }
    }

}
