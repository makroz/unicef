<?php

namespace App\Modules\mkEmpresas\Controllers;

use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;
use App\Modules\mkBase\Mk_helpers\Mk_db;

class EmpresasController extends Controller
{
    use Mk_ia_db;
    public $_autorizar='';
    protected $__modelo='';
    public function __construct(Request $request)
    {
        parent::__construct($request);
        return true;
    }
}
