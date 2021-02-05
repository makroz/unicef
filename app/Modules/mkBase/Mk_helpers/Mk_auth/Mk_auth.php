<?php
namespace App\Modules\mkBase\Mk_helpers\Mk_auth;

use \Cache;
use \Request;
use App\Modules\mkBase\Mk_helpers\Mk_db;
use App\Modules\mkBase\Mk_helpers\Mk_debug;
use Illuminate\Support\Facades\DB;
use App\Modules\mkBase\Mk_helpers\Mk_singleton;

class Mk_auth
{
    private $newToken = false;
    private $msgError = null;
    private $codeError=-1000;
    private $user=null;
    private $_access=false;
    private $tipo='token';
    private $secret='sssawdsd8ws.6@';
    private $auth;
    private $blockData=false;
    private $coockie='c_sid';
    private $modelo='\App\Modules\mkUsuarios\Usuarios';
    private $timeCache=240;

    use Mk_singleton;

    public function __construct($modelo='')
    {
        if (!empty($modelo)){
            $this->modelo=$modelo;
        }
        define('__AUTH__', $this->tipo);
        define('__SECRET_KEY__', $this->secret);
        define('__AUTH_LEER__', 1);
        define('__AUTH_EDITAR__', 2);
        define('__AUTH_CREAR__', 4);
        define('__AUTH_BORRAR__', 8);
        define('_errorLogin',-1001);

        $this->auth  = FactoryAuth::getInstance();
        if (empty($user)) {
            $this->__init();
        } else {
            $this->setUser($user);
        }
        return true;
    }


    public function blockData($bloquear)
    {
        $this->blockData=$bloquear;
    }

    public function getBlockData()
    {
        return $this->blockData;
    }
    public static function get()
    {
        return Self::getInstance();
    }
    public function __init()
    {
        //$this->setToken($this->getToken(true));
        return true;
    }

    public function setUser($user=[],$campos=[])
    {
        $this->user=$user;
        if (empty($user)){
            $this->setToken(null);
        }else{
            $userToken=new \stdClass();
            $userToken->id=$user['id'];
            foreach ($campos as $key => $value) {
                $userToken->$value=$user[$value];
            }
            //$userToken->name=$user['name'];
            //$userToken->rol=$user['rol'];

            $token=$this->auth->autenticar($userToken);
            $this->setToken($token);
            Cache::put($token.'.user',$user,$this->timeCache);
        }

    }
    public function getUser()
    {
        return $user=$this->auth->usuario();
        //return $this->user;
    }

    public function setToken($token=false)
    {
        $this->newToken=$token;

    }

    public function getTokenCoockie(){
        $token=$this->getToken();
        if (empty($token)){
            @$token=$_COOKIE[$this->coockie];
            if (empty($token)) {
                $token=md5(date('ymd.').rand());
            }
        }else{
            $token=md5($token);
        }
        $_COOKIE[$this->coockie]=$token;
        //Mk_debug::msgApi($token);
        return $token;
    }

    public function getToken()
    {
        $token=$this->auth->getToken();
        return $token;
    }

    public function getNewToken()
    {
        $token=$this->newToken;
        $this->setToken(false);
        return $token;
    }
    public function isLogin()
    {

        try {
            return $this->auth->estaAutenticado();
        } catch (\Throwable $th) {
            Mk_debug::msgApi('No Logueado: '.$th->getMessage().' >>'.$th->getFile().':'.$th->getLine());
            return $this->setError(-1001,'No Logueado');
        }

    }

    public function checkLogin()
    {

        if (!$this->isLogin()) {
            $this->detener(-1001,'No Logueado');
        }
        return true;
    }

    public function permisosGruposMix($usuarios_id=0, $grupos_id=[], $debug=true)
    {
        $model='App\Modules\mkUsuarios\Permisos';
        $permisos = new $model();
        if (!empty($grupos_id)){
            $datos= $permisos->select('permisos.slug', DB::raw('BIT_OR(grupos_permisos.valor|usuarios_permisos.valor) as valor'))->leftJoin('usuarios_permisos', function ($join) use ($usuarios_id) {
                $join->on('permisos.id', '=', 'usuarios_permisos.permisos_id')
                     ->where('usuarios_id', '=', $usuarios_id);
            })->leftJoin('grupos_permisos', function ($join) use ($grupos_id) {
                $join->on('permisos.id', '=', 'grupos_permisos.permisos_id')
                     ->wherein('grupos_id', $grupos_id);
            })->groupBy('permisos.slug')->orderBy('permisos.name')->get();
        }else{
            $datos= $permisos->select('permisos.slug', DB::raw('BIT_OR(usuarios_permisos.valor) as valor'))->leftJoin('usuarios_permisos', function ($join) use ($usuarios_id) {
                $join->on('permisos.id', '=', 'usuarios_permisos.permisos_id')
                     ->where('usuarios_id', '=', $usuarios_id);
            })->groupBy('permisos.slug')->orderBy('permisos.name')->get();

        }

        $d=$datos->toArray();
        return Mk_db::sendData(count($d), $d, '', $debug);
    }

