<?php

namespace App\Modules\mkBeneficiarios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Municipios extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','name','status','dpto_id'];
    protected $attributes = ['status' => '1'];
    

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'name' => 'required_with:name',
            'status' => 'in:0,1|required_with:status',
            'dpto_id' => 'numeric|required_with:dpto_id'
        ];
    }

}