<?php

namespace App\Modules\mkRastreo;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;
use Carbon\Carbon;

class Rastreo extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','fecha','usuarios_id'];
    protected $table = 'rastreo';
    public $timestamps = false;
    public $_customFields = ["ST_X(coord) as lat, ST_Y(coord) as lng"];
    //public $_withRelations = ['usuario'];

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo('\App\Modules\mkUsuarios\Usuarios','usuarios_id')->select('id','name');
    }


}