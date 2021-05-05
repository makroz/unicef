<?php

namespace App\Modules\mkCapacitaciones;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Sesion_grupales extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','fecha','contenido','hallazgos','status'];
    protected $attributes = ['status' => '1'];

    public $_withRelations = ['beneficiarios'];
    public $_pivot2Array = ['beneficiarios'];

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'fecha' => 'required_with:fecha',
            'contenido' => 'required_with:contenido',
            'hallazgos' => 'required_with:hallazgos',
            'status' => 'in:0,1|required_with:status',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric'
        ];
    }

    public function beneficiarios()
    {
        return $this->belongsToMany('\App\Modules\mkBeneficiarios\Beneficiarios','asistentes_grupales','sesion_grupal_id','beneficiario_id')->select('beneficiario_id as id');
        
    }


}