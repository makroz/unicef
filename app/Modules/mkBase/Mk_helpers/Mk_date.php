<?php
namespace App\Modules\mkBase\Mk_helpers;

use Carbon\Carbon;

class Mk_date
{
    public static function dateToLocal($date,$time=false)
    {
        $formato="Y-m-d";
        if ($time){
            $formato="Y-m-d H:i:s";
        }
        return date($formato,strtotime($date));
    }

    public static function dateToUTC($date,$time=false)
    {
        $formato="Y-m-d";
        if ($time){
            $formato="Y-m-d H:i:s";
        }
        return Carbon::parse($date);
    }

}