<?php

namespace App\Modules\mkRutas\Controllers;

use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;
use App\Modules\mkBase\Mk_helpers\Mk_date;
use App\Modules\mkBase\Mk_helpers\Mk_db;
use App\Modules\mkBase\Mk_helpers\Mk_debug;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

const _errorNoExiste = -1;
class RuteosController extends Controller
{
    use Mk_ia_db;
    public $_autorizar = '';

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
                $modelo['open_id']  = $user;
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
            $user                  = Mk_auth::get()->getUser()->id;
            $modelo['close_id']    = $user;
            $modelo['estado']      = 2;
            $modelo['fec_cerrado'] = date('Y-m-d H:i:s');
            $modelo['gps_close']   = DB::raw(
                "ST_GeomFromText('POINT({$request->lat} {$request->lng})')"
            );
            $id = explode(',', $request->id);
            DB::beginTransaction();
            $datos = new $this->__modelo();
            $_key  = $datos->getKeyName();
            $r     = $datos->wherein($_key, $id)
                ->update($modelo);
            $msg = '';
            if ($r == 0) {
                $r   = _errorNoExiste;
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
        $modelo = 'App\Modules\mkRutas\Rutas';
        $cols   = 'id,id as rutas_id,name,descrip,usuarios_id,status';
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
        $fi      = date('oWN');
        $filtros = [
            ['usuarios_id', '=', $userId],
            ['status', '<>', 0],
        ];

        $rutas     = $this->getDatosDbCache($request, $modelo, $cols, ['filtros' => $filtros, 'relations' => ['beneficiarios:rutas_id,id'], 'send' => false]);
        $modelo    = 'App\Modules\mkRutas\Ruteos';
        $cantRutas = count($rutas);

        $fecha_actual = date("d-m-Y");
        $ds           = date('N');
        $dt           = $ds - 1;
        $f1           = date("Y-m-d", strtotime($fecha_actual . "- {$dt} day"));
        $dt           = $dt + 7;
        $f2           = date("Y-m-d", strtotime($fecha_actual . "- {$dt} day"));
        $cols         = 'id,rutas_id,obs,usuarios_id,status,created_at,fec_cerrado';
        $rDispon      = [];
        $rOpen        = [];
        $rClosed      = [];
        $rRetrased    = [];
        $options      = [
            'filtros'   => $filtros,
            'relations' => [
                'evaluaciones:ruteos_id,id,obs,beneficiarios_id,fec_verif,estado',
                'evaluaciones.respuestas',
                'evaluaciones.servicios',
            ],
            'send'      => false,
        ];

        foreach ($rutas as $key => $ruta) {
            $filtros = [
                //['created_at', '>=', $f1],
                ['created_at', '>=', $f2],
                //['fec_cerrado', '=', null],
                ['rutas_id', '=', $ruta['id']],
                ['usuarios_id', '=', $userId],
                ['status', '<>', 0],
            ];
            $options['filtros'] = $filtros;
            $ruteos             = $this->getDatosDbCache(
                $request,
                $modelo,
                $cols,
                $options
            );

            $disp       = [];
            $cantRuteos = count($ruteos);
            if ($cantRuteos > 0) {
                foreach ($ruteos as $key => $ruteo) {
                    //Mk_debug::msgApi(['Fechas:', $userId, $ruteo['id'], Mk_date::dateToUTC($ruteo['created_at']), Mk_date::dateToUTC($ruteo['fec_cerrado']), Mk_date::dateToUTC($ruteo['created_at']) >= Mk_date::dateToUTC($ruteo['fec_cerrado'])]);
                    if (empty($ruteo['fec_cerrado'])) {
                        if (Mk_date::dateToLocal($ruteo['created_at']) >= Mk_date::dateToLocal($f1)) {
                            //$ruteo['active']=true;
                            $rOpen[] = $ruteo;
                        } else {
                            //$ruteo['active']=true;
                            $rRetrased[] = $ruteo;
                        }
                    } else {
                        $rClosed[] = $ruteo;
                    }
                }
            }
        }

        foreach ($rutas as $key => $ruta) {
            $filtros = [
                ['created_at', '>=', $f1],
                //['created_at', '<=', $f2],
                //['fec_cerrado', '<>', null],
                ['rutas_id', '=', $ruta['id']],
                ['status', '<>', 0],
            ];
            $options['filtros'] = $filtros;
            $ruteos             = $this->getDatosDbCache(
                $request,
                $modelo,
                $cols,
                $options
            );

            $cantRuteos = count($ruteos);
            if ($cantRuteos == 0) {
                $ruta['active'] = true;
                $rDispon[]      = $ruta;
            }
        }

        $result = [
            'dispon'   => [
                'ok'   => count($rDispon),
                'data' => $rDispon,
            ],
            'open'     => [
                'ok'   => count($rOpen),
                'data' => $rOpen,
            ],
            'closed'   => [
                'ok'   => count($rClosed),
                'data' => $rClosed,
            ],
            'retrased' => [
                'ok'   => count($rRetrased),
                'data' => $rRetrased,
            ],
        ];
        return Mk_db::sendData(count($result), $result, '');
    }

