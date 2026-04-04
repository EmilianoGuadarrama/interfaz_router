<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\RoutesController;
use App\Http\Controllers\SystemController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('red')->name('network.')->group(function () {

    // Vista principal de Conmutador
    Route::get('/conmutador', [NetworkController::class, 'showSwitch'])->name('switch');
    Route::post('/conmutador', [NetworkController::class, 'updateSwitch'])->name('switch.update');

    // Subapartados de Conmutador
    Route::prefix('conmutador')->name('switch.')->group(function () {
        Route::get('/general', [NetworkController::class, 'switchGeneral'])->name('general');
        Route::get('/vlans', [NetworkController::class, 'switchVlans'])->name('vlans');
        Route::post('/vlans/guardar', [NetworkController::class, 'updateSwitchVlans'])->name('vlans.update');
    });

    // Vista principal de DHCP y DNS
    Route::get('/dhcp-dns', [NetworkController::class, 'showDhcpDns'])->name('dhcpdns');
    Route::post('/dhcp-dns', [NetworkController::class, 'updateDhcpDns'])->name('dhcpdns.update');

    // Subapartados de DHCP y DNS
    Route::prefix('dhcp-dns')->name('dhcpdns.')->group(function () {
        Route::get('/general', [NetworkController::class, 'dhcpDnsGeneral'])->name('general');
        Route::post('/general/guardar', [NetworkController::class, 'updateDhcpDnsGeneral'])->name('general.update');

        Route::get('/resolv-hosts', [NetworkController::class, 'dhcpDnsResolvHosts'])->name('resolv');
        Route::post('/resolv-hosts/guardar', [NetworkController::class, 'updateDhcpDnsResolvHosts'])->name('resolv.update');

        Route::get('/tftp', [NetworkController::class, 'dhcpDnsTftp'])->name('tftp');
        Route::post('/tftp/guardar', [NetworkController::class, 'updateDhcpDnsTftp'])->name('tftp.update');

        Route::get('/advanced', [NetworkController::class, 'dhcpDnsAdvanced'])->name('advanced');
        Route::post('/advanced/guardar', [NetworkController::class, 'updateDhcpDnsAdvanced'])->name('advanced.update');

        Route::get('/static', [NetworkController::class, 'dhcpDnsStatic'])->name('static');
        Route::post('/static/guardar', [NetworkController::class, 'updateDhcpDnsStatic'])->name('static.update');
    });

    Route::get('/rutas/estaticas/ipv4', [RoutesController::class, 'staticIpv4'])->name('routes.static.ipv4');
    Route::post('/rutas/estaticas/ipv4/guardar', [RoutesController::class, 'storeStaticIpv4'])->name('routes.static.ipv4.store');
    Route::delete('/rutas/estaticas/ipv4/eliminar', [RoutesController::class, 'destroyStaticIpv4'])->name('routes.static.ipv4.destroy');

    Route::get('/rutas/estaticas/ipv6', [RoutesController::class, 'staticIpv6'])->name('routes.static.ipv6');
    Route::post('/rutas/estaticas/ipv6/guardar', [RoutesController::class, 'storeStaticIpv6'])->name('routes.static.ipv6.store');
    Route::get('/estado-conexion', [App\Http\Controllers\RoutesController::class, 'checkConnection'])->name('estado.conexion');
    Route::delete('/rutas/estaticas/ipv6/eliminar', [RoutesController::class, 'destroyStaticIpv6'])->name('routes.static.ipv6.destroy');

    // Nombres de host
    Route::get('/nombres-host', [NetworkController::class, 'hostEntries'])->name('hostentries');
    Route::post('/nombres-host/agregar', [NetworkController::class, 'storeHostEntry'])->name('hostentries.store');
    Route::delete('/nombres-host/eliminar', [NetworkController::class, 'destroyHostEntry'])->name('hostentries.destroy');

});


// LEDs
Route::prefix('sistema')->name('leds.')->group(function () {
    Route::get('/leds',                 [SystemController::class, 'leds'])->name('index');
    Route::get('/leds/crear',           [SystemController::class, 'createLed'])->name('create');
    Route::post('/leds',                [SystemController::class, 'storeLed'])->name('store');
    Route::get('/leds/{key}/editar',    [SystemController::class, 'editLed'])->name('edit');
    Route::post('/leds/{key}',          [SystemController::class, 'updateLed'])->name('update');
    Route::post('/leds/{key}/eliminar', [SystemController::class, 'destroyLed'])->name('destroy');
});

// GRABADO DE IMAGEN
Route::get('/grabado',             [SystemController::class, 'grabado'])->name('grabado.index');
Route::post('/grabado/backup',     [SystemController::class, 'descargarBackup'])->name('grabado.backup');
Route::post('/grabado/restaurar',  [SystemController::class, 'restaurarBackup'])->name('grabado.restaurar');
Route::post('/grabado/fabrica',    [SystemController::class, 'restablecerFabrica'])->name('grabado.fabrica');
Route::post('/grabado/mtdblock',   [SystemController::class, 'descargarMtdblock'])->name('grabado.mtdblock');
Route::post('/grabado/imagen',     [SystemController::class, 'grabarImagen'])->name('grabado.imagen');

// Reinicio
Route::get('/reiniciar',     [SystemController::class, 'reiniciar'])   ->name('reiniciar.index');
Route::post('/reiniciar/run',[SystemController::class, 'reiniciarRun'])->name('reiniciar.run');



