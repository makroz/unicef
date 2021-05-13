<?php

namespace App\Modules\mkUsuarios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Usuarios extends Model
{
    use Mk_ia_model;

    protected $fillable = ['name','email','pass', 'roles_slug','status'];
    protected $attributes = ['status' => 1];
    protected $hidden = ['pass'];


    public $_withRelations = ['grupos'];
    public $_pivot2Array = ['grupos'];
    protected $cascadeDeletes = ['permisos','grupos'];

    public function getRules($request){
        return [
            'name' => 'required_with:name',
            'email' => 'required_with:email|email|unique:usuarios,email,'.$request->input('id'),
            'pass' => 'sometimes|required|min:8',
            'roles_slug' => 'required_with:roles_slug',
            'status' => 'in:0,1'
        ];
    }
    public function permisos()
    {
        return $this->belongsToMany('App\Modules\mkUsuarios\Permisos', 'usuarios_permisos')
        ->withPivot('valor');
    }

    public function grupos()
    {
        return $this->belongsToMany('App\Modules\mkUsuarios\Grupos', 'usuarios_grupos');

    }

    public function roles()
    {
        return $this->hasOne('App\Modules\mkUsuarios\Roles','roles_slug');
    }

}
