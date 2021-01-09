<?php

namespace App\Modules\mkUsuarios\Controllers;
use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Http\Request;

class PermisosController extends Controller
{
    use Mk_ia_db;

    private $__modelo='\App\Modules\mkUsuarios\Permisos';
    public function __construct(Request $request)
    {
        $this->__init($request);
        return true;
    }
}
