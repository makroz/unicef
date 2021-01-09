<?php

namespace App\Modules\mkEmpresas;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Sucursales extends Model
{
    use Mk_ia_model;

    protected $fillable = ['name','dir','email','tel','status','empresas_id'];
    protected $attributes = ['status' => 1];

     //public $_withRelations = ['empleados'];
     //public $_pivot2Array = ['empresas'];
     protected $cascadeDeletes = ['empleados'];//TODO: quitar esto despues ya que no deberia borrarse el empleado es solo por preubas

    public function getRules($request){
        return [
            'name' => 'required_with:name',
            'email' => 'required_with:email|email|unique:sucursales,email,'.$request->input('id'),
            'status' => 'in:0,1'
        ];
    }

    public function empresas()
    {
        return $this->belongsTo('App\Modules\mkEmpresas\Empresas');
    }
    public function empleados()
    {
        return $this->hasMany('App\Modules\mkEmpresas\Empleados');
    }

}
