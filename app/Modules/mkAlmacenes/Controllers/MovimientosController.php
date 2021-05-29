<?php

namespace App\Modules\mkAlmacenes\Controllers;

use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;
use App\Modules\mkBase\Mk_helpers\Mk_debug;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovimientosController extends Controller
{
    use Mk_ia_db;
    public $_autorizar  = '';
    protected $__modelo = '';
    public function __construct(Request $request)
    {
        parent::__construct($request);
        return true;
    }

    public function afterSave(Request $request, $modelo, $accion, $id = 0)
    {
        Mk_debug::msgApi(['afterSave',$request->tipo,$request->productos ]);
        $user_id = 0;
        if (Mk_auth::get()->isLogin()) {
            $user_id = Mk_auth::get()->getUser()->id;
        }
        $now = date('Y-m-d H:i:s');

        if ($request->tipo>=1 && $request->tipo<=3) {
            $values = [];
            $data   = [];
            foreach ($request->productos as $prod) {
  
                if (!empty($prod['material_id'])) {
                  $ing=!empty($prod['ingreso'])?$prod['ingreso']:0;
                  $egr=!empty($prod['egreso'])?$prod['egreso']:0;
    
                  $data[]   = $now;
                    $data[]   = $now;
                    $data[]   = $user_id;
                    $data[]   = $user_id;
                    $data[]   = $request->tipo;
                    $data[]   = $ing;
                    $data[]   = $egr;
                    $data[]   = $prod['material_id'];
                    $data[]   = $id;
                    $values[] = '(?,?,?,?,?,?,?,?,?)';
                    $cant = $ing - $egr;
                    DB::update('update materiales set stock=stock+? where id=?', [$cant, $prod['material_id']]);
                }
            }
            if (count($data) > 0) {
                $values = join(',', $values);
                DB::insert('insert into mov_det (created_at,updated_at,created_by,updated_by,tipo,ingreso,egreso,material_id,movimiento_id) values ' . $values, $data);
                $this->clearCache('mov_det');
                $this->clearCache('materiales');
            }
            return true;
        }
    }

}
