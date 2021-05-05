<?php

namespace App\Modules\mkUsuarios\Controllers;
use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Support\Facades\DB;
use  App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_helpers\Mk_db;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;

class GruposController extends Controller
{
    use Mk_ia_db;
    public $_autorizar='';

    protected $__modelo='';
    public function __construct(Request $request)
    {
        parent::__construct($request);
        return true;
    }

    public function afterSave(Request $request, $modelo, $action=0, $id=0)
    {
        if ($action>=0) {
            $_key=$modelo->getKeyName();
            $modelo->$_key=$id;
            
                if (isset($request->paramsExtra['permisos'])) {
                if ($id>0) {//modificar
                        $modelo->permisos()->detach();
                }
                foreach ($request->paramsExtra['permisos'] as $key => $value) {
                    if ($value['valor']>0) {
                        $modelo->permisos()->attach($value['id'], ['valor' => $value['valor']]);
                    }
                }
                $users=DB::select('select usuarios_id from usuarios_grupos  where grupos_id=?', [$id]);
                //print_r($users);
                foreach ($users as $user){
                    Mk_auth::forgetUser($user->usuarios_id);
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
