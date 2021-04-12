<?php
namespace App\Modules\mkBase\Mk_helpers\Mk_auth\auth\token;

use App\Modules\mkBase\Mk_helpers\Mk_auth\JWT\JWT;
use App\Modules\mkBase\Mk_helpers\Mk_auth\IAuth;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;

class Auth implements IAuth
{
    private $encrypt = array('HS256');
    private $tiempo = 1; // Horas

    // Crea un nuevo token guardando la información del usuario que hemos autenticado
    public function getToken(){
        if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return null;
        }
        return $token=$_SERVER['HTTP_AUTHORIZATION'];

    }
    public function autenticar($usuario)
    {
        if (!is_object($usuario)) {
            throw new \Exception("Fallo autenticación",-401);
        } elseif (empty($usuario->id)) {
            throw new \Exception("Fallo autenticación",-401);
        }

        $time = time();

        $token = array(
            'exp'  => $time + (3600*$this->tiempo),
            'aud'  => Mk_auth::tokenPorCliente(),
            'data' => $usuario
        );

        return JWT::encode($token, __SECRET_KEY__);
    }

    public function estaAutenticado()
    {

        $token=$this->getToken();
        if ($token==null) {
            throw new \Exception('No esta autenticado',-401);
            //return false;
        }
//        $token=$_SERVER['HTTP_AUTHORIZATION'];
        $decode = JWT::decode(
            $token,
            __SECRET_KEY__,
            $this->encrypt
        );
        if ($decode->aud !== Mk_auth::tokenPorCliente()) {
            throw new \Exception("No esta autenticado",-401);
            //return false;
        }
        return true;
    }

    public function usuario()
    {
        if ($this->estaAutenticado()){
            $token=$this->getToken();

            return JWT::decode(
                $token,
                __SECRET_KEY__,
                $this->encrypt
            )->data;

        }
        return null;
    }

    public function destruir()
    {
    }
}
