<?php

namespace App\Modules\mkCapacitaciones\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use Illuminate\Support\Facades\DB;
//use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;

class Sesion_grupalesController extends Controller
{
    use Mk_ia_db;
    public $_autorizar='';
    protected $__modelo='';
    public function __construct(Request $request)
    {
        parent::__construct($request);
        return true;
    }

    public function afterSave(Request $request, $modelo, $action=0, $id=0)
    {
        // $now=date('Y-m-d H:i:s');
        // $user_id=0;
        // if (Mk_auth::get()->isLogin()) {
        //     $user_id=Mk_auth::get()->getUser()->id;
        // }

        // if ($action==0) {
        //   $_key = $modelo->getKeyName();
        //   $id    = $modelo->$_key;
        // }

        if ($request->has('beneficiarios') && is_array($request->beneficiarios)) {
            DB::delete('delete from asistentes_grupales where sesion_grupal_id=?', [$id]);

            if (count($request->beneficiarios)>0) {
                $values=[];
                $data=[];
                foreach ($request->beneficiarios as $key=>$beneficiario) {
                    $data[]=$id;
                    $data[]=$beneficiario;
                    $values[]='(?,?)';
                }
                if (count($data)>0) {
                    $values=join(',', $values);
                    DB::insert('SET FOREIGN_KEY_CHECKS=0');
                    DB::insert('insert into asistentes_grupales (sesion_grupal_id,beneficiario_id) values '.$values, $data);
                    DB::insert('SET FOREIGN_KEY_CHECKS=1');
                }
            }
            //$this->clearCache('asistentes_grupales');
        }
    }
}