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

$namespace="\App\Modules\mkPreguntas\Controllers";

Mk_app::setRuta('Categ',[],$namespace);
Mk_app::setRuta('Preguntas',[],$namespace);



