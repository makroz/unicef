<?php

use \App\Modules\mkBase\Mk_helpers\Mk_app;

$namespace=Mk_app::loadControllers(__FILE__);

$fileController=$namespace.DIRECTORY_SEPARATOR.'GruposController@';
Route::get('Grupos/permisos/{grupos_id}', $fileController.'permisos');

$fileController=$namespace.DIRECTORY_SEPARATOR.'UsuariosController@';
Route::post('login', $fileController.'login');
Route::post('logout', $fileController.'logout');
Route::post('Usuarios/permisos/{grupos_id}', $fileController.'permisos');
Route::post('Usuarios/permisosGrupos/{usuarios_id}', $fileController.'permisosGrupos');


