<?php

namespace App\Modules\mkServicios\Controllers;

use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolicitudServiciosController extends Controller
{
    use Mk_ia_db;
    public $_autorizar  = '';
    protected $__modelo = '';
    public function __construct(Request $request)
    {
        parent::__construct($request);
        return true;
    }
    public function beforeSave(Request $request, &$modelo, &$id = 0)
    {
        $user_id = 0;
        if (Mk_auth::get()->isLogin()) {
            $user_id = Mk_auth::get()->getUser()->id;
        }
        $now = date('Y-m-d H:i:s');
        if (!empty($request->servicios) && is_array($request->servicios)) {
            $data = [];
            if (empty($id)) {
                $evaluaciones_id = $request->evaluaciones_id ? $request->evaluaciones_id : null;
                foreach ($request->servicios as $servicios) {
                    $data[] = [
                        'created_by'       => $user_id,
                        'updated_by'       => $user_id,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                        'evaluaciones_id'  => $evaluaciones_id,
                        'servicios_id'     => $servicios['id'],
                        'beneficiarios_id' => $request->beneficiarios_id,
                        'cant'             => $servicios['cant'],
                        'estado'           => 0,
                        'status'           => 1,
                    ];
                }
//            Mk_debug::msgApi(['data:',$data]);
                $r = $modelo::insert($data);
            } else {
                $estado = $request->estado;
                $data   = [];
                $data[] = $now;
                $data[] = $now;
                $data[] = $user_id;
                $data[] = $user_id;
                $data[] = $request->ref;
                $data[] = $request->obs;
                $data[] = $request->forma_pago;
                $data[] = empty($request->imgFile) ? 0 : 1;
                $data[] = $user_id;
                $data[] = $id;
                $data[] = 1;
                $values = '(?,?,?,?,?,?,?,?,?,?,?)';
                DB::insert('insert into orden_servicios (created_at,updated_at,created_by,updated_by,ref,obs,forma_pago_id,foto,recolector_id,beneficiario_id,status) values ' . $values, $data);
                $id     = DB::getPdo()->lastInsertId();
                foreach ($request->servicios as $servicios) {
                    $data = [
                        'fecha_' . $estado       => $now,
                        'usuarios_id_' . $estado => $user_id,
                        'estado'                 => ($estado == 3 && empty($servicios['realizado'])) ? 9 : $estado,
                        'obs'                    => !empty($servicios['obs'])?$servicios['obs']:null,
                        'orden_servicios_id'     => $id,
                        'updated_by'             => $user_id,
                        'updated_at'             => $now,
                    ];
                    $r = $this->__modelo::where('id', $servicios['sol_id'])->update($data);

                    if ($request->estado == 3 && !empty($servicios['materiales'])) {
                        $values = [];
                        $data   = [];
                        foreach ($servicios['materiales'] as $material) {
                            if (!empty($material['id'])) {
                                $data[]   = $now;
                                $data[]   = $now;
                                $data[]   = $user_id;
                                $data[]   = $user_id;
                                $data[]   = $material['id'];
                                $data[]   = $material['cant'];
                                $data[]   = $servicios['sol_id'];
                                $values[] = '(?,?,?,?,?,?,?)';
                            }
                        }
                        if (count($data) > 0) {
                            $values = join(',', $values);
                            DB::insert('insert into meteriales_usados (created_at,updated_at,created_by,updated_by,material_id,cant,solicitud_servicio_id) values ' . $values, $data);
                        }

                    }

                }
               
                $modelo = [];
            }
            if ($r) {
                $r = count($request->servicios) + 1;

            } else {
                $r = -1;
            }
            //          Mk_debug::msgApi(['modelo grabado:',$r]);
            return $r;
        } else {
            return true;
        }

    }

}
