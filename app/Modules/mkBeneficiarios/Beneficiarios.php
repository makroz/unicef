<?php

namespace App\Modules\mkBeneficiarios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Beneficiarios extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','name','epsa','autoriza','protec','dir','nivel','status','distritos_id','entidades_id','rutas_id','manzano','lote','safsi','nfamilias','npersonas','c_gob_municipal','c_gob_municipal_p','c_ong','c_ong_p','c_familias','c_familias_p','c_otra','c_otra_p','dpto_id','municipio_id','zona_id','descom_id','epsa_id','tipo_bano_id'];
    protected $attributes = ['status' => '1','nfamilias' => '1','npersonas' => '1'];
    public $listable = ['name','epsa','autoriza', 'protec', 'dir','nivel', 'rutas_id','distritos_id','entidades_id','status'];

    public $_customFields = ["ST_X(coord) as lat, ST_Y(coord) as lng"];

    //public $_withRelations = ['evaluaciones:beneficiarios_id,id'];

    public $_cachedRelations = [
        ['App\Modules\mkRutas\rutas','rutas_id']
    ];

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'name' => 'required_with:name',
            'epsa' => 'numeric|required_with:epsa',
            'nivel' => 'required_with:nivel',
            'status' => 'in:0,1|required_with:status',
            'distritos_id' => 'numeric',
            'entidades_id' => 'numeric',
            'rutas_id' => 'numeric',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric',
            'nfamilias' => 'numeric|required_with:nfamilias',
            'npersonas' => 'numeric|required_with:npersonas',
            'dpto_id' => 'numeric',
            'municipio_id' => 'numeric',
            'zona_id' => 'numeric',
            'descom_id' => 'numeric',
            'epsa_id' => 'numeric',
            'tipo_bano_id' => 'numeric',
        ];
    }

    public function entidades()
    {
        return $this->hasOne('\App\Modules\mkBeneficiarios\Entidades');
    }

    public function distritos()
    {
        return $this->hasOne('\App\Modules\mkBeneficiarios\Distritos');
    }

    public function ruta()
    {
        return $this->belongsTo('\App\Modules\mkRutas\Rutas');
        
    }

    public function evaluaciones()
    {
        return $this->hasMany('\App\Modules\mkEvaluaciones\evaluaciones');
        
    }


}