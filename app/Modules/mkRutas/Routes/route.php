<?php

use \App\Modules\mkBase\Mk_helpers\Mk_app;
use Illuminate\Support\Facades\Route;

$namespace=Mk_app::loadControllers(__FILE__);
$fileController=$namespace.'\\RutasController@';
//Route::get('monitores', $fileController.'monitores');
Route::get('/ping', function () {
  return 1;
});
Route::get('Rutas/beneficiarios/{id}', $fileController.'beneficiarios');
$fileController=$namespace.'\\RuteosController@';
Route::get('RuteosMonitor', $fileController.'rutas');
Route::post('Reportes', $fileController.'reportes');
Route::post('Dashboard', $fileController.'dashboard');
Route::post('RuteosMonitor/setClose', $fileController.'setClose');


