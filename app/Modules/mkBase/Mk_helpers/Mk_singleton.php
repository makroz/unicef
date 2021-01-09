<?php
namespace App\Modules\mkBase\Mk_helpers;

trait Mk_singleton
{
    private static $instance;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

// function encrypt($string, $key) {
//     $result = '';
//     for($i=0; $i<strlen($string); $i++) {
//        $char = substr($string, $i, 1);
//        $keychar = substr($key, ($i % strlen($key))-1, 1);
//        $char = chr(ord($char)+ord($keychar));
//        $result.=$char;
//     }
//     return base64_encode($result);
//  }

// function decrypt($string, $key) {
//     $result = '';
//     $string = base64_decode($string);
//     for($i=0; $i<strlen($string); $i++) {
//        $char = substr($string, $i, 1);
//        $keychar = substr($key, ($i % strlen($key))-1, 1);
//        $char = chr(ord($char)-ord($keychar));
//        $result.=$char;
//     }
//     return $result;
//  }
