<?php

namespace App\Modules\mkRutas;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Rutas extends Model
{
    use Mk_ia_model;

    protected $fillable = ['name','descrip','usuarios_id', 'status'];
    protected $attributes = ['status' => 1];

    public $_withRelations = ['beneficiarios:rutas_id,id'];
    public $_pivot2Array = ['beneficiarios'];
    //protected $cascadeDeletes = ['permisos','grupos'];
    public $_joins = ['beneficiarios' =>
    [
        'onSearch' => true,
        'type' => 'left',
        'groupBy' => 'rutas.id',
        'fields' => ['count(beneficiarios.id) as nBene'],
        'on' => ['beneficiarios.rutas_id', '=', 'rutas.id'],
    ],
];

    public function getRules($request){
        return [
            'name' => 'required_with:name',
            'usuarios_id' => 'integer',
            'status' => 'in:0,1'
        ];
    }

    public function monitor()
    {
        return $this->hasOne('App\Modules\mkUsuarios\Usuarios');
    }

    public function ruteos()
    {
        return $this->hasOne('App\Modules\mkRutas\Ruteos');
    }

    public function beneficiarios(){
        return $this->hasMany('App\Modules\mkBeneficiarios\Beneficiarios')->orderBy('orden');
    }

}
