<?php

namespace App\Modules\mkBeneficiarios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Beneficiarios extends Model
{
    use Mk_ia_model;

    protected $fillable = ['name','epsa','autoriza', 'protec', 'dir', 'lat', 'long', 'nivel', 'rutas_id','distritos_id', 'entidades_id','status'];
    protected $attributes = ['status' => 1];
    public $_cachedRelations = [
        ['App\Modules\mkRutas\rutas','rutas_id']
    ];

    public function getRules($request){
        return [
            'name' => 'required_with:name',
            'distritos_id' => 'integer',
            'entidades_id' => 'integer',
            'rutas_id' => 'sometimes|integer',
            'status' => 'in:0,1'
        ];
    }
    
    public function entidades()
    {
        return $this->hasOne('\App\Modules\mkBeneficiarios\entidades');
    }

    public function distritos()
    {
        return $this->hasOne('\App\Modules\mkBeneficiarios\distritos');
    }

    public function ruta()
    {
        return $this->belongsTo('\App\Modules\mkRutas\rutas');
        
    }
}
