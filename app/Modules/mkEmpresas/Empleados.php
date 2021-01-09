<?php

namespace App\Modules\mkEmpresas;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Empleados extends Model
{
    use Mk_ia_model;

    protected $fillable = ['name','ci','email','tel','dir','status','sucursales_id'];
    protected $attributes = ['status' => 1];
    protected $imageFile=1;
    public $_joins =[
        'sucursales'=>[
            'type'=>'left',
            'fields'=>['sucursales.name as suc_name'],
            'on'=>['sucursales.id','=','empleados.sucursales_id']
        ],
        'empresas'=>[
            'onSearch'=>true,
            'type'=>'left',
            'fields'=>['empresas.name as emp_name', 'empresas.id as empresas_id'],
            'on'=>['empresas.id','=','sucursales.empresas_id']
        ]
    ];
    //public $_withRelations = ['sucursales'];
    // public $_pivot2Array = ['grupos'];
    // protected $cascadeDeletes = ['permisos','grupos'];

    public function getRules($request)
    {
        return [
            'name' => 'required_with:name',
            'email' => 'required_with:email|email|unique:empleados,email,'.$request->input('id'),
            'status' => 'in:0,1'
        ];
    }
    public function sucursales()
    {
        return $this->belongsTo('App\Modules\mkEmpresas\Sucursales');//hasOne:el que no tiene el fk, y belongTo el que tiene el fk
    }
}
