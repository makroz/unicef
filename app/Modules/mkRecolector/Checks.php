<?php

namespace App\Modules\mkRecolector;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Checks extends Model
{
    use Mk_ia_model;

    protected $fillable   = ['id', 'name', 'orden', 'tipo', 'status', 'categ_id'];
    protected $table      = 'checks';
    protected $attributes = ['tipo' => 'c', 'status' => '1'];
    protected $hidden     = ['pivot'];

    public function getRules($request)
    {
        return [
            'id'         => 'nullable|required_with:id|numeric',
            'name'       => 'required_with:name',
            'orden'      => 'numeric|required_with:orden',
            'tipo'       => 'required_with:tipo',
            'status'     => 'in:0,1|required_with:status',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric',
            'categ_id'   => 'numeric|required_with:categ_id',
        ];
    }

}
