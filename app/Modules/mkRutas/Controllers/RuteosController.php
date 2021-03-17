<?php

namespace App\Modules\mkRutas\Controllers;

use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;
use App\Modules\mkBase\Mk_helpers\Mk_db;
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

    public function beforeSave(Request $request, &$modelo, $id = 0)
    {
        if (!empty($request->lat) && !empty($request->lng)) {
            $user = Mk_auth::get()->getUser()->id;
            if (!$modelo) {
                $modelo = [];
            }
            if ($id == 0) {
                $modelo['open_id'] = $user;
                $modelo['gps_open'] = DB::raw(
                    "ST_GeomFromText('POINT({$request->lat} {$request->lng})')"
                );
            }
        }
        return true;
    }

    public function setClose(Request $request)
    {
        $this->proteger('edit');

        if (!empty($request->lat) && !empty($request->lng)) {
            $user = Mk_auth::get()->getUser()->id;
            $modelo['close_id'] = $user;
            $modelo['estado'] = 2;
            $modelo['fec_cerrado'] = date('Y-m-d H:i:s');
            $modelo['gps_close'] = DB::raw(
                "ST_GeomFromText('POINT({$request->lat} {$request->lng})')"
            );
            $id = explode(',', $request->id);
            DB::beginTransaction();
            $datos = new $this->__modelo();
            $_key = $datos->getKeyName();
            $r = $datos->wherein($_key, $id)
                ->update($modelo);
            $msg = '';
            if ($r == 0) {
                $r = _errorNoExiste;
                $msg = 'Registro ya NO EXISTE';
                DB::rollback();
            } else {
                DB::commit();
                $this->clearCache();
            }
            if (!$request->ajax()) {
                return Mk_db::sendData($r, $this->index($request, false), $msg);
            }
        }
    }

    public function rutas(Request $request)
    {
        $this->proteger('edit');
        $modelo = 'App\Modules\MkRutas\Rutas';
        $cols = 'id,id as rutas_id,name,descrip,usuarios_id,status';
        try {
            $userId = Mk_auth::get()->getUser()->id;
        } catch (\Throwable $th) {
            $userId = null;
            Mk_debug::msgApi('Error de Logueo rutas:' . $th->getMessage() . ' >>' . $th->getFile() . ':' . $th->getLine() . ':' . $th->getCode());
            return Mk_db::sendData($th->getCode());
            //    $userId=$user->id;
        }

        // if (empty($user)) {
        //     $userId=null;
        // }else{
        //     $userId=$user->id;
        // }
        $fi = date('oWN');
        $filtros = [
            ['usuarios_id', '=', $userId],
            ['status', '<>', 0],
        ];

        $rutas = $this->getDatosDbCache($request, $modelo, $cols, ['filtros' => $filtros, 'relations' => ['beneficiarios:rutas_id,id'], 'send' => false]);
        $modelo = 'App\Modules\MkRutas\Ruteos';
        $cantRutas = count($rutas['data']);

        $fecha_actual = date("d-m-Y");
        $ds = date('N');
        $dt = $ds - 1;
        $f1 = date("Y-m-d", strtotime($fecha_actual . "- {$dt} day"));
        $dt = $dt + 7;
        $f2 = date("Y-m-d", strtotime($fecha_actual . "- {$dt} day"));
        $cols = 'id,rutas_id,obs,usuarios_id,status,created_at,fec_cerrado';
        $rDispon = [];
        $rOpen = [];
        $rClosed = [];
        $rRetrased = [];
        $options = [
            'filtros' => $filtros,
            'relations' => [
                'evaluaciones:ruteos_id,id,obs,beneficiarios_id,fec_verif,estado',
                'evaluaciones.respuestas',
                'evaluaciones.servicios',
            ],
            'send' => false,
        ];

        foreach ($rutas['data'] as $key => $ruta) {
            $filtros = [
                //['created_at', '>=', $f1],
                ['created_at', '>=', $f2],
                //['fec_cerrado', '=', null],
                ['rutas_id', '=', $ruta['id']],
                ['status', '<>', 0],
            ];
            $options['filtros'] = $filtros;
            $ruteos = $this->getDatosDbCache(
                $request,
                $modelo,
                $cols,
                $options
            );

            $disp=[];
            $cantRuteos = count($ruteos['data']);
            if ($cantRuteos > 0) {
                foreach ($ruteos['data'] as $key => $ruteo) {
                    if (empty($ruteo['fec_cerrado'])) {
                        if ($ruteo['created_at'] >= $f1) {
                            $rOpen[] = $ruteo;
                        } else {
                            $rRetrased[] = $ruteo;
                        }
                    }else {
                        //if (date("Ymd", strtotime($ruteo['created_at']))>=date("Ymd", strtotime($f2))){
                            //$ruteo['created_at']>= $f2) {
//                        if ($ruteo['created_at'] >= $f1) {
                            $rClosed[] = $ruteo;
  //                      }
                        // if (date("Ymd", strtotime($ruteo['created_at']))<date("Ymd", strtotime($f1))){
                        // //if ($ruteo['created_at'] < $f1) {
                        //     $rDispon[] = $ruta;
                        // }
                        // Mk_debug::msgApi(['Entro a create at', date("Ymd", strtotime($f1)),date("Ymd", strtotime($ruteo['created_at'])),(date("Ymd", strtotime($ruteo['created_at']))>=date("Ymd", strtotime($f1)))]);
                        // if (date("Ymd", strtotime($ruteo['created_at']))>=date("Ymd", strtotime($f1))){
                        // //if ($ruteo['created_at'] > $f1) {
                        //     Mk_debug::msgApi(['Entro a create at', $ruteo]);
                        //     $rDispon1=[];
                        //     foreach ($rDispon as $keyD=>$dis){
                        //         if ($dis->id!=$ruteo['rutas_id']){
                        //             $rDispon1[] = $dis;
                        //         }
                        //     }
                        //     $rDispon=$rDispon1;
                        // }
                        // if ($ruteo['created_at'] < $f1) {
                        //     $rDispon[] = $ruta;
                        // }

                    }
                }
            } 
        }

        foreach ($rutas['data'] as $key => $ruta) {
            $filtros = [
                ['created_at', '>=', $f1],
                //['created_at', '<=', $f2],
                //['fec_cerrado', '<>', null],
                ['rutas_id', '=', $ruta['id']],
                ['status', '<>', 0],
            ];
            $options['filtros']=$filtros;
            $ruteos = $this->getDatosDbCache(
                $request,
                $modelo,
                $cols,
                $options
            );

            $cantRuteos = count($ruteos['data']);
            if ($cantRuteos == 0) {
                    $rDispon[] = $ruta;
            }
        }

        $result = [
            'dispon' => [
                'ok' => count($rDispon),
                'data' => $rDispon,
            ],
            'open' => [
                'ok' => count($rOpen),
                'data' => $rOpen,
            ],
            'closed' => [
                'ok' => count($rClosed),
                'data' => $rClosed,
            ],
            'retrased' => [
                'ok' => count($rRetrased),
                'data' => $rRetrased,
            ],
        ];
        return Mk_db::sendData(count($result), $result, '');
    }
}
