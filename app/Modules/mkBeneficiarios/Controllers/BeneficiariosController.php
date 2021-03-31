<?php

namespace App\Modules\mkBeneficiarios\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\mkBase\Mk_Helpers\Mk_db;
use App\Modules\mkBase\Mk_helpers\Mk_debug;

class BeneficiariosController extends Controller
{
    use Mk_ia_db;
    public $_autorizar='';
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

    
}
