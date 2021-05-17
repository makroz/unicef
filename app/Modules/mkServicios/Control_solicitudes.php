<?php

namespace App\Modules\mkServicios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Control_solicitudes extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','puntos','obs','solicitud_servicio_id','control_calidad_id'];
    
    

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'puntos' => 'numeric|required_with:puntos',
            'obs' => 'required_with:obs',
            'solicitud_servicio_id' => 'numeric|required_with:solicitud_servicio_id',
            'control_calidad_id' => 'numeric|required_with:control_calidad_id',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric'
        ];
    }


}