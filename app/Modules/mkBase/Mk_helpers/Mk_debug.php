<?php

namespace App\Modules\mkBase\Mk_helpers;

class Mk_debug
{
    private static $_mk_debug=-1;
    private static $_mk_debugDb=true;
    private static $msgApi=array();
    private static $msgWarning=array();
    private static $counter=0;


    public static function __init()
    {
        self::$_mk_debug = env('IA_DEBUG', true);
        self::$_mk_debugDb=env('IA_DEBUG_DB', true);
    }

    public static function msgApi($msg='', $force=false)
    {
        $r=sizeof(self::$msgApi);
        if ($msg=='') {
            if ($r==0) {
                return false;
            }
            return $r;
        }
        $call=debug_backtrace(2,2);
        //$call='   >> ['.basename($call[0]['file']).':'.$call[0]['line'].']';
        self::$counter++;
        $call=self::$counter.'-'.date('H:i:s').'('.basename($call[0]['file']).')'.$call[0]['line'];
        self::$msgApi[$call]=$msg;

        return true;
    }
    public static function error($msg='',$mod='all')
    {
        return self::warning($msg,$mod,'ERROR','error');
    }
    public static function warning($msg='', $mod='all',$nivel='INFO',$tipo='warning')
    {
        if ($mod==null){
            $mod='all';
        }
        $r=sizeof(self::$msgWarning);
        if ($msg=='') {
            if ($r==0) {
                return false;
            }
            return $r;
        }
        $call=debug_backtrace(2,2);
        self::$counter++;
        $call=self::$counter.'-'.date('H:i:s').'('.basename($call[0]['file']).')'.$call[0]['line'];
        self::$msgWarning[]=[$msg,$mod,$nivel,$tipo];
        return true;
    }

    public static function getMsgApi()
    {
        return self::$msgApi;
    }
    public static function getWarning()
    {
        return self::$msgWarning;
    }

    public static function isDebug()
    {
        if (self::$_mk_debug==-1) {
            self::__init();
        }
        return self::$_mk_debug;
    }

    public static function isDebugDb()
    {
        if (self::$_mk_debug==-1) {
            self::__init();
        }
        return self::$_mk_debugDb;
    }

    public static function setDebug($f=true)
    {
        self::$_mk_debug=$f;
        return true;
    }

    public static function setDebugDb($f=true)
    {
        self::$_mk_debugDb=$f;
        return true;
    }
}
