<?php

namespace App\Modules\mkUsuarios\Controllers;
use  App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Mk_Helpers\Mk_db;
use Illuminate\Http\Request;

class GruposController extends Controller
{
    use Mk_ia_db;


    private $__modelo='\App\Modules\mkUsuarios\Grupos';

    public function __construct(Request $request)
    {
        $this->__init($request);
        return true;
    }


    public function afterSave(Request $request, $modelo, $error=0, $id=0)
    {
        if ($error>=0) {
            $modelo->id=$id;

                if (isset($request->paramsExtra['permisos'])) {
                if ($id>0) {//modificar
                        $modelo->permisos()->detach();
                }
                foreach ($request->paramsExtra['permisos'] as $key => $value) {
                    if ($value['valor']>0) {
                        $modelo->permisos()->attach($value['id'], ['valor' => $value['valor']]);
                    }
                }
            }
        }
    }

    public function permisos(Request $request, $grupos_id)
    {
        $model='App\Modules\mkUsuarios\Permisos';
        $permisos = new $model();
        $datos= $permisos->select('permisos.id', 'permisos.name', 'grupos_permisos.valor', 'permisos.slug')
        ->leftJoin('grupos_permisos', function ($join) use ($grupos_id) {
            $join->on('permisos.id', '=', 'permisos_id')
                 ->where('grupos_id', '=', $grupos_id);
        })->orderBy('permisos.name')->get();

        if ($request->ajax()) {
            return  $datos;
        } else {
            $d=$datos->toArray();
            $ok=count($d);
            $d=$this->isCachedFront($d);
            return Mk_db::sendData($ok, $d);
        }
    }
}
