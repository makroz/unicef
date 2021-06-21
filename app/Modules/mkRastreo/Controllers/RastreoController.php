<?php

namespace App\Modules\mkRastreo\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;

class RastreoController extends Controller
{
    use Mk_ia_db;
    //public $_autorizar='';
    protected $__modelo='';
    public function __construct(Request $request)
    {
        parent::__construct($request);
        return true;
    }

    public function beforeSave(Request $request, &$modelo, $id = 0)
    {
        if (!empty($request->lat) && !empty($request->lng)) {
          if (Mk_auth::get()->isLogin()) {
            $user_id = Mk_auth::get()->getUser()->id;
          }
            if (!$modelo) {
                $modelo = [];
            }
            $modelo['usuarios_id'] = $user_id;
            $modelo['coord'] = DB::raw(
                "ST_GeomFromText('POINT({$request->lat} {$request->lng})')"
            );
        }

        return true;
    }

}