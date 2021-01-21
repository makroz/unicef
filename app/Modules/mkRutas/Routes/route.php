<?php

use \App\Modules\mkBase\Mk_helpers\Mk_app;

$namespace=Mk_app::loadControllers(__FILE__);
$fileController=$namespace.DIRECTORY_SEPARATOR.'RutasController@';
Route::get('monitores', $fileController.'monitores');
Route::get('Rutas/beneficiarios/{id}', $fileController.'beneficiarios');


