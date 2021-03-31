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
    public $_customFields = ["CONCAT(ST_X(evaluaciones.coord),' ', ST_Y(evaluaciones.coord)) as coord"];

    // public $_cachedRelations = [
    //     ['App\Modules\mkE\rutas','rutas_id']
    // ];
    public $_withRelations = [
        'respuestas',
        'beneficiario',
        'usuario',
        'ruteos:id,rutas_id',
        'servicios',
    ];
    //public $_pivot2Array = ['beneficiario:name'];
    protected $cascadeDeletes = ['respuestas'];
    public $_joins = ['ruteos' =>
        [
            'onSearch' => true,
            'type' => 'left',
            'fields' => ['ruteos.rutas_id'],
            'on' => ['ruteos.id', '=', 'evaluaciones.ruteos_id'],
        ],
        'beneficiarios' =>
        [
            'onSearch' => true,
            'type' => 'left',
            'fields' => ['beneficiarios.name'],
            'on' => ['beneficiarios.id', '=', 'evaluaciones.beneficiarios_id'],
        ],
    ];

    public function getRules($request)
    {
        return [
            'status' => 'in:0,1',
        ];
    }
    public function ruteos()
    {
        return $this->belongsTo('App\Modules\mkRutas\Ruteos', 'ruteos_id');
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

    public function beneficiarioCoord()
    {
        return $this->belongsTo(
            'App\Modules\mkBeneficiarios\beneficiarios',
            'beneficiarios_id'
        )->select([
            'id',
            'name',
            DB::raw("ST_X(coord) as lat, ST_Y(coord) as lng"),
        ]);
    }
    public function usuario()
    {
        return $this->belongsTo('App\Modules\mkUsuarios\Usuarios', 'usuarios_id');
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
