<?php

namespace App\Modules\mkBase\Mk_helpers;

use Request;
use Session;
use Cache;
use App\Modules\mkBase\Mk_helpers\Mk_debug;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;

class Mk_forms
{
    public static $ses;
    public static $timeSession=86400;

    public static function getSession($name, $default='')
    {
        $token=Mk_auth::get()->getTokenCoockie();
        if ($token=='') {
            return Session::get($name, $default);
        } else {
            return Cache::remember("{$token}.{$name}", self::$timeSession, function () use ($default) {
                return $default;
            });
        }
    }


    public static function setSession($name, $value='',$time=86400)
    {
        $token=Mk_auth::get()->getTokenCoockie();
        if ($token=='') {
            Session::put($name, $value,$time);
        } else {
            Cache::put("{$token}.{$name}",$value,self::$timeSession);
        }
        return true;
    }
    public static function getParam($name, $default='')
    {
        $clase=Request::route()->getAction();
        $clase=basename($clase['controller']);
        $clase=explode('Controller@', $clase);
        $clase=$clase[0];
        $ruta="params.{$clase}.{$name}";
        if (Request::has($name)){
            $param=Request::input($name);
            self::setSession($ruta, $param);
        }else{
            $param=self::getSession($ruta, $default);
        }
        return $param;
    }

    public function __destruct()
    {
        Mk_debug::msgApi('Desconstructor form!!!:'.$this->counter);
    }
}
