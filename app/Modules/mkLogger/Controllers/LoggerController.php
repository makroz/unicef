<?php

namespace App\Modules\mkLogger\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;

class LoggerController extends Controller
{
    use Mk_ia_db;
    //public $_autorizar='';
    public $_notBy=true;
    protected $__modelo='';
    public function __construct(Request $request)
    {
        parent::__construct($request);
        return true;
    }
    public function beforeSave(Request $request, &$modelo, $id = 0)
    {
        $user_id=0;
        if (Mk_auth::get()->isLogin()) {
          try {
            $user_id=Mk_auth::get()->getUser()->id;
          } catch (\Throwable $th) {
            //throw $th;
          }
        }
        $ip ='';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $modelo['usuario_id']=$user_id;
        $modelo['ip']=$ip;
        $modelo['token']=Mk_auth::get()->getToken();
        

        return true;
    }
}