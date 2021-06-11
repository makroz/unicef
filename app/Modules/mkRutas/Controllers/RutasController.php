<?php

namespace App\Modules\mkRutas\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Modules\mkBase\Mk_helpers\Mk_db;
use App\Modules\mkBase\Mk_helpers\Mk_debug;

class RutasController extends Controller
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
        if ($request->has('beneficiarios')){
            $ids=join(',',$request->beneficiarios);
            if ($action>0){
                DB::update('update beneficiarios set rutas_id = null, orden=0 where rutas_id = ?', [$id]);
            }
            if (!empty($ids)) {
                $orden=0;
                foreach ($request->beneficiarios as $item) {
                    DB::update("update beneficiarios set rutas_id = ?, orden={$orden} where id=$item", [$id]);
                    $orden++;
                }
            }
            $this->clearCache('beneficiarios');
        }
    }
    // public function monitores(Request $request)
    // { 
    //     $modelo = 'App\Modules\mkUsuarios\Usuarios';
    //     $cols='id,name';
    //     $filtros=[
    //         ['roles_slug','=','monitor',],
    //         ['status','<>',0]
    //     ];
    //     return $this->getDatosDbCache($request,$modelo,$cols,['filtros'=>$filtros,'send' => true]);
        
    // }

    public function beneficiarios(Request $request,$id)
    { 
        $modelo = 'App\Modules\mkBeneficiarios\Beneficiarios';
        $cols='id,name';
        $filtros=[
            ['OR',['rutas_id','=',null],['rutas_id','=',$id]],
            ['status','<>',0]
        ];
        return $this->getDatosDbCache($request,$modelo,$cols,['filtros'=>$filtros,'_customFields'=>1,'sortBy'=>'orden','send' => true]);
        
    }
    
    
}
