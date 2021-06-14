<?php

namespace App\Modules\mkRecolector;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Check_diarios extends Model
{
    use Mk_ia_model;

    protected $hidden = ['pivot'];

    protected $fillable = ['id','fecha','salida','regreso','km_salida','km_regreso','obs','recolector_id','vehiculo_id','chofer_id','salida_id','llegada_id','status'];
    protected $table = 'check_diarios';
        protected $attributes = ['status' => '1'];
    
    public $_withRelations = ['materiales','eventos','checks'];
    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'fecha' => 'required_with:fecha',
            'salida' => 'required_with:salida',
            'km_salida' => 'numeric',
            'km_regreso' => 'numeric',
            'recolector_id' => 'numeric|required_with:recolector_id',
            'vehiculo_id' => 'numeric|required_with:vehiculo_id',
            'chofer_id' => 'numeric|required_with:chofer_id',
            'salida_id' => 'numeric|required_with:salida_id',
            'llegada_id' => 'numeric|required_with:llegada_id',
            'status' => 'in:0,1|required_with:status',
            'created_by' => 'numeric',
            'updated_by' => 'numeric',
            'deleted_by' => 'numeric'
        ];
    }

    public function materiales()
    {
      return $this->belongsToMany('\App\Modules\mkServicios\Materiales','check_materiales','diario_id','material_id')->select('material_id as id','cant');
    }
    public function eventos()
    {
      return $this->belongsToMany('\App\Modules\mkRecolector\Eventos','check_eventos','diario_id','evento_id')->select('evento_id as id','detalle');
    }
    public function checks()
    {
      return $this->belongsToMany('\App\Modules\mkRecolector\Checks','check_det','diario_id','check_id')->select('check_id as id','resp');
    }


}