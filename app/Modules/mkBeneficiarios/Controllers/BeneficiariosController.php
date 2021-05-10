<?php

namespace App\Modules\mkBeneficiarios\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\mkBase\Mk_helpers\Mk_db;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;

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

    public function listas(Request $request)
    {
      $this->proteger('show');
      $r=[];
      $modelo = 'App\Modules\mkRutas\Rutas';
      $cols = 'id,name';
      $r['Rutas'] = $this->getDatosDbCache($request, $modelo, $cols, ['send' => false],'Rutas');

      $modelo = 'App\Modules\mkBeneficiarios\Distritos';
      $cols = 'id,name';
      $r['Distritos'] = $this->getDatosDbCache($request, $modelo, $cols, ['send' => false],'Distritos');

      $modelo = 'App\Modules\mkBeneficiarios\Entidades';
      $cols = 'id,name';
      $r['Entidades'] = $this->getDatosDbCache($request, $modelo, $cols, ['send' => false],'Entidades');

      $modelo = 'App\Modules\mkBeneficiarios\Dptos';
      $cols = 'id,name';
      $r['Dptos'] = $this->getDatosDbCache($request, $modelo, $cols, ['send' => false],'Dptos');

      $modelo = 'App\Modules\mkBeneficiarios\Zonas';
      $cols = 'id,name';
      $r['Zonas'] = $this->getDatosDbCache($request, $modelo, $cols, ['send' => false],'Zonas');

      $modelo = 'App\Modules\mkBeneficiarios\Descoms';
      $cols = 'id,name';
      $r['Descoms'] = $this->getDatosDbCache($request, $modelo, $cols, ['send' => false],'Descoms');

      $modelo = 'App\Modules\mkBeneficiarios\Epsas';
      $cols = 'id,name';
      $r['Epsas'] = $this->getDatosDbCache($request, $modelo, $cols, ['send' => false],'Epsas');

      $modelo = 'App\Modules\mkBeneficiarios\Tipo_banos';
      $cols = 'id,name';
      $r['Tipo_banos'] = $this->getDatosDbCache($request, $modelo, $cols, ['send' => false],'Tipo_banos');

      $modelo = 'App\Modules\mkBeneficiarios\Doc_firmados';
      $cols = 'id,name';
      $r['Doc_firmados'] = $this->getDatosDbCache($request, $modelo, $cols, ['send' => false],'Doc_firmados');

      $modelo = 'App\Modules\mkBeneficiarios\Info_metodos';
      $cols = 'id,name';
      $r['Info_metodos'] = $this->getDatosDbCache($request, $modelo, $cols, ['send' => false],'Info_metodos');

      $modelo = 'App\Modules\mkBeneficiarios\Parentescos';
      $cols = 'id,name';
      $r['Parentescos'] = $this->getDatosDbCache($request, $modelo, $cols, ['send' => false],'Parentescos');

      $modelo = 'App\Modules\mkBeneficiarios\Est_civiles';
      $cols = 'id,name';
      $r['Est_civiles'] = $this->getDatosDbCache($request, $modelo, $cols, ['send' => false],'Est_civiles');

      $modelo = 'App\Modules\mkBeneficiarios\Niv_educativos';
      $cols = 'id,name';
      $r['Niv_educativos'] = $this->getDatosDbCache($request, $modelo, $cols, ['send' => false],'Niv_educativos');

      $modelo = 'App\Modules\mkBeneficiarios\Ocupaciones';
      $cols = 'id,name';
      $r['Ocupaciones'] = $this->getDatosDbCache($request, $modelo, $cols, ['send' => false],'Ocupaciones');
         $result = [$r];
        return Mk_db::sendData(count($result), $result, '');
    }


}
