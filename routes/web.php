<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
use App\Http\Controllers\NetworkController;

Route::prefix('red')->name('network.')->group(function () {
    Route::get('/conmutador', [NetworkController::class, 'showSwitch'])->name('switch');
    Route::post('/conmutador', [NetworkController::class, 'updateSwitch'])->name('switch.update');

    Route::get('/dhcp-dns', [NetworkController::class, 'showDhcpDns'])->name('dhcpdns');
    Route::post('/dhcp-dns', [NetworkController::class, 'updateDhcpDns'])->name('dhcpdns.update');
});


Route::prefix('red')->name('network.')->group(function () {

    Route::prefix('conmutador')->name('switch.')->group(function () {
        Route::get('/general', [NetworkController::class, 'switchGeneral'])->name('general');
        Route::get('/vlans', [NetworkController::class, 'switchVlans'])->name('vlans');
        Route::post('/vlans/guardar', [NetworkController::class, 'updateSwitchVlans'])->name('vlans.update');
    });

    Route::prefix('dhcp-dns')->name('dhcpdns.')->group(function () {
        Route::get('/general', [NetworkController::class, 'dhcpDnsGeneral'])->name('general');
        Route::get('/resolv-hosts', [NetworkController::class, 'dhcpDnsResolvHosts'])->name('resolv');
        Route::get('/tftp', [NetworkController::class, 'dhcpDnsTftp'])->name('tftp');
        Route::get('/advanced', [NetworkController::class, 'dhcpDnsAdvanced'])->name('advanced');
        Route::get('/static', [NetworkController::class, 'dhcpDnsStatic'])->name('static');

        Route::post('/general/guardar', [NetworkController::class, 'updateDhcpDnsGeneral'])->name('general.update');
        Route::post('/resolv-hosts/guardar', [NetworkController::class, 'updateDhcpDnsResolvHosts'])->name('resolv.update');
        Route::post('/tftp/guardar', [NetworkController::class, 'updateDhcpDnsTftp'])->name('tftp.update');
        Route::post('/advanced/guardar', [NetworkController::class, 'updateDhcpDnsAdvanced'])->name('advanced.update');
        Route::post('/static/guardar', [NetworkController::class, 'updateDhcpDnsStatic'])->name('static.update');
    });

});
