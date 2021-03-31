<?php

namespace App\Modules\mkRutas;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Ruteos extends Model
{
    use Mk_ia_model;
    protected $fillable = [
        'obs',
        'rutas_id',
        'usuarios_id',
        'estado',
        'status',
        'created_at',
    ];

    protected $attributes = [
        'estado' => 0,
        'status' => 1,
    ];
    public $_customFields = [
        "CONCAT(ST_X(gps_open),' ', ST_Y(gps_open)) as gps_open",
        "CONCAT(ST_X(gps_close),' ', ST_Y(gps_close)) as gps_close",
    ];

    public $_listTable = [
        'obs',
        'rutas_id',
        'usuarios_id',
        'estado',
        'status',
        'fec_cerrado',
        'open_id',
        'close_id',
        'created_at',
    ];

    public $_withRelations = ['evaluaciones', 'evaluaciones.beneficiarioCoord', 'evaluaciones.servicios', 'evaluaciones.respuestas'];
    //public $_pivot2Array = ['beneficiarios'];
    protected $cascadeDeletes = ['evaluaciones'];

    public function getRules($request)
    {
        return [
            'rutas_id' => 'integer',
            'usuarios_id' => 'integer',
            'status' => 'in:0,1',
        ];
    }

    public function rutas()
    {
        return $this->belongsTo('App\Modules\mkRutas\Rutas');
    }

    public function monitor()
    {
        return $this->hasOne('App\Modules\mkUsuarios\Usuarios');
    }

    public function evaluaciones()
    {
        return $this->hasMany('App\Modules\mkEvaluaciones\Evaluaciones')->selectRaw("*, CONCAT(ST_X(coord),' ', ST_Y(coord)) as coord");
    }

    public function servicios()
    {
        return $this->hasManyThrough('App\Modules\mkServicios\SolicitudServicios',
            'App\Modules\mkEvaluaciones\Evaluaciones')
            ->select([
                'solicitud_servicios.id',
                'cant',
                'servicios_id',
                'evaluaciones_id',
                'estado',
                'status',
            ]);
    }

    public function respuestas()
    {
        return $this->hasManyThrough('App\Modules\mkEvaluaciones\Respuestas',
            'App\Modules\mkEvaluaciones\Evaluaciones')
            ->select(['respuestas.id',
                'r_s',
                'preguntas_id',
            ]);
    }
}
