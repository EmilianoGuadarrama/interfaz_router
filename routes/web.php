<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NetworkController;
use App\Http\Controllers\RoutesController;

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
});
