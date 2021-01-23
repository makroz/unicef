<?php

namespace App\Modules\mkServicios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Servicios extends Model
{
    use Mk_ia_model;

    protected $fillable = ['name','cant','obs','status'];
    protected $attributes = ['status' => 1];

    public function getRules($request){
        return [
            'name' => 'required_with:name',
            'status' => 'in:0,1'
        ];
    }

}
