<?php

namespace App\Modules\mkRutas;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Ruteos extends Model
{
    use Mk_ia_model;
    protected $fillable   = ['obs', 'rutas_id', 'usuarios_id', 'estado', 'status'];
    protected $attributes = ['estado' => 0, 'status' => 1];
    public $_customFields = ["ST_X(cabierto) as lat, ST_Y(cabierto) as lng"];

    //public $_withRelations = ['beneficiarios:rutas_id,id,name'];
    //public $_pivot2Array = ['beneficiarios'];
    //protected $cascadeDeletes = ['permisos','grupos'];

    public function getRules($request)
    {
        return [
            'rutas_id'    => 'integer',
            'usuarios_id' => 'integer',
            'status'      => 'in:0,1',
        ];
    }

    public function rutas()
    {
        return $this->hasOne('App\Modules\mkRutas\Rutas');
    }

    public function monitor()
    {
        return $this->hasOne('App\Modules\mkUsuarios\Usuarios');
    }

    public function beneficiarios()
    {
        return $this->hasOneThrough(
            'App\Modules\mkBeneficiarios\Beneficiarios',
            'App\Modules\mkRutas\Rutas'
        );
    }

}
