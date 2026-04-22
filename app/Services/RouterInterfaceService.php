<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SSH2;

class RouterInterfaceService
{
    protected RouterSshService $sshService;

    public function __construct(RouterSshService $sshService)
    {
        $this->sshService = $sshService;
    }

    public function getInterfaces(): array
    {
        $interfaces = [];
        $rawJson = '';
        
        try {
            // Priority 1: Use ubus which is standard in OpenWrt
            $rawJson = $this->sshService->getRaw('ubus call network.interface dump');
        } catch (\Exception $e) {
            Log::error('Error executing ubus: ' . $e->getMessage());
        }

        $decoded = json_decode($rawJson, true);
        
        if (is_array($decoded) && isset($decoded['interface'])) {
            $ubusInterfaces = $decoded['interface'];
            $ipAddrOutput = $this->getIpAddrOutput();
            $netDevOutput = $this->getNetDevOutput();

            foreach ($ubusInterfaces as $iface) {
                // Filter out loopback
                if ($iface['interface'] === 'loopback') {
                    continue;
                }

                $name = strtoupper($iface['interface'] ?? 'Unknown');
                $device = $iface['device'] ?? $iface['l3_device'] ?? 'N/A';
                $status = ($iface['up'] ?? false) ? 'up' : 'down';
                
                // Variant logic (verde para LAN, rojo para WAN, etc.)
                $variant = 'custom';
                $lowerName = strtolower($name);
                if (str_contains($lowerName, 'lan')) {
                    $variant = 'lan';
                } elseif (str_contains($lowerName, 'wan')) {
                    $variant = 'wan';
                }

                // UPTIME
                $uptimeSeconds = $iface['uptime'] ?? 0;
                $uptimeString = '--';
                if ($status === 'up' && $uptimeSeconds >= 0) {
                    $hours = floor($uptimeSeconds / 3600);
                    $minutes = floor(($uptimeSeconds / 60) % 60);
                    $seconds = $uptimeSeconds % 60;
                    $uptimeString = "{$hours}h {$minutes}m {$seconds}s";
                }

                // PROTOCOL
                $protocol = $iface['proto'] ?? 'Desconocido';
                $protocolMap = [
                    'static' => 'Dirección estática',
                    'dhcp' => 'Cliente DHCP',
                    'pppoe' => 'PPPoE',
                    'ppp' => 'PPP',
                    'unmanaged' => 'No administrado'
                ];
                $protocolStr = $protocolMap[$protocol] ?? ucfirst($protocol);

                // IPs
                $ipv4 = null;
                if (!empty($iface['ipv4-address'][0])) {
                    $ipv4 = $iface['ipv4-address'][0]['address'] . '/' . $iface['ipv4-address'][0]['mask'];
                }
                
                $ipv6 = null;
                if (!empty($iface['ipv6-address'][0])) {
                    $ipv6 = $iface['ipv6-address'][0]['address'] . '/' . $iface['ipv6-address'][0]['mask'];
                }

                // MAC and Stats
                $mac = '--';
                if (isset($iface['macaddr'])) {
                    $mac = strtoupper($iface['macaddr']);
                } else {
                    $mac = $this->extractMacFromIpAddr($ipAddrOutput, $device);
                }

                $stats = $this->extractStatsFromNetDev($netDevOutput, $device);
                $rxBytes = $stats['rx_bytes'] ?? 0;
                $txBytes = $stats['tx_bytes'] ?? 0;
                $rxPackets = $stats['rx_packets'] ?? 0;
                $txPackets = $stats['tx_packets'] ?? 0;

                $error = null;
                if ($status === 'down') {
                    $variant = 'error';
                    $error = 'Dispositivo de red inactivo o no presente';
                } elseif (empty($device) || $device === 'N/A') {
                    $variant = 'error';
                    $error = 'El dispositivo de red no está presente';
                }

                $interfaces[] = [
                    'name' => $name,
                    'device' => $device,
                    'protocol' => $protocolStr,
                    'status' => $status,
                    'uptime' => $uptimeString,
                    'mac' => $mac,
                    'ipv4' => $ipv4,
                    'ipv6' => $ipv6,
                    'rx_bytes' => $rxBytes,
                    'tx_bytes' => $txBytes,
                    'rx_packets' => $rxPackets,
                    'tx_packets' => $txPackets,
                    'error' => $error,
                    'variant' => $variant
                ];
            }
        } else {
            // Fallback object
            $interfaces[] = [
               'name' => 'Network',
               'device' => '--',
               'protocol' => '--',
               'status' => 'down',
               'uptime' => '--',
               'mac' => '--',
               'ipv4' => null,
               'ipv6' => null,
               'rx_bytes' => 0,
               'tx_bytes' => 0,
               'rx_packets' => 0,
               'tx_packets' => 0,
               'error' => 'No se pudo obtener información real del router. Compruebe la conexión SSH o servicio UBUS.',
               'variant' => 'error'
            ];
        }

        return $interfaces;
    }

