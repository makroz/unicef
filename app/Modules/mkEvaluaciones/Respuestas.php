<?php

namespace App\Modules\mkEvaluaciones;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Respuestas extends Model
{
    use Mk_ia_model;

    protected $fillable = ['r_s','preguntas_id','evaluaciones_id','status'];
    protected $attributes = ['status' => 1];

    //public $_withRelations = ['categ']; //TODO: revisar porque cuando se le pone campos devuelve null

    public function getRules($request){
        return [
            'r_s' => 'required_with:r_s',
            'evaluaciones_id' => 'required_with:evaluaciones_id|integer',
            'preguntas_id' => 'required_with:preguntas_id|integer',
            'status' => 'in:0,1'
        ];
    }

    


}
