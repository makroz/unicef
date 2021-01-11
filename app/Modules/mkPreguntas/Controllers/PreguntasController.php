<?php

namespace App\Modules\mkPreguntas\Controllers;

use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_Helpers\Mk_Auth\Mk_Auth;
use App\Modules\mkBase\Mk_Helpers\Mk_db;

class PreguntasController extends Controller
{
    use Mk_ia_db;
    //public $_autorizar='';

    private $__modelo='App\Modules\mkPreguntas\Preguntas';

    public function __construct(Request $request)
    {
        $this->__init($request);
        return true;
    }
}
