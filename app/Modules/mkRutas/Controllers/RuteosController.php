<?php

namespace App\Modules\mkRutas\Controllers;

use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;
use App\Modules\mkBase\Mk_Helpers\Mk_db;
use App\Modules\mkBase\Mk_helpers\Mk_debug;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RuteosController extends Controller
{
    use Mk_ia_db;
    //public $_autorizar='';

    protected $__modelo = '';
    public function __construct(Request $request)
    {
        parent::__construct($request);
        return true;
    }

    public function beforeSave(Request $request, $modelo, $id = 0)
    {
        $modelo->cabierto = DB::raw(
            "ST_GeomFromText('POINT({$request->lat} {$request->lng})')"
        );
    }
    public function rutas(Request $request)
    {
        $modelo = 'App\Modules\MkRutas\Rutas';
        $cols   = 'id as rutas_id,name,descrip,usuarios_id,status';
        $user   = Mk_auth::get()->getUser();
        if (empty($user)) {
            $user->id = 0;
        }
        $fi = date('oWN');
        // Mk_debug::msgApi(['Fecha',$fi]);
        $filtros = [
            ['usuarios_id', '=', $user->id],
            ['status', '<>', 0],
        ];
        $rutas = $this->getDatosDbCache($request, $modelo, $cols, $filtros, false);
        Mk_debug::msgApi(['rutas', $rutas]);
        $modelo = 'App\Modules\MkRutas\Ruteos';

        $fecha_actual = date("d-m-Y");
        $ds           = date('N');
        $dt           = $ds - 1;
        $f1           = date("Y-m-d", strtotime($fecha_actual . "- {$dt} day"));
        $dt           = 7 - $ds;
        $f2           = date("Y-m-d", strtotime($fecha_actual . "+ {$dt} day"));
        Mk_debug::msgApi(['rutas', $rutas]);
        $cols   = 'rutas_id,obs,usuarios_id,status,created_at';
        $result = [];
        foreach ($rutas as $key => $ruta) {
            $filtros = [
                ['created_at', '>=', $f1],
                ['created_at', '<=', $f2],
                ['fec_cerrado', '=', null],
                ['rutas_id', '=', $ruta['id']],
                ['status', '<>', 0],
            ];
            $ruteos = $this->getDatosDbCache(
                $request, $modelo, $cols, $filtros, false
            );
            $result = array_merge($result, $ruteos);
        }
        return Mk_db::sendData(count($result), $result, '');
    }
}
