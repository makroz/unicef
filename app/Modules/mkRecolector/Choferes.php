<?php

namespace App\Modules\mkRecolector;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Choferes extends Model
{
    use Mk_ia_model;

    protected $fillable   = ['id', 'name', 'ci', 'dir', 'tel', 'lic', 'fec_nac', 'status'];
    protected $table      = 'choferes';
    protected $attributes = ['lic' => 'P', 'status' => '1'];

    public function getRules($request)
    {
        return [
            'id'         => 'nullable|required_with:id|numeric',
            'name'       => 'required_with:name',
            'ci'         => 'required_with:ci',
            'lic'        => 'required_with:lic',
            'status'     => 'in:0,1|required_with:status',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric',
        ];
    }

}
