<?php

namespace App\Modules\mkUsuarios\Controllers;
use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Http\Request;

class RolesController extends controller
{
    use Mk_ia_db;

    protected $__modelo='';
    public function __construct(Request $request)
    {
        parent::__construct($request);
        return true;
    }
}
