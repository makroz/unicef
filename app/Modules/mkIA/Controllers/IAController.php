<?php

namespace App\Modules\mkIA\Controllers;

use App\Modules\mkBase\Mk_helpers\Mk_db;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;

class IAController extends BaseController
{

    public function copiaDir($dirOrigen, $dirDestino)
    {
        //Creo el directorio destino

        @mkdir($dirDestino, 0777, true);
        //abro el directorio origen

        if ($vcarga = opendir($dirOrigen)) {
            while ($file = readdir($vcarga)) //lo recorro enterito
            {
                if ($file != '.' && $file != '..') //quito el raiz y el padre
                {
                    //echo «<b>$file</b>»; //muestro el nombre del archivo
                    if (!is_dir($dirOrigen . DIRECTORY_SEPARATOR . $file)) //pregunto si no es directorio
                    {
                        if (copy($dirOrigen . DIRECTORY_SEPARATOR . $file, $dirDestino . DIRECTORY_SEPARATOR . $file)) //como no es directorio, copio de origen a destino
                        {
                            //echo » COPIADO!»;
                        } else {
                            //echo » ERROR!»;
                        }
                    } else {
                        //echo » — directorio — <br />»; //era directorio llamo a la función de nuevo con la nueva ubicación
                        $this->copiaDir($dirOrigen . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR, $dirDestino . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR);
                    }
                    //echo «<br />»;
                }
            }
            closedir($vcarga);
        }
    }
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
        // $menu = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '../../unicef-Front/api/menu.js');

        //   $menu = str_replace(['export default Menu', 'const Menu = ', "\n"], '', $menu);
        //   $menu = str_replace(['{', ",", ':', ',, {', "'"], ['{{', ',,', '::', ',{', '"'], $menu);
        //   $menu = preg_replace(['/\s+/', '/\s*(?=,)|\s*(?=:)|[,]\s+|[:]\s+|[{]\s+/', '/\{(.+):/Ui', '/\,([^\{].+):/Ui'], [' ', '', '{"$1":', ',"$1":'], $menu);
        //   $menu = json_decode($menu);
        //   dd($menu);
        //        echo config('DB_DATABASE');
        $DB     = env('DB_DATABASE');
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
        $modules  = ['Nuevo Modulo', ...$modules];
        $projects = $this->dirlist('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'www');
        $modFront = $this->dirlist('..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'unicef-Front' . DIRECTORY_SEPARATOR . 'pages');
        $modFront = ['Nuevo Modulo', ...$modFront];

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

    public function loadMod($path = '')
    {
        //echo "Dir0:".$path;
        if (empty($path)) {
            $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
            $path = realpath($path);
        }
        //echo "<br>Dir:".$path;
        //echo "Separador :".DIRECTORY_SEPARATOR.'<br>';
        $modulos = [];
        if (is_dir($path)) {
            if ($dh = opendir($path . DIRECTORY_SEPARATOR)) {
                while (($file = readdir($dh)) !== false) {
                    $path = $path;
                    if (is_dir($path . DIRECTORY_SEPARATOR . $file) && $file != "." && $file != "..") {

                        if ($dh1 = opendir($path . DIRECTORY_SEPARATOR . $file)) {
                            while (($file1 = readdir($dh1)) !== false) {
                                //$path=$path ;
                                if (!is_dir($path . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR . $file1) && $file != "." && $file != "..") {
                                    $ind              = explode('.', $file1);
                                    $modulos[$ind[0]] = $file;
                                }
                            }
                            closedir($dh1);
                        }
                    }
                }
                closedir($dh);
            }
        } else {
            $modulos = "No es ruta valida " . $path;
        }
        return $modulos;
    }

    public function store(Request $request)
    {
        // echo 'Hola mundo IA<hr>';
        $d      = DIRECTORY_SEPARATOR;
        $t      = $request->tabla;
        $tablaT = $t['name'];

        //return Mk_db::sendData(1, $t, '');

        $moduloBack  = $t['moduloB'];
        $moduloFront = $t['moduloF'];
        if ($moduloBack == 'Nuevo Modulo') {
            $moduloBack = $t['idModB'];
            $this->copiaDir(__DIR__ . $d . '..' . $d . '..' . $d . '..' . $d . 'Modules' . $d . 'mkIA' . $d . 'stubs' . $d . 'modulosB',
                __DIR__ . $d . '..' . $d . '..' . $d . '..' . $d . 'Modules' . $d . $moduloBack);

        }
        if ($moduloFront == 'Nuevo Modulo') {
            $moduloFront = $t['idModF'];
            @mkdir($_SERVER['DOCUMENT_ROOT'] . "../../unicef-Front/pages/$moduloFront", 0777, true);
        }

        //$modulo=$t['nameMod'];
        $modulo = $tablaT;

        $modTit    = $t['titMod'];
        $proyecto  = 'unicef-Front'; //escoger en el front luego
        $Clase     = ucfirst($tablaT);
        $model     = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '../app/Modules/mkIA/stubs/Model.stub');
        $controler = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '../app/Modules/mkIA/stubs/Controller.stub');
        $component = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '../app/Modules/mkIA/stubs/Component.stub');

