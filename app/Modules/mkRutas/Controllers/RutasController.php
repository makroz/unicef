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

    public function afterSave(Request $request, $modelo, $error=0, $id=0)
    {
        if ($request->has('beneficiarios')){
            $ids=join(',',$request->beneficiarios);
            if ($id>0){
                DB::update('update beneficiarios set rutas_id = null where rutas_id = ?', [$id]);
            }
            if (!empty($ids)){
                DB::update("update beneficiarios set rutas_id = ? where id in ($ids)", [$id]);
            }
            $this->clearCache('beneficiarios');
        }
    }
    public function monitores(Request $request)
    { 
        $modelo = 'App\Modules\mkUsuarios\Usuarios';
        $cols='id,name';
        $filtros=[
            ['roles_id','=','2',],
            ['status','<>',0]
        ];
        return $this->getDatosDbCache($request,$modelo,$cols,['filtros'=>$filtros]);
        
    }

    public function beneficiarios(Request $request,$id)
    { 
        $modelo = 'App\Modules\mkBeneficiarios\Beneficiarios';
        $cols='id,name';
        $filtros=[
            ['OR',['rutas_id','=',null],['rutas_id','=',$id]],
            ['status','<>',0]
        ];
        return $this->getDatosDbCache($request,$modelo,$cols,['filtros'=>$filtros,'_customFields'=>1]);
        
    }
    
    
}
