<?php

namespace App\Modules\mkBeneficiarios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Beneficiarios extends Model
{
    use Mk_ia_model;

    protected $fillable = ['nom','epsa','autoriza', 'protec', 'dir', 'lat', 'long', 'nivel', 'distritos_id', 'entidades_id','status'];
    protected $attributes = ['status' => 1];


    public function getRules($request){
        return [
            'nom' => 'required_with:nom',
            'distritos_id' => 'integer',
            'entidades_id' => 'integer',
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

}
