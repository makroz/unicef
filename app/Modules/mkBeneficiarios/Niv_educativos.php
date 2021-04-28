<?php

namespace App\Modules\mkBeneficiarios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Niv_educativos extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','name','status'];
    protected $attributes = ['status' => '1'];
    

    public function getRules($request){
        return [
            'id' => 'required_with:id',
            'name' => 'required_with:name',
            'status' => 'in:0,1;required_with:status'
        ];
    }

}