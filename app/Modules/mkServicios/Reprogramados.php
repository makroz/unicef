<?php

namespace App\Modules\mkServicios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Reprogramados extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','obs','solicitud_servicio_id','recolector_id'];
    
    

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'obs' => 'required_with:obs',
            'solicitud_servicio_id' => 'numeric|required_with:solicitud_servicio_id',
            'recolector_id' => 'numeric|required_with:recolector_id',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric'
        ];
    }


}