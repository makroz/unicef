<?php

namespace App\Modules\mkRecolector\Controllers;

use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Check_diariosController extends Controller
{
    use Mk_ia_db;
    public $_autorizar  = '';
    protected $__modelo = '';
    public function __construct(Request $request)
    {
        parent::__construct($request);
        return true;
    }

    public function afterSave(Request $request, $modelo, $action = 0, $id = 0)
    {
        $now     = date('Y-m-d H:i:s');
        $user_id = 0;
        if (Mk_auth::get()->isLogin()) {
            $user_id = Mk_auth::get()->getUser()->id;
        }

        // $query=',created_at,created_by';
        // if ($action>0){
        //   $query=',updated_at,updated_by';
        // }

        if ($request->has('materiales') && is_array($request->materiales)) {
            if ($action > 0) {
                DB::delete('delete from check_materiales where diario_id=?', [$id]);
            }

            if (count($request->materiales) > 0) {
                $values = [];
                $data   = [];
                foreach ($request->materiales as $key => $material) {
                    $data[]   = $id;
                    $data[]   = $material['id'];
                    $data[]   = $material['cant'];
                    $data[]   = $now;
                    $data[]   = $user_id;
                    $values[] = '(?,?,?,?,?)';
                }
                if (count($data) > 0) {
                    $values = join(',', $values);
                    DB::insert("insert into check_materiales (diario_id,material_id,cant,created_at,created_by) values " . $values, $data);
                }
            }
            $this->clearCache('check_materiales');
        }

        if ($request->has('eventos') && is_array($request->eventos)) {
            if ($action > 0) {
                DB::delete('delete from check_eventos where diario_id=?', [$id]);
            }

            if (count($request->eventos) > 0) {
                $values = [];
                $data   = [];
                foreach ($request->eventos as $key => $evento) {
                    $data[]   = $id;
                    $data[]   = $evento['id'];
                    $data[]   = $evento['detalle'];
                    $data[]   = $now;
                    $data[]   = $user_id;
                    $values[] = '(?,?,?,?,?)';
                }
                if (count($data) > 0) {
                    $values = join(',', $values);
                    DB::insert("insert into check_eventos (diario_id,evento_id,detalle,created_at,created_by) values " . $values, $data);
                }
            }
            $this->clearCache('check_eventos');
        }

        if ($request->has('checks') && is_array($request->checks)) {
            if ($action > 0) {
                DB::delete('delete from check_det where diario_id=?', [$id]);
            }

            if (count($request->checks) > 0) {
                $values = [];
                $data   = [];
                foreach ($request->checks as $key => $check) {
                    $data[]   = $id;
                    $data[]   = $check['id'];
                    $data[]   = $check['resp'];
                    $data[]   = $now;
                    $data[]   = $user_id;
                    $values[] = '(?,?,?,?,?)';
                }
                if (count($data) > 0) {
                    $values = join(',', $values);
                    DB::insert("insert into check_det (diario_id,check_id,resp,created_at,created_by) values " . $values, $data);
                }
            }
            $this->clearCache('check_det');
        }

    }

}