    public function reportes(Request $request)
    {
        $this->proteger('show');
        $result   = [];
        $desde    = $request->desde;
        $hasta    = $request->hasta;
        $dSemana  = date('N', strtotime($desde)) - 1;
        $fInicial = strtotime($desde . "- {$dSemana} day");
        $dSemana  = 7 - date('N', strtotime($hasta));
        $fFinal   = strtotime($hasta . "+ {$dSemana} day");
        $dias     = ($fFinal - $fInicial) / (60 * 60 * 24) + 1;
        if (!empty($request->reportes['evalMon'])) {
            $modelo = 'App\Modules\mkRutas\Rutas';
            $cols   = 'id';
            if (!empty($request->ruta) && $request->ruta > 0) {
                $rutas    = $this->getDatosDbCache($request, $modelo, $cols, ['filtros' => [['id', '=', $request->ruta], ['status', '<>', 0]], 'relations' => ['beneficiarios:rutas_id,id'], 'send' => false]);
                $ruteos   = DB::select('select count(*) as cant  from ruteos where DATE(created_at) >= ? and DATE(created_at)<=? and rutas_id=? ', [date("Y-m-d", $fInicial), date("Y-m-d", $fFinal), $request->ruta]);
                $eval     = DB::select('select count(*) as cant  from evaluaciones left join ruteos on (ruteos.id=evaluaciones.id) where DATE(evaluaciones.created_at) >= ? and DATE(evaluaciones.created_at)<=? and ruteos.rutas_id=? ', [date("Y-m-d", $fInicial), date("Y-m-d", $fFinal), $request->ruta]);
                $evalComp = DB::select('select count(*) as cant  from evaluaciones left join ruteos on (ruteos.id=evaluaciones.id) where DATE(evaluaciones.created_at) >= ? andDATE(evaluaciones.created_at)<=? and evaluaciones.estado=? and ruteos.rutas_id=?', [date("Y-m-d", $fInicial), date("Y-m-d", $fFinal), 2, $request->ruta]);
            } else {
                $rutas    = $this->getDatosDbCache($request, $modelo, $cols, ['filtros' => [['status', '<>', 0]], 'relations' => ['beneficiarios:rutas_id,id'], 'send' => false]);
                $ruteos   = DB::select('select count(*) as cant  from ruteos where DATE(created_at) >= ? and DATE(created_at)<=? ', [date("Y-m-d", $fInicial), date("Y-m-d", $fFinal)]);
                $eval     = DB::select('select count(*) as cant  from evaluaciones where DATE(created_at) >= ? and DATE(created_at)<=? ', [date("Y-m-d", $fInicial), date("Y-m-d", $fFinal)]);
                $evalComp = DB::select('select count(*) as cant  from evaluaciones where DATE(created_at) >= ? and DATE(created_at)<=? and estado=? ', [date("Y-m-d", $fInicial), date("Y-m-d", $fFinal), 2]);
            }
            $cantRutas = count($rutas);
            $cantBenef = 0;
            foreach ($rutas as $key => $ruta) {
                $cantBenef = $cantBenef + count($ruta['beneficiarios']);
            }
            $cantRutas         = count($rutas);
            $cantRuteos        = ($dias / 7) * $cantRutas;
            $cantEval          = ($dias / 7) * $cantBenef;
            $result['evalMon'] = [
                'ruteosE'  => $cantRuteos,
                'ruteos'   => $ruteos[0]->cant,
                'evalE'    => $cantEval,
                'eval'     => $eval[0]->cant,
                'evalComp' => $evalComp[0]->cant,
                'fechaIni' => date("Y-m-d", $fInicial),
                'fechaFin' => date("Y-m-d", $fFinal),
                'semanas'  => ($dias / 7),
                'benef'    => $cantBenef,
                'dias'     => $dias,
            ];
        }

        if (!empty($request->reportes['solicitudes'])) {
            if (!empty($request->ruta) && $request->ruta > 0) {
                $solicitudes = DB::select('SELECT estado, count(estado) as cant FROM solicitud_servicios left join beneficiarios on (beneficiarios.id=solicitud_servicios.beneficiarios_id) where DATE(solicitud_servicios.created_at) >= ? and DATE(solicitud_servicios.created_at)<=? and beneficiarios.rutas_id=? GROUP by estado', [$desde, $hasta, $request->ruta]);
            } else {
                $solicitudes = DB::select('SELECT estado, count(estado) as cant FROM solicitud_servicios where  DATE(created_at) >= ? and  DATE(created_at)<=? GROUP by estado', [$desde, $hasta]);
            }
            $result['solicitudes'] = $solicitudes;
        }

        if (!empty($request->reportes['ordenes'])) {
            if (!empty($request->ruta) && $request->ruta > 0) {
                $ordenes = DB::select('SELECT forma_pago_id, count(forma_pago_id) as cant FROM orden_servicios left join beneficiarios on (beneficiarios.id=orden_servicios.beneficiario_id) where DATE(orden_servicios.created_at) >= ? and DATE(orden_servicios.created_at)<=? and beneficiarios.rutas_id=? GROUP by forma_pago_id', [$desde, $hasta, $request->ruta]);
            } else {
                $ordenes = DB::select('SELECT forma_pago_id, count(forma_pago_id) as cant FROM orden_servicios where  DATE(created_at) >= ? and  DATE(created_at)<=? GROUP by forma_pago_id', [$desde, $hasta]);
            }
            $result['ordenes'] = $ordenes;
        }

        if (!empty($request->reportes['servicios'])) {
            if (!empty($request->ruta) && $request->ruta > 0) {
                $servicios = DB::select('SELECT servicios_id, sum(cant) as cant FROM solicitud_servicios left join beneficiarios on (beneficiarios.id=solicitud_servicios.beneficiarios_id) where  DATE(solicitud_servicios.created_at) >= ? and DATE(solicitud_servicios.created_at)<=? and beneficiarios.rutas_id=? GROUP by servicios_id', [$desde, $hasta, $request->ruta]);
            } else {
                $servicios = DB::select('SELECT servicios_id, sum(cant) as cant FROM solicitud_servicios where  DATE(created_at) >= ? and  DATE(created_at)<=? GROUP by servicios_id', [$desde, $hasta]);
            }
            $result['servicios'] = $servicios;
        }

        if (!empty($request->reportes['materiales'])) {
            $modelo = 'App\Modules\mkServicios\Materiales';
            $cols   = 'id,name,stock,medida_id,min_stock';

            $filtros = [
                ['stock', '<=', 'min_stock'],
                ['status', '<>', 0],
            ];

            $materiales           = $this->getDatosDbCache($request, $modelo, $cols, ['filtros' => $filtros, 'send' => false]);
            $cantMateriales       = count($materiales);
            $result['materiales'] = $materiales;
        }

        return Mk_db::sendData(count($result), $result, '');
    }

