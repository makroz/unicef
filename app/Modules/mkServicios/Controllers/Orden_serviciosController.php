<?php

namespace App\Modules\mkServicios\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\mkBase\Mk_helpers\Mk_debug;
use App\Modules\mkServicios\SolicitudServicios;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;

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
        if (!empty($request->servicios) && is_array($request->servicios)) {
            $data = [];
            if (empty($id)) {

            } else {
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
                        Mk_debug::msgApi(['r:', $r]);

                        

                        DB::delete('delete from reprogramados where solicitud_servicio_id=?', [$servicios['sol_id']]);
                        if ($servicios['verificado'] == 1) {
                          $sol=DB::select('select usuarios_id_3 as user from solicitud_servicios where id = ?', [$servicios['sol_id']]);
                          //Mk_debug::msgApi(['sol:', $sol[0]->user]);
                          
                          //$values = [];
                          $data   = [];
                            $data[]   = $now;
                            $data[]   = $now;
                            $data[]   = $user_id;
                            $data[]   = $user_id;
                            $data[]   = $servicios['sol_id'];
                            $data[]   = !empty($servicios['obs_verif']) ? $servicios['obs_verif'] : null;
                            $data[]   = $sol[0]->user;
                            $data[]   = $id;
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
