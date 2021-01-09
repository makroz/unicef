<?php

namespace App\Modules\mkHorarios\Controllers;

use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_Helpers\Mk_Auth\Mk_Auth;
use App\Modules\mkBase\Mk_Helpers\Mk_db;

class ParamsHorariosController extends Controller
{
    use Mk_ia_db;
    //public $_autorizar='';

    private $__modelo='App\Modules\mkHorarios\ParamsHorarios';

    public function __construct(Request $request)
    {
        $this->__init($request);
        return true;
    }
}