    public function dashboard(Request $request)
    {
        $this->proteger('show');
        $result = [];
        $fecha  = date("Y-m-d");

        if (!empty($request->reportes['materiales'])) {
            $modelo = 'App\Modules\mkServicios\Materiales';
            $cols   = 'id,name,stock,medida_id,min_stock';

            $filtros = [
                ['stock', '<=', 'min_stock'],
                ['status', '<>', 0],
            ];

            $materiales           = $this->getDatosDbCache($request, $modelo, $cols, ['filtros' => $filtros, 'send' => false]);
            $result['materiales'] = $materiales;
        }

        if (!empty($request->reportes['ruteos'])) {

            $modelo  = 'App\Modules\mkRutas\Rutas';
            $cols    = 'id';
            $filtros = [
                ['status', '<>', 0],
            ];

            $rutas = $this->getDatosDbCache($request, $modelo, $cols, ['filtros' => $filtros, 'relations' => ['beneficiarios:rutas_id,id'], 'send' => false]);

            $fecha_actual = date("Y-m-d");
            $ds           = date('N');
            $dt           = $ds - 1;
            $f1           = date("Y-m-d", strtotime($fecha_actual . "- {$dt} day"));
            $dt           = $dt + 7;
            $f2           = date("Y-m-d", strtotime($fecha_actual . "- {$dt} day"));
            $rDispon      = 0;
            $rOpen        = 0;
            $rOpenHoy     = 0;
            $rClosed      = 0;
            $rRetrased    = 0;
            $cantBenef    = 0;

            $rOpen     = DB::select('SELECT count(*) as cant FROM ruteos where fec_cerrado IS NULL and DATE(created_at) >=? and status<>?', [$f1, 0]);
            $rRetrased = DB::select('SELECT count(*) as cant FROM ruteos where fec_cerrado IS NULL and DATE(created_at) <? and status<>?', [$fecha, $f1, 0]);
            $rOpenHoy  = DB::select('SELECT count(*) as cant FROM ruteos where fec_cerrado IS NULL and DATE(created_at) =? and status<>?', [$fecha, 0]);
            $rClosed   = DB::select('SELECT count(*) as cant FROM ruteos where fec_cerrado IS NOT NULL and DATE(created_at) =? and status<>?', [$fecha, 0]);

            foreach ($rutas as $ruta) {
                $cantBenef = $cantBenef + count($ruta['beneficiarios']);
                $filtros   = [
                    ['created_at', '>=', $f1],
                    ['rutas_id', '=', $ruta['id']],
                    ['status', '<>', 0],
                ];
                $options['filtros'] = $filtros;
                $modelo             = 'App\Modules\mkRutas\Ruteos';
                $ruteos             = $this->getDatosDbCache(
                    $request,
                    $modelo,
                    'id',
                    $options
                );

                $cantRuteos = count($ruteos);
                if ($cantRuteos == 0) {
                    $rDispon++;
                }

            }

            $result['ruteos'] = [
                'dispon'   => $rDispon,
                'open'     => $rOpen[0]->cant,
                'openHoy'  => $rOpenHoy[0]->cant,
                'closed'   => $rClosed[0]->cant,
                'retrased' => $rRetrased[0]->cant,
                'benef'    => $cantBenef,
            ];
        }

        if (!empty($request->reportes['evaluaciones'])) {
            $evaluaciones           = DB::select('SELECT estado, count(estado) as cant FROM evaluaciones where DATE(created_at) = ? GROUP by estado', [$fecha]);
            $result['evaluaciones'] = $evaluaciones;
        }

        if (!empty($request->reportes['servicios'])) {
            $servicios           = DB::select('SELECT servicios_id, sum(cant) as cant FROM solicitud_servicios where DATE(created_at) = ? GROUP by servicios_id', [$fecha]);
            $result['servicios'] = $servicios;
        }

        if (!empty($request->reportes['solicitudes'])) {
            $solicitudes           = DB::select('SELECT estado, sum(cant) as cant FROM solicitud_servicios where DATE(created_at) = ? GROUP by estado', [$fecha]);
            $result['solicitudes'] = $solicitudes;
        }

        return Mk_db::sendData(count($result), $result, '');
    }
}
