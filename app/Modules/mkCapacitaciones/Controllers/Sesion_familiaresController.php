<?php

namespace App\Modules\mkCapacitaciones\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\mkBase\Mk_helpers\Mk_debug;

class Sesion_familiaresController extends Controller
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
      //Mk_debug::msgApi(['afterSave',$modelo,  $request]);
        if ($request->has('apoyos') && is_array($request->apoyos['apoyos'])) {
          if ($action=0){
            $bene=$request->beneficiario_id;
          }else{
            $bene=$request->beneficiario_id_;
          }
            DB::delete('delete from requiere_apoyos where beneficiario_id=?', [$bene]);

            if (count($request->apoyos['apoyos'])>0) {
                $values=[];
                $data=[];
                foreach ($request->apoyos['apoyos'] as $key=>$apoyo) {
                    $data[]=$apoyo;
                    $data[]=$bene;
                    $values[]='(?,?)';
                }
                if (count($data)>0) {
                    $values=join(',', $values);
                    DB::insert('insert into requiere_apoyos (lista_apoyo_id,beneficiario_id) values '.$values, $data);
                }
            }
            //$this->clearCache('asistentes_grupales');
        }
    }
}