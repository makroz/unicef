<?php

namespace App\Modules\mkLogger;

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class Logger extends Model
{
    use Mk_ia_model;

    protected $fillable = ['id','type','message','usuario_id','ip','token','attrib','action','app_id','deletable','status','created_at'];
    protected $table = 'logger';
        protected $attributes = ['deletable' => '1','status' => '1','action' => '0'];
    

    public function getRules($request){
        return [
            'id' => 'nullable|required_with:id|numeric',
            'type' => 'numeric|required_with:type',
            'message' => 'required_with:message',
            'usuario_id' => 'numeric|required_with:usuario_id',
            'ip' => 'required_with:ip',
            'token' => 'required_with:token',
            'action' => 'required_with:action',
            'app_id' => 'required_with:app_id',
            'deletable' => 'required_with:deletable',
            'status' => 'in:0,1|required_with:status'
        ];
    }


}