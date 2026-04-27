<?php

namespace App\Services;

use Exception;

class WifiService
{
    /**
     * @var RouterSshService
     */
    protected $router;

    /**
     * Constructor inyectando el servicio RouterSshService existente.
     *
     * @param RouterSshService $router
     */
    public function __construct(RouterSshService $router)
    {
        $this->router = $router;
    }

    /**
     * Obtiene el estado actual del WiFi analizando la salida UCI de OpenWRT.
     *
     * @return array
     */
    public function getWifiStatus(): array
    {
        try {
            // Ejecutar comando para obtener configuración wireless
            $raw = $this->router->getRaw('uci show wireless');

            $radios = [];
            $interfaces = [];

            if (empty($raw)) {
                return [
                    'success' => false,
                    'message' => 'No se obtuvo respuesta del router o configuración vacía.',
                    'data'    => ['radios' => [], 'interfaces' => []],
                ];
            }

            // Convertir salida en array de líneas y limpiar espacios perimetrales
            $lines = array_map('trim', explode("\n", $raw));

            // Primera pasada: crear e identificar radios e interfaces
            foreach ($lines as $line) {
                if (preg_match('/^wireless\.([^\.]+)=wifi-device$/', $line, $matches)) {
                    $radios[$matches[1]] = [
                        'id' => $matches[1], 'channel' => '?', 'hwmode' => '11g', 'type' => 'mac80211'
                    ];
                } elseif (preg_match('/^wireless\.([^\.]+)=wifi-iface$/', $line, $matches)) {
                    $interfaces[$matches[1]] = [
                        'id' => $matches[1], 'ssid' => '?', 'mode' => 'ap', 'encryption' => '?', 'device' => 'radio0', 'bssid' => '?'
                    ];
                }
            }

            // Si no retornó devices pero sabemos que existen, fallback
            if (empty($radios)) {
                 $radios['radio0'] = ['id' => 'radio0', 'channel' => '?', 'hwmode' => '11bgn', 'type' => 'mac80211'];
            }

            // Segunda pasada: poblar propiedades dinámicas
            foreach ($lines as $line) {
                if (preg_match('/^wireless\.([^\.]+)\.(.+)=(.*)$/', $line, $matches)) {
                    $id = $matches[1];
                    $key = $matches[2];
                    $value = trim($matches[3], "'\"");
                    
                    if (isset($radios[$id])) {
                        $radios[$id][$key] = $value;
                    } elseif (isset($interfaces[$id])) {
                        if ($key === 'maclist') {
                            if (!isset($interfaces[$id][$key]) || !is_array($interfaces[$id][$key])) $interfaces[$id][$key] = [];
                            $interfaces[$id][$key][] = $value;
                        } else {
                            $interfaces[$id][$key] = $value;
                        }
                    } else {
                        // En caso de omisión
                        if (in_array($key, ['channel', 'hwmode', 'type', 'htmode', 'path'])) {
                            if (!isset($radios[$id])) $radios[$id] = ['id' => $id];
                            $radios[$id][$key] = $value;
                        } else {
                            if (!isset($interfaces[$id])) $interfaces[$id] = ['id' => $id];
                            $interfaces[$id][$key] = $value;
                        }
                    }
                }
            }

            return [
                'success' => true,
                'message' => 'Estado del WiFi obtenido exitosamente.',
                'data'    => ['radios' => $radios, 'interfaces' => $interfaces],
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener estado WiFi: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    /**
     * Actualiza el SSID en la primera interfaz WiFi.
     *
     * @param string $ssid
     * @return array
     */
    public function updateSSID(string $ssid): array
    {
        $ssid = trim($ssid);

        if (empty($ssid)) {
            return [
                'success' => false,
                'message' => 'El SSID no puede estar vacío.',
                'data'    => null,
            ];
        }

        try {
            $commands = [
                'uci set wireless.@wifi-iface[0].ssid="' . $ssid . '"',
                'uci commit',
                'wifi reload'
            ];

            $this->router->execute($commands);

            return [
                'success' => true,
                'message' => 'SSID actualizado correctamente a: ' . $ssid,
                'data'    => ['ssid' => $ssid],
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el SSID: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    /**
     * Actualiza la clave de red en la primera interfaz WiFi.
     *
     * @param string $password
     * @return array
     */
    public function updatePassword(string $password): array
    {
        if (strlen($password) < 8) {
            return [
                'success' => false,
                'message' => 'La contraseña debe tener un mínimo de 8 caracteres.',
                'data'    => null,
            ];
        }

        try {
            $commands = [
                'uci set wireless.@wifi-iface[0].key="' . $password . '"',
                'uci commit',
                'wifi reload'
            ];

            $this->router->execute($commands);

            return [
                'success' => true,
                'message' => 'Contraseña del WiFi actualizada correctamente.',
                'data'    => null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la contraseña: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    /**
     * Reinicia el servicio WiFi.
     *
     * @return array
     */
    public function restartWifi(): array
    {
        try {
            $this->router->execute([
                'wifi down',
                'wifi up'
            ]);

            return [
                'success' => true,
                'message' => 'El servicio Wi-Fi ha sido reiniciado correctamente.',
                'data'    => null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al intentar reiniciar el Wi-Fi: ' . $e->getMessage(),
                'data'    => null,
            ];
        }
    }

    /**
     * Escanea las redes Wi-Fi cercanas usando iwinfo.
     *
     * @return array
     */
    public function scanNetworks(): array
    {
        try {
            $raw = $this->router->getRaw('iwinfo wlan0 scan');

            if (empty($raw) || strpos($raw, 'No scan results') !== false) {
                return [
                    'success' => true,
                    'data'    => [],
                ];
            }

            $cells = explode('Cell', $raw);
            $networks = [];

            foreach ($cells as $cell) {
                if (trim($cell) === '') continue;

                $network = [];

                if (preg_match('/Address:\s*([0-9a-fA-F:]+)/i', $cell, $matches)) {
                    $network['bssid'] = $matches[1];
                }

                if (preg_match('/ESSID:\s*"([^"]+)"/i', $cell, $matches)) {
                    $ssid = trim($matches[1]);
                    if (strtolower($ssid) === 'unknown') {
                        $ssid = 'Oculta';
                    }
                    $network['ssid'] = $ssid;
                } else {
                    $network['ssid'] = 'Desconocida';
                }

                if (preg_match('/Channel:\s*(\d+)/i', $cell, $matches)) {
                    $network['channel'] = $matches[1];
                }

                if (preg_match('/Signal:\s*([-\d]+\s*dBm)/i', $cell, $matches)) {
                    $network['signal'] = $matches[1];
                } else if (preg_match('/Signal:\s*([-\d]+)\s*dBm/i', $cell, $matches)) {
                    $network['signal'] = $matches[1] . ' dBm';
                }

                if (preg_match('/Quality:\s*([\d]+\/[\d]+)/i', $cell, $matches)) {
                    $network['quality'] = $matches[1];
                }

                if (preg_match('/Encryption:\s*([^\n]+)/i', $cell, $matches)) {
                    $network['encryption'] = trim($matches[1]);
                }

                if (!empty($network['bssid'])) {
                    $network['ssid'] = $network['ssid'] ?? 'Oculta';
                    $network['channel'] = $network['channel'] ?? '-';
                    $network['signal'] = $network['signal'] ?? '-';
                    $network['quality'] = $network['quality'] ?? '-';
                    $network['encryption'] = $network['encryption'] ?? 'None';
                    
                    $networks[] = $network;
                }
            }

            return [
                'success' => true,
                'data'    => $networks,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al escanear redes WiFi: ' . $e->getMessage(),
                'data'    => [],
            ];
        }
    }

    /**
     * Conectarse a una red WiFi creando una interfaz tipo STA e integrándose a la zona WAN.
     *
     * @param string $ssid
     * @param string $password
     * @param string $network
     * @param string|null $bssid
     * @param bool $lockBssid
     * @return array
     */
    public function connectToNetwork($ssid, $password, $network, $bssid = null, $lockBssid = false): array
    {
        try {
            $realCommands = [
                // /etc/config/firewall (Adaptado con los identificadores exactos reportados)
                "uci del firewall.cfg02dc81.network || true",
                "uci add_list firewall.cfg02dc81.network='lan'",
                "uci del firewall.cfg03dc81.network || true",
                "uci add_list firewall.cfg03dc81.network='wan'",
                "uci add_list firewall.cfg03dc81.network='wan6'",
                "uci add_list firewall.cfg03dc81.network='$network'",

                // /etc/config/wireless
                "uci del wireless.wifinet1 || true",
                "uci set wireless.wifinet1=wifi-iface",
                "uci set wireless.wifinet1.ssid='$ssid'",
                "uci set wireless.wifinet1.encryption='sae'",
                "uci set wireless.wifinet1.device='radio0'",
                "uci set wireless.wifinet1.mode='sta'",
                "uci set wireless.wifinet1.key='$password'",
                "uci set wireless.wifinet1.network='$network'",
                "uci set wireless.wifinet1.encryption='psk2'"
            ];
            
            if ($lockBssid && !empty($bssid)) {
                $realCommands[] = "uci set wireless.wifinet1.bssid='$bssid'";
            }
            
            $realCommands[] = "uci commit";
            $realCommands[] = "wifi reload";
            
            $this->router->execute($realCommands);

            return [
                'success' => true,
                'message' => 'Configuración completada. El router se está conectando a la nueva red...'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'No se pudo aplicar la configuración: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene las estaciones (dispositivos WiFi) actualmente conectadas.
     *
     * @return array
     */
    public function getConnectedDevices(): array
    {
        try {
            // Obtener leases DHCP para emparejar MACs con IP y Hostname
            $leasesRaw = $this->router->getRaw('cat /tmp/dhcp.leases 2>/dev/null || cat /var/dhcp.leases 2>/dev/null || true');
            $leases = [];
            if (!empty($leasesRaw)) {
                $lines = explode("\n", trim($leasesRaw));
                foreach ($lines as $line) {
                    $parts = array_values(array_filter(explode(" ", trim($line))));
                    if (count($parts) >= 4) {
                        $mac = strtoupper($parts[1]);
                        $leases[$mac] = [
                            'ip' => $parts[2],
                            'hostname' => $parts[3] !== '*' ? $parts[3] : 'Desconocido',
                        ];
                    }
                }
            }

            // Obtener listas de interfaces activas de iwinfo
            $ifacesRaw = $this->router->getRaw('iwinfo | grep "^[a-zA-Z0-9-]\+" | awk \'{print $1}\' || true');
            $interfaces = array_filter(explode("\n", trim($ifacesRaw)));
            $devices = [];

            foreach ($interfaces as $iface) {
                $iface = trim($iface);
                if (empty($iface)) continue;

                $assocRaw = $this->router->getRaw("iwinfo $iface assoclist 2>/dev/null || true");
                if (empty($assocRaw) || strpos($assocRaw, 'No information available') !== false) {
                    continue;
                }

                $lines = explode("\n", trim($assocRaw));
                $currentMac = null;

                foreach ($lines as $line) {
                    $line = trim($line);
                    
                    // Regex para capturar MAC y parametros base
                    if (preg_match('/^([0-9A-Fa-f:]{17})\s+([-\d]+)\s*dBm/', $line, $matches)) {
                        $currentMac = strtoupper($matches[1]);
                        $signal = $matches[2] ?? '?';
                        
                        $snr = '?';
                        $rate = '?';
                        if (preg_match('/\(SNR\s+(\d+)\)\s+([\d\.]+)\sMBit\/s/', $line, $extMatches)) {
                            $snr = $extMatches[1];
                            $rate = $extMatches[2];
                        }

                        $devices[$currentMac] = [
                            'network' => $iface,
                            'mac' => $currentMac,
                            'signal' => $signal,
                            'snr' => $snr,
                            'rx_tx' => $rate . ' Mbit/s',
                            'ip' => $leases[$currentMac]['ip'] ?? 'Desconocida',
                            'hostname' => $leases[$currentMac]['hostname'] ?? '?',
                        ];
                    } elseif ($currentMac && preg_match('/RX:\s+([\d\.]+\s[KMG]?B)\s+\((\d+\sPkts)\)/', $line, $rxMatches)) {
                        // Aquí podríamos guardar el consumo RX/TX si hiciese falta
                    }
                }
            }

            return [
                'success' => true,
                'data' => array_values($devices)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => 'Error al listar clientes: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina una interfaz inalámbrica usando UCI.
     *
     * @param string $interfaceId
     * @return array
     */
    public function deleteInterface(string $interfaceId): array
    {
        try {
            // Prevenir inyección u operaciones inválidas
            if (!preg_match('/^[a-zA-Z0-9_@\[\]-]+$/', $interfaceId)) {
                throw new Exception('ID de interfaz inválido.');
            }

            $commands = [
                "uci del wireless.$interfaceId",
                "uci commit",
                "wifi reload"
            ];
            
            $this->router->execute($commands);

            return [
                'success' => true,
                'message' => "La interfaz ha sido eliminada exitosamente."
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar interfaz: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Añade una nueva red inalámbrica (interfaz).
     *
     * @param string $device
     * @param string $ssid
     * @param string $mode
     * @param string $network
     * @param string $encryption
     * @param string|null $password
     * @param bool $hidden
     * @param bool $wmm
     * @return array
     */
    public function addNetwork(string $device, string $ssid, string $mode, string $network, string $encryption, ?string $password, bool $hidden, bool $wmm, string $macfilter = 'disable', ?string $maclist = null): array
    {
        try {
            $timestamp = time();
            $ifaceId = "wifinet_{$timestamp}";

            $commands = [
                "uci set wireless.$ifaceId=wifi-iface",
                "uci set wireless.$ifaceId.device='$device'",
                "uci set wireless.$ifaceId.mode='$mode'",
                "uci set wireless.$ifaceId.ssid='$ssid'",
                "uci set wireless.$ifaceId.network='$network'",
                "uci set wireless.$ifaceId.encryption='$encryption'",
                "uci set wireless.$ifaceId.wmm='" . ($wmm ? '1' : '0') . "'"
            ];

            if ($encryption === 'psk2' && !empty($password)) {
                $commands[] = "uci set wireless.$ifaceId.key='$password'";
            }

            if ($macfilter !== 'disable') {
                $commands[] = "uci set wireless.$ifaceId.macfilter='$macfilter'";
                if (!empty($maclist)) {
                    $macs = preg_split('/[\s,]+/', trim($maclist), -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($macs as $mac) {
                        if (preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', trim($mac))) {
                            $commands[] = "uci add_list wireless.$ifaceId.maclist='" . trim($mac) . "'";
                        }
                    }
                }
            }

            if ($hidden) {
                $commands[] = "uci set wireless.$ifaceId.hidden='1'";
            }

            $commands[] = "uci commit";
            $commands[] = "wifi reload";

            $this->router->execute($commands);

            return [
                'success' => true,
                'message' => "La red '$ssid' ha sido añadida exitosamente."
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al añadir red: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Edita una red inalámbrica (interfaz) existente.
     *
     * @param string $ifaceId El Id UCI ej. wifinet1
     * @param string $ssid
     * @param string $mode
     * @param string $network
     * @param string $encryption
     * @param string|null $password
     * @param bool $hidden
     * @param bool $wmm
     * @param string $macfilter
     * @param string|null $maclist
     * @return array
     */
    public function editNetwork(string $ifaceId, string $ssid, string $mode, string $network, string $encryption, ?string $password, bool $hidden, bool $wmm, string $macfilter = 'disable', ?string $maclist = null): array
    {
        try {
            // Validar ID
            if (!preg_match('/^[a-zA-Z0-9_@\[\]-]+$/', $ifaceId)) {
                throw new Exception('ID de interfaz inválido.');
            }
            
            $commands = [
                "uci set wireless.$ifaceId.mode='$mode'",
                "uci set wireless.$ifaceId.ssid='$ssid'",
                "uci set wireless.$ifaceId.network='$network'",
                "uci set wireless.$ifaceId.encryption='$encryption'",
                "uci set wireless.$ifaceId.wmm='" . ($wmm ? '1' : '0') . "'",
                "uci delete wireless.$ifaceId.maclist || true"
            ];

            if ($encryption === 'psk2' && !empty($password)) {
                $commands[] = "uci set wireless.$ifaceId.key='$password'";
            } else if ($encryption === 'none') {
                $commands[] = "uci delete wireless.$ifaceId.key || true";
            }

            if ($macfilter !== 'disable') {
                $commands[] = "uci set wireless.$ifaceId.macfilter='$macfilter'";
                if (!empty($maclist)) {
                    $macs = preg_split('/[\s,]+/', trim($maclist), -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($macs as $mac) {
                        if (preg_match('/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/', trim($mac))) {
                            $commands[] = "uci add_list wireless.$ifaceId.maclist='" . trim($mac) . "'";
                        }
                    }
                }
            } else {
                 $commands[] = "uci delete wireless.$ifaceId.macfilter || true";
            }

            if ($hidden) {
                $commands[] = "uci set wireless.$ifaceId.hidden='1'";
            } else {
                $commands[] = "uci delete wireless.$ifaceId.hidden || true";
            }

            $commands[] = "uci commit";
            $commands[] = "wifi reload";

            $this->router->execute($commands);

            return [
                'success' => true,
                'message' => "La red '$ssid' ha sido editada exitosamente."
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al editar red: ' . $e->getMessage()
            ];
        }
    }
}
