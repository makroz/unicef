<?php

namespace App\Modules\mkBeneficiarios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Familiares extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','name','edad','genero','status','beneficiario_id','parentesco_id','est_civil_id','niv_educativo_id','ocupacion_id'];
    protected $attributes = ['status' => '1'];
    

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'name' => 'required_with:name',
            'edad' => 'numeric',
            'status' => 'in:0,1|required_with:status',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'beneficiario_id' => 'numeric|required_with:beneficiario_id',
            'parentesco_id' => 'numeric|required_with:parentesco_id',
            'est_civil_id' => 'numeric|required_with:est_civil_id',
            'niv_educativo_id' => 'numeric|required_with:niv_educativo_id',
            'ocupacion_id' => 'numeric|required_with:ocupacion_id'
        ];
    }


}