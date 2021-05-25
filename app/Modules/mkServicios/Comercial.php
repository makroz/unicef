<?php

namespace App\Modules\mkServicios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Comercial extends Model
{
    use Mk_ia_model;
    protected $table = "comercial";
    protected $fillable = ['id','created_by','created_at','estado'];
    
    

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric'
        ];
    }


}