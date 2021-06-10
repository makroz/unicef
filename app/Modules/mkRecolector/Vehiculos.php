<?php

namespace App\Modules\mkRecolector;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Vehiculos extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','placa','serie','modelo','color','capacidad','cilindradas','anio','marca_id','status'];
    protected $table = 'vehiculos';
        protected $attributes = ['status' => '1'];
    

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'placa' => 'required_with:placa',
            'marca_id' => 'numeric|required_with:marca_id',
            'status' => 'in:0,1|required_with:status',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric'
        ];
    }


}