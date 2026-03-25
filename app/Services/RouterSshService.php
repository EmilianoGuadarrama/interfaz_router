<?php

namespace App\Services;

use phpseclib3\Net\SSH2;
use Exception;

class RouterSshService
{
    protected string $host;
    protected int $port;
    protected string $user;
    protected string $password;

    public function __construct()
    {
        $this->host = env('ROUTER_HOST', '192.168.10.1');
        $this->port = (int) env('ROUTER_PORT', 22);
        $this->user = env('ROUTER_USER', 'root');
        $this->password = env('ROUTER_PASSWORD', '');
    }

    public function execute(array $commands): array
    {
        $ssh = new SSH2($this->host, $this->port);

        if (!$ssh->login($this->user, $this->password)) {
            throw new Exception('Error de autenticación SSH con el router.');
        }

        $output = '';
        $hasError = false;
        $errorWords = ['not found', 'parse error', 'invalid', 'error', 'failed'];

        foreach ($commands as $index => $command) {
            $result = $ssh->exec($command);
            $output .= ">>> Comando " . ($index + 1) . ":\n{$command}\n";
            $output .= ($result !== '' ? $result : '[sin salida]') . "\n\n";

            foreach ($errorWords as $word) {
                if (stripos($result, $word) !== false) {
                    $hasError = true;
                }
            }
        }

        return [
            'success' => !$hasError,
            'output' => $output,
        ];
    }
}
