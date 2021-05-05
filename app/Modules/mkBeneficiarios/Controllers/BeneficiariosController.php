<?php

namespace App\Modules\mkBeneficiarios\Controllers;

use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BeneficiariosController extends Controller
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
        if (!empty($request->lat) && !empty($request->lng)) {
            if (!$modelo) {
                $modelo = [];
            }
            $modelo['coord'] = DB::raw(
                "ST_GeomFromText('POINT({$request->lat} {$request->lng})')"
            );
        }

        return true;
    }

    public function afterSave(Request $request, $modelo, $action = 0, $id = 0)
    {
        $now     = date('Y-m-d H:i:s');
        $user_id = 0;
        if (Mk_auth::get()->isLogin()) {
            $user_id = Mk_auth::get()->getUser()->id;
        }

        if ($request->has('metodos') && is_array($request->metodos)) {

            DB::delete('delete from beneficiarios_info_metodos where beneficiario_id=?', [$id]);

            if (count($request->metodos) > 0) {
                $values = [];
                $data   = [];
                foreach ($request->metodos as $key => $metodo) {
                    $data[]   = $metodo;
                    $data[]   = $id;
                    $values[] = '(?,?)';
                }
                if (count($data) > 0) {
                    $values = join(',', $values);
                    DB::insert('insert into beneficiarios_info_metodos (info_metodo_id,beneficiario_id) values ' . $values, $data);
                }
            }
            //$this->clearCache('beneficiarios_doc_firmados');
        }

        if ($request->has('firmados') && is_array($request->firmados)) {

            DB::delete('delete from beneficiarios_doc_firmados where beneficiario_id=?', [$id]);

            if (count($request->firmados) > 0) {
                $values = [];
                $data   = [];
                foreach ($request->firmados as $key => $firmado) {
                    if ($firmado == "1") {
                        $data[]   = $key;
                        $data[]   = $id;
                        $values[] = '(?,?)';
                    }
                }
                if (count($data) > 0) {
                    $values = join(',', $values);
                    DB::insert('insert into beneficiarios_doc_firmados (doc_firmado_id,beneficiario_id) values ' . $values, $data);
                }
            }
            //$this->clearCache('beneficiarios_doc_firmados');
        }

        if ($request->has('familiares') && is_array($request->familiares)) {

            DB::delete('delete from familiares where beneficiario_id=?', [$id]);

            if (count($request->familiares) > 0) {
                $values = [];
                $data   = [];
                foreach ($request->familiares as $key => $familiar) {
                    if (!empty($familiar['name'])) {
                        $data[]   = $now;
                        $data[]   = $now;
                        $data[]   = $user_id;
                        $data[]   = $user_id;
                        $data[]   = $familiar['name'];
                        $data[]   = $familiar['edad'];
                        $data[]   = $familiar['genero'];
                        $data[]   = 1;
                        $data[]   = $id;
                        $data[]   = $familiar['parentesco_id'];
                        $data[]   = $familiar['est_civil_id'];
                        $data[]   = $familiar['niv_educativo_id'];
                        $data[]   = $familiar['ocupacion_id'];
                        $values[] = '(?,?,?,?,?,?,?,?,?,?,?,?,?)';
                    }
                }
                if (count($data) > 0) {
                    $values = join(',', $values);
                    DB::insert('insert into familiares (created_at,updated_at,created_by,updated_by,name,edad,genero,status,
              beneficiario_id,parentesco_id,est_civil_id,niv_educativo_id,ocupacion_id) values ' . $values, $data);
                }
            }
            $this->clearCache('familiares');
        }

        if ($request->has('problemas') && is_array($request->problemas)) {

            DB::delete('delete from prob_sol_existentes where beneficiario_id=?', [$id]);

            if (count($request->problemas) > 0) {
                $values = [];
                $data   = [];
                foreach ($request->problemas as $key => $problema) {
                    if (!empty($problema)) {
                        $data[]   = $now;
                        $data[]   = $now;
                        $data[]   = $user_id;
                        $data[]   = $user_id;
                        $data[]   = $problema['problemas'];
                        $data[]   = $problema['soluciones'];
                        $data[]   = $id;
                        $data[]   = 1;
                        $values[] = '(?,?,?,?,?,?,?,?)';
                    }
                }
                if (count($data) > 0) {
                    $values = join(',', $values);
                    DB::insert('insert into prob_sol_existentes (created_at,updated_at,created_by,updated_by,problemas,soluciones,beneficiario_id,status) values ' . $values, $data);
                }
            }
            $this->clearCache('prob_sol_existentes');
        }

    }

}
