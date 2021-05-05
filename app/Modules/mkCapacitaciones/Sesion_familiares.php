<?php

namespace App\Modules\mkCapacitaciones;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Sesion_familiares extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','fecha','contenido','hallazgos','alertas','acciones','nparticipantes','status','beneficiario_id'];
    protected $attributes = ['status' => '1'];
    public $_withRelations = ['apoyos.apoyos'];
    //public $_pivot2Array = ['apoyos:apoyos.apoyos'];

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'fecha' => 'required_with:fecha',
            'contenido' => 'required_with:contenido',
            'hallazgos' => 'required_with:hallazgos',
            'alertas' => 'required_with:alertas',
            'acciones' => 'required_with:acciones',
            'nparticipantes' => 'numeric|required_with:nparticipantes',
            'status' => 'in:0,1|required_with:status',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric',
            'beneficiario_id' => 'numeric|required_with:beneficiario_id'
        ];
    }

    public function apoyos()
    {
        return $this->hasOne('\App\Modules\mkBeneficiarios\Beneficiarios','id','beneficiario_id')->select('id');
        
    }


}