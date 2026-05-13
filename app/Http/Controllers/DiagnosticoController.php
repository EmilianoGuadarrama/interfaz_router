<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DiagnosticoController extends Controller
{
    public function index()
    {
        return view('diagnostico.index');
    }

    private function validateHost($host)
    {
        return preg_match('/^[a-zA-Z0-9.-]+$/', $host);
    }

    public function ping(Request $request)
    {
        $host = $request->input('host');
        if (!$host || !$this->validateHost($host)) {
            return response("Host inválido.", 400);
        }

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $cmd = $isWindows ? "ping -n 4 " . escapeshellarg($host) : "ping -c 4 " . escapeshellarg($host);
        
        $output = shell_exec($cmd);
        return response($output ?? "No se pudo ejecutar PING.");
    }

    public function traceroute(Request $request)
    {
        $host = $request->input('host');
        if (!$host || !$this->validateHost($host)) {
            return response("Host inválido.", 400);
        }

        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $cmd = $isWindows ? "tracert " . escapeshellarg($host) : "traceroute " . escapeshellarg($host);
        
        $output = shell_exec($cmd);
        return response($output ?? "No se pudo ejecutar TRACEROUTE.");
    }

    public function nslookup(Request $request)
    {
        $host = $request->input('host');
        if (!$host || !$this->validateHost($host)) {
            return response("Host inválido.", 400);
        }

        $cmd = "nslookup " . escapeshellarg($host);
        
        $output = shell_exec($cmd);
        return response($output ?? "No se pudo ejecutar NSLOOKUP.");
    }
}
