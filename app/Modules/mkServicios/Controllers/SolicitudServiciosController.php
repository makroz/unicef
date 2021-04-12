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
        
        if (@is_array($request->paramsExtra['servicios'])){
            $data = [];
            $now=date('Y-m-d H:i:s');
            $evaluaciones_id=$request->evaluaciones_id?$request->evaluaciones_id:null;
            foreach ($request->paramsExtra['servicios'] as $servicios){
                
                $data[]=[
                    'created_at'=>$now,
                    'updated_at'=> $now,
                    'usuarios_id_1'=>$user_id,
                    'fecha_1'=>$now,
                    'evaluaciones_id'=>$evaluaciones_id,
                    'servicios_id'=>$servicios['id'],
                    'beneficiarios_id'=>$request->beneficiarios_id,
                    'cant'=>$servicios['cant'],
                    'estado'=>1,
                    'status'=>1,
                ];
            }
//            Mk_debug::msgApi(['data:',$data]);
            $r=$modelo::insert($data);
            if ($r){
                $r=count($request->paramsExtra['servicios'])+1;
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
