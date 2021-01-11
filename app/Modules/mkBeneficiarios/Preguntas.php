<?php

namespace App\Modules\mkPreguntas;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Preguntas extends Model
{
    use Mk_ia_model;

    protected $fillable = ['pregunta','tipo','orden','categ_id','status'];
    protected $attributes = ['status' => 1];

    //public $_withRelations = ['categ']; //TODO: revisar porque cuando se le pone campos devuelve null

    public function getRules($request){
        return [
            'pregunta' => 'required_with:pregunta',
            'tipo' => 'required_with:tipo',
            'orden' => 'required_with:orden',
            'status' => 'in:0,1'
        ];
    }
    public function categ()
    {
        return $this->hasOne('App\Modules\mkPreguntas\Categ');
    }


}
