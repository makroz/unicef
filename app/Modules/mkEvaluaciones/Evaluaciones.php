<?php

namespace App\Modules\mkEvaluaciones;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Evaluaciones extends Model
{
    use Mk_ia_model;

    protected $fillable = ['obs','ruteos_id','beneficiarios_id','usuarios_id','estado','status'];
    protected $attributes = ['status' => 1,'estado'=> 0];
    public $_customFields = ["ST_X(coord) as lat, ST_Y(coord) as lng"];
    
    // public $_cachedRelations = [
    //     ['App\Modules\mkE\rutas','rutas_id']
    // ];
    //public $_withRelations = ['ruteo','beneficiarios','usuario'];
    //public $_pivot2Array = ['beneficiarios'];
    //protected $cascadeDeletes = ['permisos','grupos'];


    public function getRules($request){
        return [
            'status' => 'in:0,1'
        ];
    }
    public function ruteo()
    {
        return $this->hasOne('App\Modules\mkRutas\Ruteos');
    }
    public function beneficiario()
    {
        return $this->hasOne('App\Modules\mkBeneficiarios\Beneficiarios');
    }
    public function usuario()
    {
        return $this->hasOne('App\Modules\mkUsuarios\Usuarios');
    }

}