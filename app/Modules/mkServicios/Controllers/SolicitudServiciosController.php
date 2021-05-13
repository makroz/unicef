<?php

namespace App\Modules\mkServicios\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_helpers\Mk_db;
use App\Modules\mkBase\Mk_helpers\Mk_debug;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;

class SolicitudServiciosController extends Controller
{
    use Mk_ia_db;
    public $_autorizar='';
    protected $__modelo='';
    public function __construct(Request $request)
    {
        parent::__construct($request);
        return true;
    }
    public function beforeSave(Request $request, $modelo, $id=0)
    {
        $user_id=0;
        if (Mk_auth::get()->isLogin()){
            $user_id=Mk_auth::get()->getUser()->id;
        }
        
        if (!empty($request->servicios) && is_array($request->servicios)){
            $data = [];
            $now=date('Y-m-d H:i:s');
            if ($id==0) {
                $evaluaciones_id=$request->evaluaciones_id?$request->evaluaciones_id:null;
                foreach ($request->servicios as $servicios) {
                    $data[]=[
                  'created_by'=>$user_id,
                  'updated_by'=> $user_id,
                    'created_at'=>$now,
                    'updated_at'=> $now,
                    'evaluaciones_id'=>$evaluaciones_id,
                    'servicios_id'=>$servicios['id'],
                    'beneficiarios_id'=>$request->beneficiarios_id,
                    'cant'=>$servicios['cant'],
                    'estado'=>0,
                    'status'=>1,
                ];
                }
//            Mk_debug::msgApi(['data:',$data]);
                $r=$modelo::insert($data);
            }else{
              $estado=$request->estado;
              foreach ($request->servicios as $servicios) {
                $data=[
                    'fecha_'.$estado=>$now,
                    'usuarios_id_'.$estado=>$user_id,
                    'estado'=>$estado,
                    'updated_by'=> $user_id,
                    'updated_at'=> $now,
                ];
                $r=$this->__modelo::where('id', $servicios['sol_id'])->update($data);
              }
            }
            if ($r){
                $r=count($request->servicios)+1;
            }else{
                $r=-1;
            }
  //          Mk_debug::msgApi(['modelo grabado:',$r]);
            return $r;
        }else{
            return true;
        }
        
    } 

}
