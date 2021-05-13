<?php

namespace App\Modules\mkServicios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class SolicitudServicios extends Model
{
    use Mk_ia_model;

    protected $fillable = ['cant', 'estado', 'status','created_at','created_by',
        'fecha_1', 'usuarios_id_1',
        'fecha_2', 'usuarios_id_2',
        'fecha_3', 'usuarios_id_3',
        'fecha_4', 'usuarios_id_4',
        'fecha_5', 'usuarios_id_5',
        'fecha_6', 'usuarios_id_6',
        'servicios_id', 'beneficiarios_id', 'evaluaciones_id',
    ];
    protected $attributes = ['status' => 1, 'estado' => 0];

    public function getRules($request)
    {
        return [
            'cant'   => 'required,integer,min:1',
            //'servicios_id' => 'array',
            //'beneficiarios_id' => 'required',
            'status' => 'in:0,1',
        ];
    }

}
