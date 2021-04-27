<?php

namespace App\Modules\mkBeneficiarios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Epsas extends Model
{
    use Mk_ia_model;

    protected $fillable = ['name'];
    {{**Attributes**}}
    

    public function getRules($request){
        return [
            'name' => ''
        ];
    }

}