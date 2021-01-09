<?php
namespace App\Modules\mkBase\Mk_helpers\Mk_auth\auth\session;

use App\Mk_helpers\Mk_auth\IAuth;

class Auth implements IAuth
{
    private $cookie = '__USUARIO__';
    private $tiempo = 1; // Expresado en horas

    public function autenticar($usuario)
    {
        if (!is_object($usuario)) {
            throw new Exception("Fallo autenticación");
        } elseif (empty($usuario->id)) {
            throw new Exception("Fallo autenticación");
        }

        $extraParaElToken = $usuario->id . $usuario->Usuario;

        setcookie(
            $this->cookie,
            json_encode(
                (object) [
                    'id' => $usuario->id,
                    'Usuario' => $usuario->Usuario,
                    'Token' => $this->token($extraParaElToken)
                ]
            ),
            time() + (3600 * $this->tiempo)
        );
    }

    public function estaAutenticado()
    {
        if (!empty($_COOKIE[$this->cookie])) {
            $json = json_decode($_COOKIE[$this->cookie]);

            if (empty($json)) {
                throw new Exception("No esta autenticado");
            }

            if (empty($json->Token)) {
                throw new Exception("No esta autenticado");
            }

            $extraParaElToken = $json->id . $json->Usuario;

            if ($json->Token !== $this->token($extraParaElToken)) {
                throw new Exception("No esta autenticado");
            }
        } else {
            throw new Exception("No esta autenticado");
        }
    }

    public function destruir()
    {
        $this->EstaAutenticado();

        unset($_COOKIE[$this->cookie]);
        setcookie($this->cookie, null, -1);
    }

    public function usuario()
    {
        $this->EstaAutenticado();

        return json_decode($_COOKIE[$this->cookie]);
    }

    private function token($extra)
    {
        return sha1(tokenPorCliente() . $extra);
    }
}