    public function getDevices(): array
    {
        $devices = [];
        try {
            $output = (string)$this->sshService->getRaw('ls -1 /sys/class/net/');
            $lines = array_filter(explode("\n", $output));
            foreach ($lines as $line) {
                $dev = trim($line);
                if (!empty($dev) && $dev !== 'lo') {
                    $devices[] = $dev;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error getting devices: ' . $e->getMessage());
        }
        return $devices;
    }

    public function getUciConfig(): array
    {
        $config = [];
        try {
            $output = (string)$this->sshService->getRaw('uci show network');
            $lines = explode("\n", $output);
            foreach ($lines as $line) {
                if (preg_match('/^network\.([a-zA-Z0-9_\-]+)\.(.+?)=[\'"]?(.*?)[\'"]?$/', trim($line), $matches)) {
                    $iface = strtolower($matches[1]);
                    $key = strtolower($matches[2]);
                    $val = $matches[3];
                    if (!isset($config[$iface][$key])) {
                        $config[$iface][$key] = $val;
                    } else {
                        if (!is_array($config[$iface][$key])) {
                            $config[$iface][$key] = [$config[$iface][$key]];
                        }
                        $config[$iface][$key][] = $val;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error getting UCI network config: ' . $e->getMessage());
        }
        return $config;
    }

    public function getUciFirewallZones(): array
    {
        $zones = [];
        try {
            $output = (string)$this->sshService->getRaw('uci show firewall');
            $lines = explode("\n", $output);
            $tempZones = [];
            foreach ($lines as $line) {
                if (preg_match('/^firewall\.(@?zone\[\d+\])\.(name|network)=[\'"]?(.*?)[\'"]?$/', trim($line), $matches)) {
                    $zoneId = $matches[1];
                    $key = $matches[2];
                    $val = $matches[3];
                    $tempZones[$zoneId][$key] = $val;
                }
            }
            
            // Format into a friendlier array
            foreach ($tempZones as $zData) {
                if (!empty($zData['name'])) {
                    $zName = $zData['name'];
                    $networks = !empty($zData['network']) ? explode(' ', $zData['network']) : [];
                    $zones[$zName] = [
                        'name' => $zName,
                        'networks' => $networks
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Error getting UCI firewall zones: ' . $e->getMessage());
        }
        return $zones;
    }

    public function getUciDhcpConfig(): array
    {
        $config = [];
        try {
            $output = (string)$this->sshService->getRaw('uci show dhcp');
            $lines = explode("\n", $output);
            
            foreach ($lines as $line) {
                // e.g. dhcp.lan.start='100' or dhcp.@dhcp[0].interface='lan'
                if (preg_match('/^dhcp\.([a-zA-Z0-9_\-]+)\.(.+?)=[\'"]?(.*?)[\'"]?$/', trim($line), $matches)) {
                    $iface = strtolower($matches[1]);
                    // Ignore internal generic dhcp blocks unless they map to an interface by name
                    if (str_starts_with($iface, '@')) continue; 

                    $key = strtolower($matches[2]);
                    $val = $matches[3];
                    
                    if (!isset($config[$iface][$key])) {
                        $config[$iface][$key] = $val;
                    } else {
                        if (!is_array($config[$iface][$key])) {
                            $config[$iface][$key] = [$config[$iface][$key]];
                        }
                        $config[$iface][$key][] = $val;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error getting UCI dhcp config: ' . $e->getMessage());
        }
        return $config;
    }

    private function getIpAddrOutput(): string 
    {
        try {
            return (string)$this->sshService->getRaw('ip addr show');
        } catch (\Exception $e) {
            return '';
        }
    }

    private function getNetDevOutput(): string 
    {
        try {
            return (string)$this->sshService->getRaw('cat /proc/net/dev');
        } catch (\Exception $e) {
            return '';
        }
    }

    private function extractMacFromIpAddr(string $output, string $device): string
    {
        if (empty($device) || $device === 'N/A') return '--';
        
        $lines = explode("\n", $output);
        $foundDevice = false;

        foreach ($lines as $line) {
            if (preg_match("/^\d+:\s+" . preg_quote($device, '/') . ":/", $line)) {
                $foundDevice = true;
                continue;
            }
            
            if ($foundDevice && preg_match("/link\/[a-z]+\s+([0-9a-fA-F:]+)/", $line, $matches)) {
                if ($matches[1] !== '00:00:00:00:00:00') {
                    return strtoupper($matches[1]);
                }
            }
            
            if ($foundDevice && preg_match("/^\d+:/", $line)) {
                break; // next device started
            }
        }
        
        return '--';
    }

    private function extractStatsFromNetDev(string $output, string $device): array
    {
        if (empty($device) || $device === 'N/A') {
            return ['rx_bytes'=>0, 'rx_packets'=>0, 'tx_bytes'=>0, 'tx_packets'=>0];
        }

        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            if (str_contains($line, $device . ':')) {
                $parts = explode(':', $line);
                if (count($parts) === 2) {
                    $statsStr = trim($parts[1]);
                    $statsStr = preg_replace('/\s+/', ' ', $statsStr);
                    $statsArray = explode(' ', $statsStr);
                    
                    if (count($statsArray) >= 10) {
                        return [
                            'rx_bytes' => (int)$statsArray[0],
                            'rx_packets' => (int)$statsArray[1],
                            'tx_bytes' => (int)$statsArray[8],
                            'tx_packets' => (int)$statsArray[9],
                        ];
                    }
                }
            }
        }

        return ['rx_bytes'=>0, 'rx_packets'=>0, 'tx_bytes'=>0, 'tx_packets'=>0];
    }
}
