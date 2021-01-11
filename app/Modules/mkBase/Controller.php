<?php

namespace App\Modules\mkBase;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Modules\mkBase\Mk_helpers\Mk_app;


class Controller extends BaseController
{
    use  DispatchesJobs, ValidatesRequests; //TODO::aumentar trait para injectar las Authorizaciones
    public function __construct(Request $request)
    {
        if ($this->__modelo==''){
            $this->__modelo=Mk_app::getNameModel($this);
        }
        $this->__init($request);
        return true;
    }
}
