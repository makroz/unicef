<?php

namespace App\Modules\mkBeneficiarios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Entidades extends Model
{
    use Mk_ia_model;

    protected $fillable = ['name','status'];
    protected $attributes = ['status' => 1];

    public function getRules($request){
        return [
            'name' => 'required_with:name',
            'status' => 'in:0,1'
        ];
    }

}
