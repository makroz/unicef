<?php

namespace App\Modules\mkIA\Controllers;

use Illuminate\Http\Request;
use App\Modules\mkBase\Mk_ia_db;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Modules\mkBase\Mk_helpers\Mk_db;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;
use Illuminate\Routing\Controller as BaseController;

class IAController extends BaseController
{

    public function tabs($n=1){
        $tab='    ';
        $tabs='';
        while ($n>0){
            $tabs=$tabs.$tab++;
            $n--;
        }
        return $tabs;
    }
    
     public function dirlist($path){
        $dir=[];
        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $path ;
        if (is_dir($path)) {
            if ($dh = opendir($path)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_dir($path . DIRECTORY_SEPARATOR. $file) && $file != "." && $file != ".." && $file != "mkBase" && $file != "mkIA") {
                        $dir[]=basename($path . DIRECTORY_SEPARATOR. $file);
                    }
                }
                closedir($dh);
            }
        } else {
            echo "No es ruta valida";
        }
        return $dir;
     }
    public function index(Request $request){
        $tablas=[];
        $lista=DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = 'unicef'");

        foreach($lista as $table){
            $t=[];
            $t['name']=$table->TABLE_NAME;
            $tabla=DB::select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '$table->TABLE_NAME'
            AND table_schema = 'unicef'");
            $t['cols']=$tabla;
            $tablas[]=$t;
        }

        $modules=$this->dirlist('Modules');
        $projects=$this->dirlist('..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'www');
        $modFront=$this->dirlist('..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'..'. DIRECTORY_SEPARATOR .'www'. DIRECTORY_SEPARATOR .'unicef-Front'. DIRECTORY_SEPARATOR .'pages');

        $result = [
            'tablas' => [
                'ok' => count($tablas),
                'data' => $tablas,
            ],
            'modulos' => [
                'ok' => count($modules),
                'data' => $modules,
            ],
            'proyectos' => [
                'ok' => count($projects),
                'data' => $projects,
            ],
            'modulosFront' => [
                'ok' => count($modFront),
                'data' => $modFront,
            ],
        ];

        return Mk_db::sendData(count($result), $result, '');
    }

    public function store(Request $request){
        // echo 'Hola mundo IA<hr>';
        $t=$request->tabla;
        $tablaT=$t['name'];

        //return Mk_db::sendData(1, $t, '');

        
        $moduloBack=$t['moduloB'];
        $moduloFront=$t['moduloF'];
        $modulo=$t['nameMod'];
        $modTit=$t['titMod'];
        $proyecto='unicef-Front';//escoger en el front luego
        $Clase=ucfirst($tablaT);
        $model = file_get_contents($_SERVER['DOCUMENT_ROOT'].'../app/Modules/mkIA/stubs/Model.php');
        $controler = file_get_contents($_SERVER['DOCUMENT_ROOT'].'../app/Modules/mkIA/stubs/Controller.php');
        $component = file_get_contents($_SERVER['DOCUMENT_ROOT'].'../app/Modules/mkIA/stubs/Component.vue');

        $fillable=[];
        $rules=[];
        $lista=$t['cols'];
        $campos = "";
        $formulario="";
        $attributes=[];


        foreach ($lista as $col){

            if (!empty($col['COLUMN_DEFAULT'])) {
                $attributes[]="'".$col['COLUMN_NAME']."' => '".$col['COLUMN_DEFAULT']."'";
            }
            if ($col['form']){
                $fillable[]="'".$col['COLUMN_NAME']."'";
                $lRules=[];
                foreach ($col['rulesB'] as $rul) {
                    if ($rul=='required'){
                        $lRules[]="required_with:".$col['COLUMN_NAME'];
                    }
                    if ($rul=='status'){
                            $lRules[]="in:0,1";
                    }
                }
                if (count($lRules)>0){
                    $rules[]="'".$col['COLUMN_NAME']."' => '".join(';',$rules)."'";
                }
                
                //Formulario
                $lRulesF=[];
                foreach ($col['rulesF'] as $rulF) {
                    if ($rulF=='required'){
                        $lRulesF[]="rules.required";
                    }
                }
                $rulF='';
                if (count($lRulesF)>0){
                    $rulF=":rules='[".join(',',$lRulesF)."]'";
                }

                if ($col['typeF']=='alfa'){
                    $formulario=$formulario.
                    "<v-text-field
                    label='".$col['lForm']."'
                    v-model='item.".$col['COLUMN_NAME']."'
                    $rulF
                    validate-on-blur
                    :readonly=\"accion == 'show'\"
                  ></v-text-field>";
                }
                //Formulario

            }
            $ancho='';
            if (!empty($col['ancho'])){
                $ancho="width: '".$col['ancho']."',";
            }
            $alineacion='';
            if (!empty($col['align'])){
                $lAlign['-1']='left';
                $lAlign['l']='left';
                $lAlign['r']='right';
                $lAlign['c']='center';
                $alineacion="align: '".$lAlign[$col['align']]."',";
            }
            if ($col['list'] && $col['COLUMN_NAME']!='status') {
                $campos=$campos."
                {
                    text: '".$col['lList']."',
                    value: '".$col['COLUMN_NAME']."',
                    $alineacion
                    $ancho
                    headers: true,
                    type: '".$col['typeF']."',
                    search: ".($col['search']?'true':'false').",
                },";
            }
        }
        if (count($attributes)>0){
            $attributes="protected \$attributes = [".join(',',$attributes)."];";
        }else{
            $attributes='';
        }

        //protected $attributes = ['status' => 1];
        $fillable=join(',',$fillable);
        $rules=join(",\r\n".$this->tabs(3),$rules);
        $model=str_replace(['{{**NameSpace**}}','{{**NameClass**}}','{{**Fillable**}}','{{**Attributes**}}','{{**Rules**}}'],[$moduloBack,$Clase,$fillable,$attributes,$rules],$model);
        $controler=str_replace(['{{**NameSpace**}}','{{**NameClass**}}'],[$moduloBack,$Clase],$controler);
        $component=str_replace(['{{**NameClass**}}','{{**Formulario**}}','{{**Lista**}}'],[$Clase,$formulario,$campos],$component);


        //echo '<br>MEnu <hr>';
        $menu = file_get_contents($_SERVER['DOCUMENT_ROOT'].'../../unicef-Front/api/menu.js');
        if (strpos($menu, "/{$moduloFront}/{$modulo}/")===false) {
            $p=strpos($menu, "component: '$moduloFront'");
            $p=strpos($menu, "]", $p);
            $menu1=substr($menu, 0, $p-13);
            $menu2=substr($menu, $p+2);
            $menu=$menu1.
        ",\n".
        "                {\n".
        "                     name: '{$modulo}',\n".
        "                     title: '{$modTit}',\n".
        "                     href: '/{$moduloFront}/{$modulo}/'\n".
        "                 }\n".
        "             ]\n".
        $menu2;
            file_put_contents($_SERVER['DOCUMENT_ROOT'].'../../unicef-Front/api/menu.js', $menu);
        }
        file_put_contents($_SERVER['DOCUMENT_ROOT']."../../unicef-Front/pages/$moduloFront/$modulo.vue",$component);
        file_put_contents($_SERVER['DOCUMENT_ROOT']."../app/Modules/$moduloBack/$Clase.php",$model);
        file_put_contents($_SERVER['DOCUMENT_ROOT']."../app/Modules/$moduloBack/Controllers/$Clase"."Controller.php",$controler);
        $result = [
            'menu' => [
                'data' => $menu,
            ],
            'controler' => [
                'data' => $controler,
            ],
            'model' => [
                'data' => $model,
            ],
            'Componente' => [
                'data' => $component,
            ],
        ];

        //$menu = explode("\n", $menu);
        // dump($model);
        // dump($controler);
        // dump($menu);
        return Mk_db::sendData(4, $result, '');
    }
}