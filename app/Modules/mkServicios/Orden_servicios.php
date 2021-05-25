<?php

namespace App\Modules\mkServicios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Orden_servicios extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','ref','foto','obs','estado','recolector_id','forma_pago_id','beneficiario_id','created_at','status','comercial_id'];
    
    
    

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'ref' => 'required_with:ref',
            'foto' => 'required_with:foto',
            'obs' => 'required_with:obs',
            'estado' => 'required_with:estado',
            'recolector_id' => 'numeric|required_with:recolector_id',
            'forma_pago_id' => 'numeric|required_with:forma_pago_id',
            'beneficiario_id' => 'numeric|required_with:beneficiario_id',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric'
        ];
    }


}