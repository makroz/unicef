<?php

namespace App\Modules\mkPreguntas;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Categ extends Model
{
    use Mk_ia_model;

    protected $table = "categ";
    protected $fillable = ['name','orden','status'];
    protected $attributes = ['status' => 1];

    public function getRules($request){
        return [
            'name' => 'required_with:name',
            'orden' => 'integer',
            'status' => 'in:0,1'
        ];
    }

}
