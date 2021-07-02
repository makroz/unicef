<?php

namespace App\Modules\mkRastreo\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\mkBase\Mk_helpers\Mk_db;
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

    public function rastreo(Request $request)
    {
        $this->proteger('show');
        $result   = [];
        $desde    = $request->desde;
        $hasta    = $request->hasta;
        if (!empty($request->reportes['rastreo'])) {
            $modelo = 'App\Modules\mkRastreo\Rastreo';
            $cols   = 'id,fecha,usuarios_id';
            if (!empty($request->usuario) && $request->usuario > 0) {
              $rastreo   = DB::select('select id,fecha,usuarios_id, ST_X(coord) as lat, ST_Y(coord) as lng  from rastreo where DATE(fecha) >= ? and DATE(fecha)<=? and usuarios_id=? ', [$desde,$hasta, $request->usuario]);
                //$rastreo    = $this->getDatosDbCache($request, $modelo, $cols, ['sortBy'=>'fecha','filtros' => [['fecha', '>=', $request->desde],['fecha', '<=', $request->hasta],['usuarios_id', '=', $request->usuario]], '_customFields'=>1,'send' => false]);
            } else {
              $rastreo   = DB::select('select id,fecha,usuarios_id, ST_X(coord) as lat, ST_Y(coord) as lng  from rastreo where DATE(fecha) >= ? and DATE(fecha)<=?', [$desde,$hasta]);
                //$rastreo    = $this->getDatosDbCache($request, $modelo, $cols, ['sortBy'=>'fecha','filtros' => [['fecha', '>=', $request->desde],['fecha', '<=', $request->hasta]],'_customFields'=>1,'send' => false]);
            }
            $cantRastreo = count($rastreo);
            $result['rastreo'] = $rastreo;
        }

        return Mk_db::sendData(count($result), $result, '');
    }

}