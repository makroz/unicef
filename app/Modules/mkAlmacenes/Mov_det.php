<?php

namespace App\Modules\mkAlmacenes;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Mov_det extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','tipo','cant','obs','movimiento_id','material_id','status'];
    protected $attributes = ['status' => '1'];
    

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'tipo' => 'numeric|required_with:tipo',
            'cant' => 'numeric|required_with:cant',
            'movimiento_id' => 'numeric',
            'material_id' => 'numeric',
            'status' => 'in:0,1|required_with:status',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric'
        ];
    }


}