    public function login($username='',$password='',$id=0){
        try {
            $modelo=new $this->modelo();
            if (empty($id)) {
                $datos=$modelo->select(['usuarios.id','usuarios.name','usuarios.email','usuarios.status','roles.id as rol_id','roles.name as rol'])
                ->where('email', $username)->where('pass',  sha1($password))
            ->leftJoin('roles', 'roles.id', '=', 'roles_id')->with('grupos')->first();
            }else{
                $datos=$modelo->select(['usuarios.id','usuarios.name','usuarios.email','usuarios.status','roles.id as rol_id','roles.name as rol'])->where('usuarios.id', $id)
            ->leftJoin('roles', 'roles.id', '=', 'roles_id')->with('grupos')->first();
            }
        } catch (\Throwable $th) {
            $user=[];
            $this->detener(_errorLogin,'Error de Logueo');
        }
        if (!$datos) {
            $user=[];
        } else {
            $user=$datos->toArray();
            $permisos=$this->permisosGruposMix($user['id'], $user['grupos'], false);
            array_walk($permisos['data'], function(&$el,$clave){
                $el['slug']=strtolower($el['slug']);
            });
            $user['permisos']=array_column($permisos['data'],'valor','slug');
        }
        $this->setUser($user);
        return $user;
    }

    public function cors()
    {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: *");
            header('Access-Control-Allow-Credentials: true');
        }
        if (!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, OPTIONS');
            }

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }
            header("Access-Control-Max-Age", "3600");
            header("Access-Control-Allow-Headers", "Content-Type, Accept, X-Requested-With, remember-me");
        }
        return true;
    }

    public function canAccess($act='',$controller='')
    {
        if ($this->_access) return true;
        $router=Request::route()->getAction();
        //$router=explode($router['namespace'].'\\', $router['controller']);
        $router=basename($router['controller']);
       // print_r($router);
        $router=explode('Controller@', $router);
        if (empty($controller)){
            $controller=strtolower($router[0]);
        }
        if (empty($act)){
            $act=$router[1];
        }

        //if ($act=='login') return true;
        try {
            $user=$this->auth->usuario();
        } catch (\Throwable $th) {
            Mk_debug::msgApi('Error de Logueo:'.$th->getMessage().' >>'.$th->getFile().':'.$th->getLine());
            $this->setError(-1001,'Error de Logueo');
            return false;
        }

        if (empty($user)){
            return false;
        }

        $user=Cache::remember($this->getToken().'.user', $this->timeCache, function () use ($user) {
            Mk_debug::msgApi('entro');
            return $this->login(null,null,$user->id);
        });

        Mk_debug::msgApi($user);

        $act=strtolower($act);
        $controller=strtolower($controller);

        $actions=['leer'=>'1','ver'=>'1','show'=>'1','index'=>'1','1'=>'1',
                'editar'=>'2','edit'=>'2','modificar'=>'2','setstatus'=>'2','update'=>'2','2'=>'2',
                'crear'=>'4','add'=>'4','adicionar'=>'4','alta'=>'4','store'=>'4','4'=>'4',
                'del'=>'8','elim'=>'8','eliminar'=>'8','delete'=>'8','borrar'=>'8','destroy'=>'8','restore'=>'8','recycled'=>'8', 'restaurar'=>'8','recyclar'=>'8','8'=>'8'
                ];

        if ((empty(isset($user['permisos'][$controller])))||(empty(isset($actions[$act])))){
            @Mk_debug::msgApi("No Existen Permisos: controler($controller): {$user['permisos'][$controller]} - Action({$act}): {$actions[$act]})");
            return $this->setError(-1002,'No Existen Permisos') ;
        }

        if (!(($user['permisos'][$controller] & $actions[$act])==$actions[$act])){
            @Mk_debug::msgApi("Error de Acceso $controller/$act:"."!{$user['permisos'][$controller]} & {$actions[$act]} == ".(($user['permisos'][$controller] & $actions[$act])));
            return $this->setError(-1002,'Error de Acceso');
        }
        Mk_debug::msgApi(['Revisando Acceso:',"$controller/$act","{$user['permisos'][$controller]} & {$actions[$act]} == ".(($user['permisos'][$controller] & $actions[$act]))]);
        $this->_access=true;
        return true;
    }

    public function detener($code='',$msg='')
    {
        if (empty($code)){
            $code=$this->codeError;
        }
        if (empty($msg)){
            $msg=$this->msgError;
        }

        $this->cors();
        Mk_debug::error($msg);
        echo json_encode(Mk_db::sendData($code, null, $msg));
        die();
    }

    public function proteger($act='',$controller='')
    {
        if (!$this->canAccess($act,$controller)){
            $this->detener();
        }
        return true;
    }

    public function getMsgError()
    {
        return $this->msgError;
    }

    public function setError($cod,$msg)
    {
        $this->msgError=$msg;
        $this->codeError=$cod;
        return false;
    }
    public static function tokenPorCliente() {
        $aud = __SECRET_KEY__;

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $aud .= $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $aud .= $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $aud .= $_SERVER['REMOTE_ADDR'];
        }

        $aud .= @$_SERVER['HTTP_USER_AGENT'];
        $aud .= gethostname();

        return sha1($aud);
    }
}

interface IAuth
{
    public function autenticar($usuario);
    public function estaAutenticado();
    public function destruir();
    public function usuario();
    public function getToken();

}

class FactoryAuth
{
    public static function getInstance()
    {
        $rut=sprintf('App\Modules\mkBase\MK_helpers\Mk_auth\auth\%s\Auth', __AUTH__);
        return new $rut();
    }
}
