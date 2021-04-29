<?php

namespace App\Modules\{{**NameSpace**}};

use Illuminate\Database\Eloquent\Model;
use \App\Modules\mkBase\Mk_ia_model;

class {{**NameClass**}} extends Model
{
    use Mk_ia_model;

    protected $fillable = [{{**Fillable**}}];
    {{**Attributes**}}
    

    public function getRules($request){
        return [
            {{**Rules**}}
        ];
    }
{{**BackRel**}}

}