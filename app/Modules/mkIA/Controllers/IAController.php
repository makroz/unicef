<?php

namespace App\Modules\mkIA\Controllers;

use App\Modules\mkBase\Mk_helpers\Mk_db;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class IAController extends BaseController
{

    public function tabs($n = 1)
    {
        $tab  = '    ';
        $tabs = '';
        while ($n > 0) {
            $tabs = $tabs . $tab++;
            $n--;
        }
        return $tabs;
    }

    public function dirlist($path)
    {
        $dir  = [];
        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $path;
        if (is_dir($path)) {
            if ($dh = opendir($path)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_dir($path . DIRECTORY_SEPARATOR . $file) && $file != "." && $file != ".." && $file != "mkBase" && $file != "mkIA") {
                        $dir[] = basename($path . DIRECTORY_SEPARATOR . $file);
                    }
                }
                closedir($dh);
            }
        } else {
            echo "No es ruta valida";
        }
        return $dir;
    }
    public function index(Request $request)
    {
//        echo config('DB_DATABASE'); 
        $DB=env('DB_DATABASE');
        $tablas = [];
        $lista  = DB::select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = '$DB'");

        foreach ($lista as $table) {
            $t         = [];
            $t['name'] = $table->TABLE_NAME;
            $tabla     = DB::select("SELECT * FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name = '$table->TABLE_NAME'
            AND table_schema = '$DB'");
            $t['cols']  = $tabla;
            $relaciones = DB::select("select CONSTRAINT_NAME,COLUMN_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA='$DB'
             AND TABLE_NAME='$table->TABLE_NAME' and
             REFERENCED_TABLE_NAME IS NOT NULL");
            $t['rels'] = $relaciones;
            $tablas[]  = $t;
        }

        $modules  = $this->dirlist('Modules');
        $projects = $this->dirlist('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'www');
        $modFront = $this->dirlist('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'unicef-Front' . DIRECTORY_SEPARATOR . 'pages');

        $result = [
            'tablas'       => [
                'ok'   => count($tablas),
                'data' => $tablas,
            ],
            'modulos'      => [
                'ok'   => count($modules),
                'data' => $modules,
            ],
            'proyectos'    => [
                'ok'   => count($projects),
                'data' => $projects,
            ],
            'modulosFront' => [
                'ok'   => count($modFront),
                'data' => $modFront,
            ],
        ];

        return Mk_db::sendData(count($result), $result, '');
    }

    public function store(Request $request)
    {
        // echo 'Hola mundo IA<hr>';
        $t      = $request->tabla;
        $tablaT = $t['name'];

        //return Mk_db::sendData(1, $t, '');

        $moduloBack  = $t['moduloB'];
        $moduloFront = $t['moduloF'];
        //$modulo=$t['nameMod'];
        $modulo = $tablaT;

        $modTit    = $t['titMod'];
        $proyecto  = 'unicef-Front'; //escoger en el front luego
        $Clase     = ucfirst($tablaT);
        $model     = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '../app/Modules/mkIA/stubs/Model.php');
        $controler = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '../app/Modules/mkIA/stubs/Controller.php');
        $component = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '../app/Modules/mkIA/stubs/Component.vue');

        $backRel='';
        $fillable   = [];
        $rules      = [];
        $lista      = $t['cols'];
        $campos     = "";
        $formulario = "";
        $attributes = [];

        $relMounted = '';
        $dataRel    = '';
        foreach ($lista as $col) {
            /* relaciones  */
            $relCol = '';
            if (!empty($col['relTable'])) {
//              echo "0 rel table: ".$col['COLUMN_NAME'].' '.$col['relTable'];
                $refTable = ucfirst($col['relTable']);
                $colRel   = $col['relField'];
                $relMounted .= "
    this.l{$refTable} = await this.getListaBackend('{$refTable}', 'id,{$colRel}', '{$col['COLUMN_NAME']}')
";
                $dataRel .= "
      l{$refTable}: [],
";

                $relCol = "lista: this.l{$refTable},";
               // echo "1 rel table: $refTable";
            }
            /* relaciones  */

            if (!empty($col['COLUMN_DEFAULT'])) {
                $attributes[] = "'" . $col['COLUMN_NAME'] . "' => '" . $col['COLUMN_DEFAULT'] . "'";
            }

            $lRules = [];
            if ($col['COLUMN_KEY'] == 'PRI') {
                $lRules[] = "nullable";
            }
            foreach ($col['rulesB'] as $rul) {
                if ($rul == 'required') {
                    $lRules[] = "required_with:" . $col['COLUMN_NAME'];
                }
                if ($rul == 'num') {
                    $lRules[] = "numeric";
                }
                if ($rul == 'status') {
                    $lRules[] = "in:0,1";
                }
            }
            if (count($lRules) > 0) {
                $rules[] = "'" . $col['COLUMN_NAME'] . "' => '" . join('|', $lRules) . "'";
            }
            if ($col['form'] || $col['list']) {
                $fillable[] = "'" . $col['COLUMN_NAME'] . "'";
            }

            if ($col['form']) {

                //Formulario
                $formul   = '';
                $addInput = false;
                $lRulesF  = [];
                foreach ($col['rulesF'] as $rulF) {
                    if ($rulF == 'required') {
                        $lRulesF[] = "rules.required";
                    }
                    if ($rulF == 'num') {
                        $lRulesF[] = "rules.num";
                    }
                }
                $rulF = '';
                if (count($lRulesF) > 0) {
                    $rulF = ":rules='[" . join(',', $lRulesF) . "]'";
                }

                if ($col['typeF'] == 'text') {
                    $addInput = true;
                    $formul   = "
          <v-text-field
            label='" . $col['lForm'] . "'
            v-model='item." . $col['COLUMN_NAME'] . "'
            $rulF
            validate-on-blur
            :readonly=\"accion == 'show'\"
          >
          </v-text-field>
";
                }

                if ($col['typeF'] == 'num') {
                    $addInput = true;
                    $formul   = "
          <v-text-field
            type='number'
            label='" . $col['lForm'] . "'
            v-model='item." . $col['COLUMN_NAME'] . "'
            $rulF
            validate-on-blur
            :readonly=\"accion == 'show'\"
          >
          </v-text-field>
";
                }
                
                if ($col['typeF'] == 'selDB') {
                   echo "2 rel table ".$col['COLUMN_NAME'];
                   echo "2.1 rel table ".$refTable;
                    $addInput = true;
                    $formul   = "
          <v-select
            :items='l{$refTable}'
            item-text='{$colRel}'
            item-value='id'
            label='" . $col['lForm'] . "'
            v-model='item." . $col['COLUMN_NAME'] . "'
            $rulF
            validate-on-blur
            :readonly=\"accion == 'show'\"
          >
          </v-select>
";
                }

                if ($col['typeF'] == 'check') {
                  $addInput = true;
                  $formul   = "
      <v-checkbox
        v-model='item." . $col['COLUMN_NAME'] . "'
        value='1'
        label='" . $col['lForm'] . "'
        :readonly=\"accion == 'show'\"
      >
      </v-checkbox>
";
              }

                //
                if ($addInput) {
                    $formulario .= '
        <v-flex>' . $formul . '        </v-flex>';
                }
                //Formulario
            }
            $ancho = '';
            if (!empty($col['ancho'])) {
                $ancho = "width: '" . $col['ancho'] . "',";
            }
            $alineacion = '';
            if (!empty($col['align'])) {
                $lAlign['-1'] = 'left';
                $lAlign['l']  = 'left';
                $lAlign['r']  = 'right';
                $lAlign['c']  = 'center';
                $alineacion   = "align: '" . $lAlign[$col['align']] . "',";
            }
            if ($col['list'] && $col['COLUMN_NAME'] != 'status') {
                $tipo = $col['typeF'];
                if ($tipo == 'selDB') {
                    $tipo = 'num';
                }
                $campos = $campos . "
        {
          text: '" . $col['lList'] . "',
          value: '" . $col['COLUMN_NAME'] . "',
          $alineacion
          $ancho
          headers: true,
          type: '" . $tipo . "',
          search: " . ($col['search'] ? 'true' : 'false') . ",
          $relCol
        },";
            }
        }
        if (count($attributes) > 0) {
            $attributes = "protected \$attributes = [" . join(',', $attributes) . "];";
        } else {
            $attributes = '';
        }

        //protected $attributes = ['status' => 1];
        $fillable  = join(',', $fillable);
        $rules     = join(",\r\n" . $this->tabs(3), $rules);
        $model     = str_replace(['{{**NameSpace**}}', '{{**NameClass**}}', '{{**Fillable**}}', '{{**Attributes**}}', '{{**Rules**}}','{{**BackRel**}}'], [$moduloBack, $Clase, $fillable, $attributes, $rules,$backRel], $model);
        $controler = str_replace(['{{**NameSpace**}}', '{{**NameClass**}}'], [$moduloBack, $Clase], $controler);
        $component = str_replace(['{{**NameClass**}}', '{{**Formulario**}}', '{{**titModulo**}}',
            '{{**Lista**}}', '{{**dataRel**}}', '{{**RelMounted**}}'], [$Clase, $formulario, $modTit, $campos, $dataRel, $relMounted], $component);

        //echo '<br>MEnu <hr>';
        $menu = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '../../unicef-Front/api/menu.js');

        // strrpos()
        $p = strpos($menu, "name: '{$modulo}'");
        if ($p !== false) {
            //$p=strpos($menu, "name: '{$modulo}'");
            $menu1 = substr($menu, 0, $p);
            $p     = strrpos($menu1, ",\n");
            $menu1 = substr($menu, 0, $p);

            $menu2 = substr($menu, $p);
            $p     = strpos($menu2, "}\n");
            $menu2 = substr($menu2, $p + 2);
            $menu  = $menu1 . $menu2;
        }
//        if (strpos($menu, "/{$moduloFront}/{$modulo}/")===false) {
        $p     = strpos($menu, "component: '$moduloFront'");
        $p     = strpos($menu, "]", $p);
        $menu1 = substr($menu, 0, $p - 13);
        $menu2 = substr($menu, $p + 2);
        $menu  = $menu1 .
            ",\n" .
            "                {\n" .
            "                     name: '{$modulo}',\n" .
            "                     title: '{$modTit}',\n" .
            "                     href: '/{$moduloFront}/{$modulo}/'\n" .
            "                 }\n" .
            "             ]\n" .
            $menu2;
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '../../unicef-Front/api/menu.js', $menu);
        //}
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "../../unicef-Front/pages/$moduloFront/$modulo.vue", $component);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "../app/Modules/$moduloBack/$Clase.php", $model);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "../app/Modules/$moduloBack/Controllers/$Clase" . "Controller.php", $controler);
        $result = [
            'menu'       => [
                'data' => $menu,
            ],
            'controler'  => [
                'data' => $controler,
            ],
            'model'      => [
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
