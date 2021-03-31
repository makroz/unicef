<?php

namespace App\Modules\mkUsuarios\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use App\Modules\mkBase\Controller;
use App\Modules\mkBase\Mk_Helpers\Mk_db;
use App\Modules\mkBase\Mk_helpers\Mk_debug;
use App\Modules\mkBase\Mk_Helpers\Mk_Auth\Mk_Auth;

class UsuariosController extends Controller
{
    use Mk_ia_db;
    public $_autorizar='';
    //public $_validators=[];


    protected $__modelo='';
    public function __construct(Request $request)
    {
        parent::__construct($request);
        return true;
    }

    public function beforeSave(Request $request, $modelo, $id=0)
    {
        if ($id==0){
            $modelo->pass= sha1($modelo->pass);
        }
    }

    public function afterSave(Request $request, $modelo, $error=0, $id=0)
    {

        Mk_debug::msgApi(['afterSave:',$request,  $error, $id]);
        if ($error>=0) {
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
            }
            if (isset($request->paramsExtra['grupos'])) {
                if ($id>0) {//modificar
                    $modelo->grupos()->detach();
                }
                foreach ($request->paramsExtra['grupos'] as $key => $value) {
                    if ($value>0) {
                        $modelo->grupos()->attach($value);
                    }
                }
            }
        }
    }
    

    public function permisos(Request $request, $usuarios_id)
    {
        $model='App\Modules\mkUsuarios\Permisos';
        $permisos = new $model();
        $datos= $permisos->select('permisos.id', 'permisos.name', 'usuarios_permisos.valor', 'permisos.slug')
        ->leftJoin('usuarios_permisos', function ($join) use ($usuarios_id) {
            $join->on('permisos.id', '=', 'permisos_id')
                 ->where('usuarios_id', '=', $usuarios_id);
        })->orderBy('permisos.name')->get();

        if ($request->ajax()) {
            return  $datos;
        } else {
            $d=$datos->toArray();
            $ok=count($d);
            $d=$this->isCachedFront($d);
            return Mk_db::sendData($ok, $d, $this->permisosGrupos($request, 0, false,2));
        }
    }

    public function permisosGrupos(Request $request, $usuarios_id=0, $debug=true,$lista=1)
    {
        $grupos_id=$request->grupos;
        if (!is_array($grupos_id)) {
            $grupos_id=[];
        }
        $model='App\Modules\mkUsuarios\Permisos';
        $permisos = new $model();
        $datos= $permisos->select('permisos.id', \Illuminate\Support\Facades\DB::raw('BIT_OR(grupos_permisos.valor) as valor'))->leftJoin('grupos_permisos', function ($join) use ($grupos_id) {
            $join->on('permisos.id', '=', 'permisos_id')
                 ->wherein('grupos_id', $grupos_id);
        })->groupBy('permisos.id')->orderBy('permisos.name')->get();

        if ($request->ajax()) {
            return  $datos;
        } else {
            $d=$datos->toArray();
            $ok=count($d);
            $d=$this->isCachedFront($d,$lista);//paso 1 para cachear el front
            return Mk_db::sendData($ok, $d, '', $debug);
        }
    }


    public function login(Request $request)
    {

        $Auth=Mk_auth::get();
        $msg='';
        $user=$Auth->login( $request->username, $request->password);
        if (empty($user)) {
            $r=_errorLogin;
            $msg='Login Erroneo';
            $user=[];
        } else {
            $r=$user['id'];
            //print_r($d);
        }
        return Mk_db::sendData($r, $user, $msg);
    }
}
