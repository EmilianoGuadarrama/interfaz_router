<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\Ruta_estatica\RoutesController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\WifiController;
use App\Http\Controllers\Red\DhcpDnsController;
use App\Http\Controllers\Red\ConmutadorController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('red')->name('red.')->group(function () {

    // =====================================================================
    // INTERFACES (se mantiene en NetworkController)
    // =====================================================================
    Route::get('/interfaces', [NetworkController::class, 'interfaces'])->name('interfaces');
    Route::post('/interfaces/store', [NetworkController::class, 'storeInterface'])->name('interfaces.store');
    Route::post('/interfaces/{name}/restart', [NetworkController::class, 'restartInterface'])->name('interfaces.restart');
    Route::post('/interfaces/{name}/stop', [NetworkController::class, 'stopInterface'])->name('interfaces.stop');
    Route::delete('/interfaces/{name}/eliminar', [NetworkController::class, 'destroyInterface'])->name('interfaces.destroy');
    Route::post('/interfaces/{name}/update', [NetworkController::class, 'updateInterface'])->name('interfaces.update');
    Route::post('/interfaces/lan/update', [NetworkController::class, 'updateLanInterface'])->name('interfaces.lan.update');
    Route::post('/interfaces/wan/update', [NetworkController::class, 'updateWanInterface'])->name('interfaces.wan.update');

    // Vista principal de Wi-Fi
    Route::get('/wifi', [WifiController::class, 'index'])->name('wifi');
    Route::get('/wifi/scan', [WifiController::class, 'scan'])->name('wifi.scan');
    Route::post('/wifi/connect', [WifiController::class, 'connect'])->name('wifi.connect');
    Route::post('/wifi/ssid', [WifiController::class, 'updateSSID'])->name('wifi.ssid.update');
    Route::post('/wifi/password', [WifiController::class, 'updatePassword'])->name('wifi.password.update');
    Route::post('/wifi/restart', [WifiController::class, 'restart'])->name('wifi.restart');
    Route::post('/wifi/delete', [WifiController::class, 'deleteInterface'])->name('wifi.delete');
    Route::post('/wifi/edit', [WifiController::class, 'editNetwork'])->name('wifi.edit');
    Route::post('/wifi/add', [WifiController::class, 'addNetwork'])->name('wifi.add');


    // Vista principal de Conmutador
    Route::get('/conmutador', [NetworkController::class, 'showSwitch'])->name('switch');
    Route::post('/conmutador', [NetworkController::class, 'updateSwitch'])->name('switch.update');
    // =====================================================================
    // CONMUTADOR — ConmutadorController
    // =====================================================================
    Route::get('/conmutador', [ConmutadorController::class, 'index'])->name('conmutador');

    Route::prefix('conmutador')->name('conmutador.')->group(function () {
        Route::get('/general', [ConmutadorController::class, 'general'])->name('general');
        Route::post('/general', [ConmutadorController::class, 'updateGeneral'])->name('general.update');
        Route::get('/vlans', [ConmutadorController::class, 'vlans'])->name('vlans');
        Route::post('/vlans', [ConmutadorController::class, 'storeVlan'])->name('vlans.store');
        Route::put('/vlans/{index}', [ConmutadorController::class, 'updateVlan'])->name('vlans.update');
        Route::delete('/vlans/{index}', [ConmutadorController::class, 'destroyVlan'])->name('vlans.destroy');
        Route::post('/puertos', [ConmutadorController::class, 'updatePorts'])->name('ports.update');
    });

    // =====================================================================
    // DHCP Y DNS — DhcpDnsController
    // =====================================================================
    Route::get('/dhcp-dns', [DhcpDnsController::class, 'index'])->name('dhcpdns');

    Route::prefix('dhcp-dns')->name('dhcpdns.')->group(function () {
        Route::get('/general', [DhcpDnsController::class, 'general'])->name('general');
        Route::post('/general', [DhcpDnsController::class, 'updateGeneral'])->name('general.update');

        Route::get('/resolv-hosts', [DhcpDnsController::class, 'resolvHosts'])->name('resolv');
        Route::post('/resolv-hosts', [DhcpDnsController::class, 'updateResolvHosts'])->name('resolv.update');

        Route::get('/tftp', [DhcpDnsController::class, 'tftp'])->name('tftp');
        Route::post('/tftp', [DhcpDnsController::class, 'updateTftp'])->name('tftp.update');

        Route::get('/advanced', [DhcpDnsController::class, 'advanced'])->name('advanced');
        Route::post('/advanced', [DhcpDnsController::class, 'updateAdvanced'])->name('advanced.update');

        Route::get('/static', [DhcpDnsController::class, 'staticLeases'])->name('static');
        Route::post('/static', [DhcpDnsController::class, 'storeStaticLease'])->name('static.store');
        Route::delete('/static/{index}', [DhcpDnsController::class, 'destroyStaticLease'])->name('static.destroy');
    });

    // =====================================================================
    // RUTAS ESTÁTICAS (se mantiene en RoutesController)
    // =====================================================================
    Route::get('/rutas/estaticas/ipv4', [RoutesController::class, 'staticIpv4'])->name('routes.static.ipv4');
    Route::post('/rutas/estaticas/ipv4/guardar', [RoutesController::class, 'storeStaticIpv4'])->name('routes.static.ipv4.store');
    Route::delete('/rutas/estaticas/ipv4/eliminar', [RoutesController::class, 'destroyStaticIpv4'])->name('routes.static.ipv4.destroy');

    Route::get('/rutas/estaticas/ipv6', [RoutesController::class, 'staticIpv6'])->name('routes.static.ipv6');
    Route::post('/rutas/estaticas/ipv6/guardar', [RoutesController::class, 'storeStaticIpv6'])->name('routes.static.ipv6.store');
    Route::delete('/rutas/estaticas/ipv6/eliminar', [RoutesController::class, 'destroyStaticIpv6'])->name('routes.static.ipv6.destroy');

    Route::get('/estado-conexion', [RoutesController::class, 'checkConnection'])->name('estado.conexion');

    // =====================================================================
    // NOMBRES DE HOST (se mantiene en NetworkController)
    // =====================================================================
    Route::get('/nombres-host', [NetworkController::class, 'hostEntries'])->name('hostentries');
    Route::post('/nombres-host/agregar', [NetworkController::class, 'storeHostEntry'])->name('hostentries.store');
    Route::delete('/nombres-host/eliminar', [NetworkController::class, 'destroyHostEntry'])->name('hostentries.destroy');
});

