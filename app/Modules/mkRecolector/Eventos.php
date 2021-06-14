<?php

namespace App\Modules\mkRecolector;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Eventos extends Model
{
    use Mk_ia_model;

    protected $fillable   = ['id', 'name', 'orden', 'status'];
    protected $table      = 'eventos';
    protected $attributes = ['status' => '1'];
    protected $hidden = ['pivot'];
    
    public function getRules($request)
    {
        return [
            'id'         => 'nullable|required_with:id|numeric',
            'name'       => 'required_with:name',
            'orden'      => 'numeric|required_with:orden',
            'status'     => 'in:0,1|required_with:status',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric',
        ];
    }

}
