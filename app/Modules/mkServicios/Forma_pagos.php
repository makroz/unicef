<?php

namespace App\Modules\mkServicios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Forma_pagos extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','name','status'];
    protected $attributes = ['status' => '1'];
    

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'name' => 'required_with:name',
            'status' => 'in:0,1|required_with:status',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric'
        ];
    }


}