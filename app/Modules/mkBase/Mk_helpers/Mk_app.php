<?php
namespace App\Modules\mkBase\Mk_helpers;

use Illuminate\Support\Facades\Route;

class Mk_app
{
    public static function loadRoutes($path = '')
    {
        //echo "Dir0:".$path;
        if (empty($path)) {
            $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Modules' ;
        }
        //echo "<br>Dir:".$path;
        //echo "Separador :".DIRECTORY_SEPARATOR.'<br>';
        if (is_dir($path)) {
            if ($dh = opendir($path)) {
                while (($file = readdir($dh)) !== false) {
                    $path=$path ;
                    if (is_dir($path . DIRECTORY_SEPARATOR. $file) && $file != "." && $file != "..") {
                        $routeFile = $path . DIRECTORY_SEPARATOR. $file . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'route.php';
                        if (file_exists($routeFile)) {
                            require $routeFile;
                        }
                    }
                }
                closedir($dh);
            }
        } else {
            echo "No es ruta valida";
        }
    }
    public static function setRuta($modulo, $extras = [], $nameSpace = '')
    {
        if (empty($extras['prefix'])) {
            $extras['prefix'] = $modulo;
        }
        $prefix = $extras['prefix'];

        if (empty($extras['extras'])) {
            $extras['extras'] = [];
        }
        //echo 'ns0:'.$nameSpace;
        if (!empty($nameSpace)) {
            $nameSpace = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $nameSpace);
            if (substr($nameSpace, -1) != DIRECTORY_SEPARATOR) {
                $nameSpace .= DIRECTORY_SEPARATOR;
            }
            $nameSpace = str_replace(DIRECTORY_SEPARATOR,'\\',$nameSpace);
        }
        //echo '<br>ns1:'.$nameSpace;
        $modulo = str_replace(DIRECTORY_SEPARATOR,'\\',$modulo);
        $rutasExtras = $extras['extras'];
        //echo "<br>route: ".$nameSpace . $modulo . 'Controller<br>';
        Route::resource($prefix, $nameSpace . $modulo . 'Controller');
        Route::group(['prefix' => $prefix], function () use ($modulo, $rutasExtras, $nameSpace) {
            Route::post('/delete', $nameSpace . $modulo . 'Controller@destroy');
            Route::post('/restore', $nameSpace . $modulo . 'Controller@restore');
            Route::post('/setStatus', $nameSpace . $modulo . 'Controller@setStatus');
            foreach ($rutasExtras as $key => $lruta) {
                $method = $lruta[0];
                Route::{$method}($lruta[1], $nameSpace . $modulo . 'Controller@' . $lruta[2]);
            }
        });
    }

    public static function loadControllers($path = '')
    {
        //echo "Dir0:".DIRECTORY_SEPARATOR.':'.$path.'<br>';
        $path=str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        if (empty($path)) {
            return false;
        }
        //echo 'Dir1:'.$path.'<br>';
        $nameSpace = explode(DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR, $path);
        $nameSpace = array_pop($nameSpace);
        $nameSpace = 'App' . DIRECTORY_SEPARATOR . self::getNameSpace($nameSpace, 2) . DIRECTORY_SEPARATOR . 'Controllers';
        $nameSpace = str_replace(DIRECTORY_SEPARATOR,'\\',$nameSpace);
        $path = self::getNameSpace($path, 2) . DIRECTORY_SEPARATOR . 'Controllers';
        $path=str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        //echo "Dir2:".$path.'<br>'.'ns:'.$nameSpace.'<br>';
        if (is_dir($path)) {
            foreach (glob($path . DIRECTORY_SEPARATOR . "*Controller.php") as $filename) {
                $filename = pathinfo($filename, PATHINFO_FILENAME);
                $filename = str_replace('Controller', '', $filename);
                //echo 'Ruta:'.$filename.'<br>';
                Mk_app::setRuta($filename, [], $nameSpace);
            }
            return $nameSpace;
        } else {
            echo "No es ruta valida2";
        }
        return false;
    }
    public static function getNameSpace($name, $desnivel = 0)
    {
        $name=str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $name);
        $nodos = explode(DIRECTORY_SEPARATOR, $name);
        //print_r($nodos);echo '<br>';
        while ($desnivel > 0) {
            array_pop($nodos);
            //print_r($nodos);echo '<br>';
            $desnivel--;
        }
        //echo 'Ultimo:';
        //print_r($nodos);echo '<br>';
        return join('\\', $nodos);
    }
    public static function getNameModel($clase)
    {
        //$clase=str_replace('/', '\\', $clase);
        $nameSpace = explode('\\', get_class($clase));
        $model = array_pop($nameSpace);
        array_pop($nameSpace);
        $nameSpace = join('\\', $nameSpace);
        $model = explode('Controller', $model);
        $model = $model[0];
        //print_r($nodos);
        return $nameSpace . '\\' . $model;
    }

    public static function getNSModules()
    {
        return "App\\Modules\\";
    }

    public static function recurse_copy_dir(string $src, string $dest): int
    {
        $count = 0;
        $src = rtrim($dest, "/\\") . "/";
        $dest = rtrim($dest, "/\\") . "/";
        $list = dir($src);
        @mkdir($dest);
        while (($file = $list->read()) !== false) {
            if ($file === "." || $file === "..") {
                continue;
            }

            if (is_file($src . $file)) {
                copy($src . $file, $dest . $file);
                $count++;
            } elseif (is_dir($src . $file)) {
                $count += self::recurse_copy_dir($src . $file, $dest . $file);
            }
        }
        return $count;
    }

}
//TODO: hacer que las rutas base y directorios sean configradas en Constantes y desde el ENV
