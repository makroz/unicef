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
        'servicios_id', 'beneficiarios_id', 'evaluaciones_id','orden_servicios_id','obs','obs_verif','comercial_id',
    ];
    protected $attributes = ['status' => 1, 'estado' => 0];
    //public $_withRelations = ['materiales','qa'];
    public function getRules($request)
    {
        return [
            'cant'   => 'required,integer,min:1',
            //'servicios_id' => 'array',
            //'beneficiarios_id' => 'required',
            'status' => 'in:0,1',
        ];
    }

    public function materiales()
    {
        return $this->belongsToMany('\App\Modules\mkServicios\Materiales','materiales_usados','solicitud_servicio_id','material_id')->select(['materiales_usados.id as id_usado','material_id as id','materiales_usados.cant']);
        
    }
    public function qa()
    {
        return $this->belongsToMany('\App\Modules\mkServicios\Control_calidades','control_solicitudes','solicitud_servicio_id','control_calidad_id')->select(['control_solicitudes.id as id_control','control_calidad_id as id','control_solicitudes.puntos']);
        
    }

    public function servicios()
    {
        return $this->belongsTo('\App\Modules\mkServicios\Servicios','servicios_id');
        
    }

    public function nota()
    {
        return $this->belongsTo('\App\Modules\mkServicios\Orden_servicios','orden_servicios_id');
    }
    

}
