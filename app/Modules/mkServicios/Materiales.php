<?php

namespace App\Modules\mkServicios;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Materiales extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','name','status','medida_id','stock','min_stock','costo','precio','lnota','mat_categ_id','ubicacion_id'];
    protected $attributes = ['status' => '1','costo' => '0.00','precio' => '0.00','lnota' => '1'];
    
    protected $hidden = ['pivot'];
    
    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'name' => 'required_with:name',
            'status' => 'in:0,1|required_with:status',
            'medida_id' => 'numeric|required_with:medida_id',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric',
            'stock' => 'numeric|required_with:stock',
            'min_stock' => 'numeric|required_with:min_stock',
            'costo' => 'numeric|required_with:costo',
            'precio' => 'numeric|required_with:precio',
            'lnota' => 'required_with:lnota',
            'mat_categ_id' => 'numeric',
            'ubicacion_id' => 'numeric'
        ];
    }


}