<?php
namespace App\Modules\mkBase\Mk_helpers;
use Illuminate\Support\Facades\Route;
class Mk_app
{
    public static function loadRoutes($path=''){
        if (empty($path)){
            $path=__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR;
            if (is_dir($path)) {
                if ($dh = opendir($path)) {
                   while (($file = readdir($dh)) !== false) {
                      if (is_dir($path . $file) && $file!="." && $file!=".."){
                          $routeFile=$path . $file.DIRECTORY_SEPARATOR.'Routes'.DIRECTORY_SEPARATOR.'route.php';
                         if (file_exists($routeFile)){
                            require ($routeFile);
                         }
                      }
                   }
                closedir($dh);
                }
             }else{
                 echo "No es ruta valida";
             }


        }


    }
    public static function setRuta($modulo, $extras=[],$namespace='')
    {
        if (empty($extras['prefix'])) {
            $extras['prefix']=$modulo;
        }
        $prefix=$extras['prefix'];

        if (empty($extras['extras'])) {
            $extras['extras']=[];
        }
        if (!empty($namespace)) {
            $namespace=str_replace(['/','\\'],DIRECTORY_SEPARATOR,$namespace);
            if (substr($namespace, -1)!=DIRECTORY_SEPARATOR){
                $namespace.=DIRECTORY_SEPARATOR;
            }
        }

        $rutasExtras=$extras['extras'];
        Route::resource($prefix, $namespace. $modulo.'Controller');
        Route::group(['prefix' => $prefix], function () use ($modulo,$rutasExtras,$namespace) {
            Route::post('/delete',$namespace. $modulo.'Controller@destroy');
            Route::post('/restore', $namespace.$modulo.'Controller@restore');
            Route::post('/setStatus', $namespace.$modulo.'Controller@setStatus');
            foreach ($rutasExtras as $key => $lruta) {
                $method=$lruta[0];
                Route::{$method}($lruta[1], $namespace.$modulo.'Controller@'.$lruta[2]);
            }
        });
     }
}

//join(array_slice(explode("\\", $class), 0, -1), "\\"); consigue el namespace de una clase
