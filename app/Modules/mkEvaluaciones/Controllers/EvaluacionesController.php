<?php

namespace App\Modules\mkEvaluaciones\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\mkBase\Mk_helpers\Mk_debug;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;

class EvaluacionesController extends Controller
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
        $user_id=0;
        if (Mk_auth::get()->isLogin()) {
            $user_id=Mk_auth::get()->getUser()->id;
        }
        $modelo['usuarios_id']=$user_id;
        if (!empty($request->lat)&&!empty($request->lng)) {
            if (!$modelo) {
                $modelo=[];
            }
            $modelo['coord'] = DB::raw(
                "ST_GeomFromText('POINT({$request->lat} {$request->lng})')"
            );
        }
        return true;
    }

    public function afterSave(Request $request, $modelo, $action=0, $id=0)
    {
        if ($request->has('respuestas')) {
            $now=date('Y-m-d H:i:s');
            if ($action==0) {
                $values=[];
                foreach ($request->respuestas as $key=>$respuesta) {
                    //$data=[$now,$now,$id,$key,$respuesta,1];
                    //DB::insert('insert into respuestas (created_at,updated_at,evaluaciones_id,preguntas_id,r_s,status) values (?,?,?,?,?,?)', $data);
                    $data[]=$now;
                    $data[]=$now;
                    $data[]=$id;
                    $data[]=$key;
                    $data[]=$respuesta;
                    $data[]=1;
                    $values[]='(?,?,?,?,?,?)';
                }
                $values=join(',',$values);
                DB::insert('insert into respuestas (created_at,updated_at,evaluaciones_id,preguntas_id,r_s,status) values '.$values, $data);
                $this->clearCache('respuestas');
            }else{
                foreach ($request->respuestas as $key=>$respuesta) {
                    DB::update('update respuestas set r_s=? where evaluaciones_id=? and preguntas_id=? ', [$respuesta,$id,$key]);
                }
            }
        }
    }
}