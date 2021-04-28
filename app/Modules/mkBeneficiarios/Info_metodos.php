<?php

namespace App\Modules\mkBeneficiarios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Info_metodos extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','name','orden','status'];
    protected $attributes = ['status' => '1'];
    

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'name' => 'required_with:name',
            'orden' => 'numeric|required_with:orden',
            'status' => 'in:0,1|required_with:status'
        ];
    }

}