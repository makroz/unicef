<?php

namespace App\Modules\mkRutas\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\mkBase\Mk_helpers\Mk_db;
use App\Modules\mkBase\Mk_helpers\Mk_debug;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;

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

    public function beforeSave(Request $request, &$modelo, $id = 0)
    {
        if (!empty($request->lat)&&!empty($request->lng)) {
            if (!$modelo){
                $modelo=[];
            }
            $modelo['cabierto'] = DB::raw(
                "ST_GeomFromText('POINT({$request->lat} {$request->lng})')"
            );
        }
        return true;
    }

    public function rutas(Request $request)
    {
        $modelo = 'App\Modules\MkRutas\Rutas';
        $cols   = 'id,id as rutas_id,name,descrip,usuarios_id,status';
    try{
        $user   = Mk_auth::get()->getUser();
    } catch (\Throwable $th) {
        Mk_debug::msgApi('Error de Logueo rutas:'.$th->getMessage().' >>'.$th->getFile().':'.$th->getLine().':'.$th->getCode());
        return Mk_db::sendData($th->getCode());
        
    }
        
        if (empty($user)) {
            $userId=null;
        }else{
            $userId=$user->id;
        }
        $fi = date('oWN');
        // Mk_debug::msgApi(['Fecha',$fi]);
        $filtros = [
            ['usuarios_id', '=', $userId],
            ['status', '<>', 0],
        ];
         
        $rutas = $this->getDatosDbCache($request, $modelo, $cols, ['filtros'=>$filtros,'relations'=>['beneficiarios:rutas_id,id'],'send'=>false]);
        //Mk_debug::msgApi(['rutas', $rutas]);
        $modelo = 'App\Modules\MkRutas\Ruteos';
        $cantRutas=count($rutas['data']);

        $fecha_actual = date("d-m-Y");
        $ds           = date('N');
        $dt           = $ds - 1;
        $f1           = date("Y-m-d", strtotime($fecha_actual . "- {$dt} day"));
        // $dt           = 7 - $ds;
        // $f2           = date("Y-m-d", strtotime($fecha_actual . "+ {$dt} day"));
        //Mk_debug::msgApi(['rutas', $rutas]);
        $cols   = 'id,rutas_id,obs,usuarios_id,status,created_at,fec_cerrado';
        $rDispon = [];
        $rOpen = [];
        $rClosed = [];
        //dd($rutas);
        foreach ($rutas['data'] as $key => $ruta) {
            //dd($ruta);
            $filtros = [
                ['created_at', '>=', $f1],
                //['created_at', '<=', $f2],
                //['fec_cerrado', '=', null],
                ['rutas_id', '=', $ruta['id']],
                ['status', '<>', 0],
            ];
            $ruteos = $this->getDatosDbCache(
                $request, $modelo, $cols,['filtros'=>$filtros,'relations'=>['evaluaciones:ruteos_id,id,obs,beneficiarios_id,fec_verif,estado','evaluaciones.respuestas','evaluaciones.servicios'],'send'=>false]);
               // dd($rutas);
            $cantRuteos=count($ruteos['data']);
            if ($cantRuteos>0){
                foreach ($ruteos['data'] as $key => $ruteo) {
                    if (empty($ruteo['fec_cerrado'])){
                        $rOpen[] = $ruteo;
                    }else{
                        $rClosed[] = $ruteo;
                    }
                }
            }else{
                $rDispon[] = $ruta;
            }
        }
        $result=[
            'dispon' => [
                'ok'=> count($rDispon),
                'data' => $rDispon
            ],
            'open' => [
                'ok'=> count($rOpen),
                'data' => $rOpen
            ],
            'closed' => [
                'ok'=> count($rClosed),
                'data' => $rClosed
            ]
        ];
        return Mk_db::sendData(count($result), $result, '');
    }
}
