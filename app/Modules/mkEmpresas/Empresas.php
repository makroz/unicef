<?php

namespace App\Modules\mkEmpresas;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Empresas extends Model
{
    use Mk_ia_model;

    protected $fillable = ['name','email','status'];
    protected $attributes = ['status' => 1];

     //public $_withRelations = ['sucursales']; //TODO: revisar porque cuando se le pone campos devuelve null
     //public $_pivot2Array = ['sucursales'];
    // protected $cascadeDeletes = ['permisos','grupos'];

    public function getRules($request){
        return [
            'name' => 'required_with:name',
            'email' => 'required_with:email|email|unique:empresas,email,'.$request->input('id'),
            'status' => 'in:0,1'
        ];
    }
    public function sucursales()
    {
        return $this->hasOne('App\Modules\mkEmpresas\Sucursales');
    }


}
