<?php

namespace App\Modules\mkServicios\Controllers;

use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkServicios\Comercial;
use App\Modules\mkServicios\Orden_servicios;
use App\Modules\mkServicios\SolicitudServicios;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Orden_serviciosController extends Controller
{
    use Mk_ia_db;
    public $_autorizar  = '';
    protected $__modelo = '';
    public function __construct(Request $request)
    {
        parent::__construct($request);
        return true;
    }

    public function beforeSave(Request $request, &$modelo, $id = 0)
    {
        $user_id = 0;
        if (Mk_auth::get()->isLogin()) {
            $user_id = Mk_auth::get()->getUser()->id;
        }
        $now = date('Y-m-d H:i:s');

        if ($request->act == 'comercial') {
            $modelo  = [];
            $estUser = 6;

            $data   = [];
            $data[] = $now;
            $data[] = $now;
            $data[] = $user_id;
            $data[] = $user_id;
            $values = '(?,?,?,?)';
            DB::insert('insert into comercial (created_at,updated_at,created_by,updated_by) values ' . $values, $data);
            $comercial = DB::getPdo()->lastInsertId();
            $this->clearCache('comercial');

            $data = [
                'estado'       => $estUser,
                'comercial_id' => $comercial,
                'updated_by'   => $user_id,
                'updated_at'   => $now,
            ];
            $r = Orden_servicios::where('estado', 5)->update($data);
            $this->clearCache('orden_servicios');

            $data = [
                'fecha_' . $estUser       => $now,
                'comercial_id'            => $comercial,
                'usuarios_id_' . $estUser => $user_id,
                'estado'                  => $estUser,
                'updated_by'              => $user_id,
                'updated_at'              => $now,
            ];
            $r = SolicitudServicios::where('estado', 5)->update($data);
            $this->clearCache('SolicitudServicios');

        }

        if (!empty($request->servicios) && is_array($request->servicios)) {
            $data = [];
            if (empty($id)) {

            } else {
                if ($request->act == 'finalizar') {
                    $modelo  = [];
                    $estUser = 7;

                    $data = [
                        'estado'     => $estUser,
                        'updated_by' => $user_id,
                        'updated_at' => $now,
                    ];
                    $r = Orden_servicios::wherein('comercial_id', $request->servicios)->update($data);
                    $this->clearCache('orden_servicios');
                    $r = Comercial::wherein('id', $request->servicios)->update($data);
                    $this->clearCache('comercial');

                    $data = [
                        'fecha_' . $estUser       => $now,
                        'usuarios_id_' . $estUser => $user_id,
                        'estado'                  => $estUser,
                        'updated_by'              => $user_id,
                        'updated_at'              => $now,
                    ];
                    $r = SolicitudServicios::wherein('comercial_id', $request->servicios)->update($data);
                    $this->clearCache('SolicitudServicios');
                }
                if ($request->act == 'autorizar') {
                    $modelo['estado'] = 5;
                    $estUser          = 5;

                    $data = [
                        'fecha_' . $estUser       => $now,
                        'usuarios_id_' . $estUser => $user_id,
                        'estado'                  => $estUser,
                        'updated_by'              => $user_id,
                        'updated_at'              => $now,
                    ];
                    $r = SolicitudServicios::where('orden_servicios_id', $id)->where('estado', 4)->update($data);
                    $this->clearCache('SolicitudServicios');
                }

                if ($request->act == 'verificar') {
                    $modelo['estado'] = 4;
                    $estUser          = 4;

                    foreach ($request->servicios as $servicios) {
                        $data = [
                            'fecha_' . $estUser       => $now,
                            'usuarios_id_' . $estUser => $user_id,
                            'estado'                  => $servicios['verificado'],
                            'obs_verif'               => !empty($servicios['obs_verif']) ? $servicios['obs_verif'] : null,
                            'updated_by'              => $user_id,
                            'updated_at'              => $now,
                        ];
                        $r = SolicitudServicios::where('id', $servicios['sol_id'])->update($data);

                        DB::delete('delete from reprogramados where solicitud_servicio_id=?', [$servicios['sol_id']]);
                        if ($servicios['verificado'] == 1) {
                            $sol = DB::select('select usuarios_id_3 as user from solicitud_servicios where id = ?', [$servicios['sol_id']]);
                            //Mk_debug::msgApi(['sol:', $sol[0]->user]);

                            //$values = [];
                            $data   = [];
                            $data[] = $now;
                            $data[] = $now;
                            $data[] = $user_id;
                            $data[] = $user_id;
                            $data[] = $servicios['sol_id'];
                            $data[] = !empty($servicios['obs_verif']) ? $servicios['obs_verif'] : null;
                            $data[] = $sol[0]->user;
                            $data[] = $id;
                            $values = '(?,?,?,?,?,?,?,?)';
                            DB::insert('insert into reprogramados (created_at,updated_at,created_by,updated_by,solicitud_servicio_id,obs,recolector_id,orden_servicio_id) values ' . $values, $data);

                        }

                        DB::delete('delete from control_solicitudes where solicitud_servicio_id=?', [$servicios['sol_id']]);
                        if (!empty($servicios['qa'])) {

                            $values = [];
                            $data   = [];
                            foreach ($servicios['qa'] as $qa) {
                                if (!empty($qa['id'])) {
                                    $data[]   = $now;
                                    $data[]   = $now;
                                    $data[]   = $user_id;
                                    $data[]   = $user_id;
                                    $data[]   = $servicios['sol_id'];
                                    $data[]   = $qa['puntos'];
                                    $data[]   = $qa['id'];
                                    $values[] = '(?,?,?,?,?,?,?)';
                                }
                            }
                            if (count($data) > 0) {
                                $values = join(',', $values);
                                DB::insert('insert into control_solicitudes (created_at,updated_at,created_by,updated_by,solicitud_servicio_id,puntos,control_calidad_id) values ' . $values, $data);

                            }
                        }
                        $this->clearCache('reprogramados');
                        $this->clearCache('control_solicitudes');
                        $this->clearCache('SolicitudServicios');
                    }
                    //$modelo = [];
                }
            }
            if ($r) {
                $r = count($request->servicios) + 1;
            } else {
                $r = -1;
            }
            return $r;
        } else {
            return true;
        }

    }

}
