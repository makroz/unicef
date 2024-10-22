<?php

namespace App\Modules\mkEvaluaciones;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use \App\Modules\mkBase\Mk_ia_model;

class Evaluaciones extends Model
{
    use Mk_ia_model;

    protected $fillable = [
        'obs',
        'ruteos_id',
        'beneficiarios_id',
        'usuarios_id',
        'estado',
        'status',
    ];
    protected $attributes = [
        'status' => 1,
        'estado' => 0,
    ];
    public $_customFields = ["CONCAT(ST_X(coord),' ', ST_Y(coord)) as coord"];

    // public $_cachedRelations = [
    //     ['App\Modules\mkE\rutas','rutas_id']
    // ];
    public $_withRelations = [
        'respuestas',
        'beneficiario',
        'usuario',
        'ruteo:id,rutas_id',
        'servicios',
    ];
    //public $_pivot2Array = ['beneficiario:name'];
    protected $cascadeDeletes = ['respuestas'];

    public function getRules($request)
    {
        return [
            'status' => 'in:0,1',
        ];
    }
    public function ruteo()
    {
        return $this->belongsTo('App\Modules\mkRutas\Ruteos','ruteos_id');
    }
    public function beneficiario()
    {
        return $this->belongsTo(
            'App\Modules\mkBeneficiarios\beneficiarios',
            'beneficiarios_id'
        )->select([
            'id',
            'name',
            'epsa',
            'autoriza',
            'protec',
            'dir',
            'nivel',
            'rutas_id',
            'distritos_id',
            'entidades_id',
            'status',
            DB::raw("CONCAT(ST_X(coord),' ', ST_Y(coord)) as coord"),
        ]);
    }
    public function usuario()
    {
        return $this->belongsTo('App\Modules\mkUsuarios\Usuarios');
    }
    public function respuestas()
    {
        return $this->hasMany('App\Modules\mkEvaluaciones\Respuestas')
            ->select([
                'respuestas.id',
                'r_s',
                'preguntas_id',
                'evaluaciones_id',
                'respuestas.status',
            ]);
    }
    public function servicios()
    {
        return $this->hasMany('App\Modules\mkServicios\SolicitudServicios')
            ->select([
                'solicitud_servicios.id',
                'cant',
                'servicios_id',
                'evaluaciones_id',
                'solicitud_servicios.estado',
            ]);

    }

}
