<?php

namespace App\Modules\mkPreguntas\Controllers;

use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Http\Request;
class CategController extends Controller

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
