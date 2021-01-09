<?php

use \App\Modules\mkBase\Mk_helpers\Mk_app;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
$namespace="\App\Modules\mkUsuarios\Controllers\\";

Mk_app::setRuta('Roles',[],$namespace);
Mk_app::setRuta('Permisos',[],$namespace);
Mk_app::setRuta('Grupos',['extras'=>[
    ['post','/permisos/{grupos_id}','permisos']
]],$namespace);

Route::post('login', $namespace.'UsuariosController@login');
Route::post('logout', $namespace.'UsuariosController@logout');
Mk_app::setRuta('Usuarios',['extras'=>[
                ['post','/permisos/{grupos_id}','permisos'],
                ['post','/permisosGrupos/{usuarios_id}','permisosGrupos']
]],$namespace);

