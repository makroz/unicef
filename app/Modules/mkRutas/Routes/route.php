<?php

use \App\Modules\mkBase\Mk_helpers\Mk_app;
use Illuminate\Support\Facades\Route;

$namespace=Mk_app::loadControllers(__FILE__);
$fileController=$namespace.DIRECTORY_SEPARATOR.'RutasController@';
Route::get('monitores', $fileController.'monitores');
Route::get('Rutas/beneficiarios/{id}', $fileController.'beneficiarios');
$fileController=$namespace.DIRECTORY_SEPARATOR.'RuteosController@';
Route::get('RuteosMonitor', $fileController.'rutas');
Route::post('RuteosMonitor/setClose', $fileController.'setClose');


