<?php

namespace App\Modules\mkBeneficiarios\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_Helpers\Mk_db;
use App\Modules\mkBase\Mk_helpers\Mk_debug;

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

    
}
