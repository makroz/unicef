<?php

namespace App\Modules\mkBeneficiarios\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;

class BeneficiariosController extends Controller
{
    use Mk_ia_db;
    //public $_autorizar='';
    protected $__modelo='';
    public function __construct(Request $request)
    {
        parent::__construct($request);
        return true;
    }

    public function beforeSave(Request $request, &$modelo, $id = 0)
    {
        if (!empty($request->lat)&&!empty($request->lng)) {
            if (!$modelo){
                $modelo=[];
            }
            $modelo['coord'] = DB::raw(
                "ST_GeomFromText('POINT({$request->lat} {$request->lng})')"
            );
        }


        return true;
    }

    public function afterSave(Request $request, $modelo, $error=0, $id=0)
    {
        $now=date('Y-m-d H:i:s');
        $user_id=0;
        if (Mk_auth::get()->isLogin()){
            $user_id=Mk_auth::get()->getUser()->id;
        }

        // if ($request->has('firmados')){
        //     $ids=join(',',$request->firmados);
        //     DB::delete('delete from doc_firmados where beneficiario_id=?', [$id]);
        //     if ($id>0){
        //         DB::update('update beneficiarios set rutas_id = null where rutas_id = ?', [$id]);
        //     }
        //     if (!empty($ids)){
        //         DB::update("update beneficiarios set rutas_id = ? where id in ($ids)", [$id]);
        //     }
        //     $this->clearCache('beneficiarios');
        // }

        if ($request->has('metodos') && is_array($request->metodos)) {

          DB::delete('delete from beneficiarios_info_metodos where beneficiario_id=?', [$id]);

          if (count($request->metodos)>0) {
              $values=[];
              $data=[];
              foreach ($request->metodos as $key=>$metodo) {
                      $data[]=$metodo;
                      $data[]=$id;
                      $values[]='(?,?)';
              }
              if (count($data)>0) {
                  $values=join(',', $values);
                  DB::insert('insert into beneficiarios_info_metodos (info_metodo_id,beneficiario_id) values '.$values, $data);
              }
          }
          //$this->clearCache('beneficiarios_doc_firmados');
      }

      if ($request->has('firmados') && is_array($request->firmados)) {

        DB::delete('delete from beneficiarios_doc_firmados where beneficiario_id=?', [$id]);

        if (count($request->firmados)>0) {
            $values=[];
            $data=[];
            foreach ($request->firmados as $key=>$firmado) {
                if ($firmado=="1") {
                    $data[]=$key;
                    $data[]=$id;
                    $values[]='(?,?)';
                }
            }
            if (count($data)>0) {
                $values=join(',', $values);
                DB::insert('insert into beneficiarios_doc_firmados (doc_firmado_id,beneficiario_id) values '.$values, $data);
            }
        }
        //$this->clearCache('beneficiarios_doc_firmados');
    }


        if ($request->has('problemas') && is_array($request->problemas)) {

          DB::delete('delete from prob_sol_existentes where beneficiario_id=?', [$id]);

          if (count($request->problemas)>0) {
              $values=[];
              $data=[];
              foreach ($request->problemas as $key=>$problema) {
                if (!empty($problema)) {
                    $data[]=$now;
                    $data[]=$now;
                    $data[]=$user_id;
                    $data[]=$user_id;
                    $data[]=$problema['problemas'];
                    $data[]=$problema['soluciones'];
                    $data[]=$id;
                    $data[]=1;
                    $values[]='(?,?,?,?,?,?,?,?)';
                }
              }
              if (count($data)>0) {
                  $values=join(',', $values);
                  DB::insert('insert into prob_sol_existentes (created_at,updated_at,created_by,updated_by,problemas,soluciones,beneficiario_id,status) values '.$values, $data);
              }
          }
          $this->clearCache('prob_sol_existentes');
      }

    }

    
}