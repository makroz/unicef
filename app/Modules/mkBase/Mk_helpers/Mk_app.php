<?php
namespace App\Modules\mkBase\Mk_helpers;
use Illuminate\Support\Facades\Route;
class Mk_app
{
    public static function loadRoutes($path=''){
        if (empty($path)){
            $path=__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR;
        }
            
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

     public static function loadControllers($path=''){
        if (empty($path)){
            return false;
        }
        $nameSpace=explode(DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR,$path);
        $nameSpace = array_pop($nameSpace);
        $nameSpace= 'App'.DIRECTORY_SEPARATOR.self::getNameSpace($nameSpace,2).DIRECTORY_SEPARATOR.'Controllers';
        $path=self::getNameSpace($path,2).DIRECTORY_SEPARATOR.'Controllers';
        //echo $path;

        if (is_dir($path)) {
            foreach (glob($path.DIRECTORY_SEPARATOR."*Controller.php") as $filename) {
                $filename=pathinfo($filename, PATHINFO_FILENAME);
                $filename=str_replace('Controller','',$filename);
                //echo $filename.'*';
                Mk_app::setRuta($filename,[],$nameSpace);
            }
            return $nameSpace;
        }else{
            echo "No es ruta valida";
        }
        return false;
    }
    public static function getNameSpace($name,$desnivel=0)
    {
        $nodos=explode('\\',$name);
        while ($desnivel>0){
            array_pop($nodos);
            $desnivel--;
        }
        return join('\\',$nodos);
    }
    public static function getNameModel($clase)
    {
        $nameSpace=explode('\\',get_class($clase));
        $model=array_pop($nameSpace);
        array_pop($nameSpace);
        $nameSpace=join('\\',$nameSpace);
        $model=explode('Controller',$model);
        $model=$model[0];
        //print_r($nodos);
        return $nameSpace.'\\'.$model;
    }

    public static function getNSModules()
    {
        return "App\Modules\\";
    }

    
    public static function recurse_copy_dir(string $src, string $dest) : int {
    $count = 0;
    $src = rtrim($dest, "/\\") . "/";
    $dest = rtrim($dest, "/\\") . "/";
    $list = dir($src);
    @mkdir($dest);
    while(($file = $list->read()) !== false) {
        if($file === "." || $file === "..") continue;
        if(is_file($src . $file)) {
            copy($src . $file, $dest . $file);
            $count++;
        } elseif(is_dir($src . $file)) {
            $count += recurse_copy_dir($src . $file, $dest . $file);
        }
    }
    return $count;
}
}
//TODO: hacer que las rutas base y directorios sean configradas en Constantes y desde el ENV