        $dirModules = $this->loadMod($_SERVER['DOCUMENT_ROOT'] . '../app/Modules');
        //Mk_debug::msgApi(['listada comercial',$dirModules]);
        $backRel    = '';
        $fillable   = [];
        $rules      = [];
        $lista      = $t['cols'];
        $campos     = "";
        $formulario = "";
        $attributes = [];
        $rImport    = '';
        $rComponent = '';

        $relMounted = '';
        $dataRel    = '';
        foreach ($lista as $col) {
            /* relaciones  */
            $relCol = '';
            if (!empty($col['relTable'])) {
                $refTable = ucfirst($col['relTable']);
                $colRel   = $col['relField'];
                $relMounted .= "
                {mod: '{$refTable}',campos: 'id,{$colRel}',datos: { modulo: '{$dirModules[$refTable]}' },item: '{$col['COLUMN_NAME']}'},";
                $dataRel .= "
      l{$refTable}: [],";
                $relCol = "lista: 'l{$refTable}',";
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
            $lOptions = '';
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

                if ($col['typeF'] == 'textarea') {
                    $addInput = true;
                    $formul   = "
      <v-textarea
        label='" . $col['lForm'] . "'
        v-model='item." . $col['COLUMN_NAME'] . "'
        $rulF
        validate-on-blur
        rows='2'
        :readonly=\"accion == 'show'\"
      >
      </v-textarea>
";
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
                    // echo "2 rel table " . $col['COLUMN_NAME'];
                    // echo "2.1 rel table " . $refTable;
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

                if ($col['typeF'] == 'sel') {
                  $refTable=ucfirst($col['COLUMN_NAME']);
                  $dataRel .= "
        l{$refTable}: [
";
          foreach ($col['selList'] as $key => $val) {
            $dataRel .="          { id: '".$val['value']."', name: '".$val['text']."' },
";
          }
          $dataRel .="        ],";
                  $relCol = "lista: 'l{$refTable}',";
                  $addInput = true;
                  $formul   = "
        <v-select
          :items='l{$refTable}'
          item-text='name'
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
                    $lOptions = "options: [1, 'Si', 'No'],";
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

                if ($col['typeF'] == 'date') {

                    $addInput = true;
                    $formul   = "
      <mk-date
      v-model='item." . $col['COLUMN_NAME'] . "'
      label='" . $col['lForm'] . "'
      $rulF
      :accion='accion'
      >
      </mk-date>
";
                }

                if ($col['typeF'] == 'time') {

                  $addInput = true;
                  $formul   = "
    <mk-time
    v-model='item." . $col['COLUMN_NAME'] . "'
    label='" . $col['lForm'] . "'
    $rulF
    :accion='accion'
    >
    </mk-time>
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
                if ($tipo == 'selDB' || $tipo == 'sel') {
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
          $lOptions
          $relCol
        },";
            }
        }
        if (count($attributes) > 0) {
            $attributes = "protected \$attributes = [" . join(',', $attributes) . "];";
        } else {
            $attributes = '';
        }
        $attributes = "protected \$table = '{$tablaT}';
        {$attributes}";
        if (!empty($relMounted)) {
            $relMounted = "
          let listas = await this.getDatasBackend(this.urlModulo, [{$relMounted}
          ])";
        }

        //protected $attributes = ['status' => 1];
        $fillable  = join(',', $fillable);
        $rules     = join(",\r\n" . $this->tabs(3), $rules);
        $model     = str_replace(['{{**NameSpace**}}', '{{**NameClass**}}', '{{**Fillable**}}', '{{**Attributes**}}', '{{**Rules**}}', '{{**BackRel**}}'], [$moduloBack, $Clase, $fillable, $attributes, $rules, $backRel], $model);
        $controler = str_replace(['{{**NameSpace**}}', '{{**NameClass**}}'], [$moduloBack, $Clase], $controler);
        $component = str_replace(['{{**NameClass**}}', '{{**Formulario**}}', '{{**titModulo**}}',
            '{{**Lista**}}', '{{**dataRel**}}', '{{**RelMounted**}}'], [$Clase, $formulario, $modTit, $campos, $dataRel, $relMounted], $component);

        //echo '<br>MEnu <hr>';
        $menu = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '../../unicef-Front/api/menu.js');
        $menu = str_replace(['export default Menu', 'const Menu = ', "\n"], '', $menu);
        try {
            $menu1 = json_decode($menu);
            if (empty($menu1)) {
              $menu = str_replace(['export default Menu', 'const Menu = ', "\n"], '', $menu);
              $menu = str_replace(['{', ",", ':', ',, {', "'"], ['{{', ',,', '::', ',{', '"'], $menu);
              $menu = preg_replace(['/\s+/', '/\s*(?=,)|\s*(?=:)|[,]\s+|[:]\s+|[{]\s+/', '/\{(.+):/Ui', '/\,([^\{].+):/Ui'], [' ', '', '{"$1":', ',"$1":'], $menu);
              $menu = json_decode($menu);
            }else {
              $menu=$menu1;
            }
        } catch (\Throwable $th) {
            $menu = str_replace(['{', ",", ':', ',, {', "'"], ['{{', ',,', '::', ',{', '"'], $menu);
            $menu = preg_replace(['/\s+/', '/\s*(?=,)|\s*(?=:)|[,]\s+|[:]\s+|[{]\s+/', '/\{(.+):/Ui', '/\,([^\{].+):/Ui'], [' ', '', '{"$1":', ',"$1":'], $menu);
            $menu = json_decode($menu);
        }
        $existe = 0;
        foreach ($menu as $key => $modMenu) {
            if (!empty($modMenu->name) && $modMenu->name == $moduloFront) {
                $existe = 1;
                foreach ($modMenu->items as $key1 => $modMenu1) {
                    if (!empty($modMenu1->name) && $modMenu1->name == $modulo) {
                        $existe = 2;
                        $menu[$key]->items[$key1] = json_decode('{"name":"' . $modulo . '","title":"' . $modTit . '","href":"' . "/{$moduloFront}/{$modulo}/" . '"}');
                        break;
                    }
                }
                if ($existe == 1) {
                    $menu[$key]->items[] = json_decode('{"name":"' . $modulo . '","title":"' . $modTit . '","href":"' . "/{$moduloFront}/{$modulo}/" . '"}');
                }
            }
        }
        if ($existe == 0) {
            $menu[] = json_decode('{"name":"' . $moduloFront . '","title":"' . ucfirst($moduloFront) . '","icon":"face","items":[{"name":"' . $modulo . '","title":"' . $modTit . '","href":"' . "/{$moduloFront}/{$modulo}/" . '"}]}');
        }

        //dd($menu);

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '../../unicef-Front/api/menu.js', 'const Menu = ' . print_r(json_encode($menu), true) . "\n export default Menu");
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
