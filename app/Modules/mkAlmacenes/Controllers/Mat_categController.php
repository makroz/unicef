<?php

namespace App\Modules\mkAlmacenes\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;

class Mat_categController extends Controller
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