<?php

namespace App\Modules\mkEmpresas;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class ParamsHorarios extends Model
{
    use Mk_ia_model;

    protected $fillable = ['min_tol','he_ent','he_sal','min_sep','hr_defecto'];
    protected $attributes = ['status' => 1];

    public function getRules($request)
    {
        return [
            'status' => 'in:0,1'
        ];
    }
}
