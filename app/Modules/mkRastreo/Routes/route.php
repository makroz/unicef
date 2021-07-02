<?php

use \App\Modules\mkBase\Mk_helpers\Mk_app;

$namespace=Mk_app::loadControllers(__FILE__);
$fileController=$namespace.'\\RastreoController@';
Route::post('Rastreomap', $fileController.'rastreo');

