<?php

namespace App\Modules\mkEmpresas\Controllers;

use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_Helpers\Mk_Auth\Mk_Auth;
use App\Modules\mkBase\Mk_Helpers\Mk_db;

class EmpleadosController extends Controller
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