// LEDs
Route::prefix('sistema')->name('leds.')->group(function () {
    Route::get('/leds', [SystemController::class, 'leds'])->name('index');
    Route::get('/leds/crear', [SystemController::class, 'createLed'])->name('create');
    Route::post('/leds', [SystemController::class, 'storeLed'])->name('store');
    Route::get('/leds/{key}/editar', [SystemController::class, 'editLed'])->name('edit');
    Route::post('/leds/{key}', [SystemController::class, 'updateLed'])->name('update');
    Route::post('/leds/{key}/eliminar', [SystemController::class, 'destroyLed'])->name('destroy');
});

// GRABADO DE IMAGEN
Route::get('/grabado', [SystemController::class, 'grabado'])->name('grabado.index');
Route::post('/grabado/backup', [SystemController::class, 'descargarBackup'])->name('grabado.backup');
Route::post('/grabado/restaurar', [SystemController::class, 'restaurarBackup'])->name('grabado.restaurar');
Route::post('/grabado/fabrica', [SystemController::class, 'restablecerFabrica'])->name('grabado.fabrica');
Route::post('/grabado/mtdblock', [SystemController::class, 'descargarMtdblock'])->name('grabado.mtdblock');
Route::post('/grabado/imagen', [SystemController::class, 'grabarImagen'])->name('grabado.imagen');
Route::post('/grabado/guardar-lista', [SystemController::class, 'guardarLista'])->name('grabado.guardarLista');

// Reinicio
Route::get('/reiniciar', [SystemController::class, 'reiniciar'])->name('reiniciar.index');
Route::post('/reiniciar/run', [SystemController::class, 'reiniciarRun'])->name('reiniciar.run');

// arranque y tareas programadas
Route::get('/arranque', [SystemController::class, 'startup'])->name('startup');
Route::post('/arranque', [SystemController::class, 'updateStartup'])->name('startup.update');
Route::post('/arranque/scripts/{script}/{action}', [SystemController::class, 'startupScriptAction'])->name('startup.scripts.action');

Route::get('/tareas-programadas', [SystemController::class, 'scheduledTasks'])->name('tasks');
Route::post('/tareas-programadas', [SystemController::class, 'updateScheduledTasks'])->name('tasks.update');

//////////SISTEMA

Route::prefix('system')->group(function () {

    // 1. Ruta GET para MOSTRAR la vista del formulario
    Route::get('/general', [SystemController::class, 'general'])->name('system.general');

    // 2. Ruta POST para PROCESAR los datos cuando le das a "Guardar" o "Guardar y Aplicar"
    Route::post('/general/update', [SystemController::class, 'updateGeneral'])->name('system.general.update');

    // ... (Aquí seguramente ya tienes o pondrás tus otras rutas de leds, grabado, tareas, etc.)
});
