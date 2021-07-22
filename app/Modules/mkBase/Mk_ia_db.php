<?php
namespace App\Modules\mkBase;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use \App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;
use \App\Modules\mkBase\Mk_helpers\Mk_db;
use \App\Modules\mkBase\Mk_helpers\Mk_debug;
use \App\Modules\mkBase\Mk_helpers\Mk_forms;
use \Illuminate\Http\Request;

const _errorNoExiste      = -1;
const _errorAlGrabar      = -10;
const _errorAlGrabar2     = -11;
const _errorLogin         = -1000;
const _errorNoAutenticado = -1001;

const _maxRowTable             = 1000;
const _cacheQueryDebugInactive = true;
const _cachedQuerys            = 'cachedQuerys_';
const _cachedTime              = 30 * 24 * 60 * 60;

trait Mk_ia_db
{
    public function proteger($act = '', $controler = '')
    {
        if (isset($this->_autorizar)) {
            if (empty($controler)) {
                if (!empty($this->_autorizar)) {
                    $controler = $this->_autorizar;
                }
            }
            Mk_auth::get()->proteger($act, $controler);
        }
    }
    public function __init(Request $request)
    {
        // if (isset($this->_autorizar)) {
        //     Mk_debug::warning('Modulo protegido', 'AUTH', basename($this->__modelo),'info');
        // }
        Mk_db::startDbLog();
        return true;
    }

    public function index(Request $request, $_debug = true)
    {
        $this->proteger();

        if ($request->has('flushCache')) {
            Cache::flush();
        }

        $page     = Mk_forms::getParam('page', 1);
        $perPage  = Mk_forms::getParam('per_page', 5);
        $sortBy   = Mk_forms::getParam('sortBy', 'id');
        $order    = Mk_forms::getParam('order', 'desc');
        $buscarA  = Mk_forms::getParam('buscar', '');
        $recycled = $request->recycled;
        $cols     = $request->cols;
        $disabled = $request->disabled;

        $prefix = $this->addCacheList($this->__modelo, [$page, $perPage, $sortBy, $order, $buscarA, $recycled, $cols, $disabled]);
        if (_cacheQueryDebugInactive) {
            Cache::forget($prefix);
            Mk_debug::warning('Cache del BACKEND Desabilitado!', 'CACHE', 'BackEnd');
        }

        $datos = Cache::remember($prefix, _cachedTime, function () use ($prefix, $page, $perPage, $sortBy, $order, $buscarA, $recycled, $cols, $disabled) {
            $modelo = new $this->__modelo();
            $table  = $modelo->getTable();
            Mk_debug::warning('Se cargo de la BD! ' . $table, 'CACHE ACTIVO', 'BackEnd');
            if (!empty($cols)) {
                $cols = explode(',', $cols);
                $cols = array_merge([$modelo->getKeyName()], $cols);
            } else {
                if (!$modelo->_listTable) {
                    $modelo->_listTable = $modelo->getFill();
                }
                $cols = array_merge([$modelo->getKeyName()], $modelo->_listTable);
            }
            $modelo->isJoined($buscarA, $sortBy);

            $consulta = $modelo->orderBy(Mk_db::tableCol($sortBy, $modelo), $order);

            $where = Mk_db::getWhere($buscarA, $modelo);

            if ($recycled == 1) {
                $consulta = $consulta->onlyTrashed();
            }
            $colsJoin = [];
            if ($modelo->joined) {
                if (!empty($modelo->_joins)) {
                    foreach ($modelo->_joins as $t => $d) {
                        //echo "onsearh: ".$d['onSearch']." joined:"+$d['joined'];
                        if ((empty($d['onSearch'])) || (($d['onSearch'] === true) && ($d['joined'] === true))) {
                            //  echo "entro onsearh: ";
                            switch ($d['type']) {
                                case 'left':
                                    $consulta = $consulta->leftJoin($t, ...$d['on']);
                                    break;
                                case 'right':
                                    $consulta = $consulta->rightJoin($t, ...$d['on']);
                                    break;
                                default:
                                    $consulta = $consulta->join($t, ...$d['on']);
                                    break;
                            }
                            if (!empty($d['groupBy'])) {
                                $consulta = $consulta->groupBy($d['groupBy']);
                            }
                            if (!empty($d['fields'])) {
                                $colsJoin = array_merge($colsJoin, $d['fields']);
                            }
                        }
                    }
                }
            }

            if ($disabled == 1) {
                if (in_array("status", $modelo->getFill())) {
                    if ($where != '') {
                        $where = '(' . $where . ")and({$table}.status<>'0')";
                    } else {
                        $where = "({$table}.status<>'0')";
                    }
                }
            }

            if ($where != '') {
                $consulta = $consulta->whereRaw($where);
            }

            if ($perPage < 0) {
                //$perPage=_maxRowTable;
            }

            if (isset($modelo->_withRelations)) {
                $consulta = $consulta->with($modelo->_withRelations);
            }
            //$cols     = array_merge($cols, $colsJoin);
            $colsJoin = Mk_db::tableCol($colsJoin, $modelo);
            $cols     = Mk_db::tableCol($cols, $modelo);
            $consulta = $consulta->select($cols);
            if (!empty($modelo->_customFields)) {
                foreach ($modelo->_customFields as $field) {
                    $consulta = $consulta->addSelect(DB::raw($field));
                }
            }

            if (!empty($colsJoin)) {
                foreach ($colsJoin as $field) {
                    $consulta = $consulta->addSelect(DB::raw($field));
                }
            }

            //Mk_debug::msgApi(['listar index perpage', $perPage]);
            if ($perPage < 0) {
                $result = $consulta->get()->toArray();
                $result = [
                    'total' => count($result),
                    'data'  => $result,
                ];
            } else {
                $result = $consulta->paginate($perPage)->toArray();
                //$modelo->simplePaginate($perPage, Mk_db::tableCol($cols, $modelo), 'page', $page);
            }
            return $result;

        });

        if ($request->ajax()) {
            return $datos;
        } else {
            // if ($perPage>0){
            //     $d=$datos->toArray();
            // }else{
            //     $d=$datos;
            // }

            $datos['data'] = $this->isCachedFront($datos['data']);
            $datos         = $this->isCachedFront($datos);
            return Mk_db::sendData($datos['total'], $datos['data'], '', $_debug, true);
        }
    }
    public function isCachedFront($data, $nct = 1, $mod = '')
    {

        if ($mod != '') {
            if ($mod == md5(json_encode($data))) {
                $data = '_ct_';
            }
        } else {
            $_ct = '_ct_';
            if ($nct != 1) {
                $_ct = '_ct2_';
            }

            if (request()->has($_ct)) {
                if (request()->input($_ct, '') == md5(json_encode($data))) {
                    $data = '_ct_';
                }
            }
        }
        return $data;
    }
    public function beforeDel($id, $modelo)
    {
    }
    public function afterDel($id, $modelo, $error = 0)
    {
    }

    public function beforeRestore($id, $modelo)
    {
    }
    public function afterRestore($id, $modelo, $error = 0)
    {
    }

    public function beforeSave(Request $request, $modelo, $id = 0)
    {

    }
    public function afterSave(Request $request, $modelo, $action = 0, $id = 0)
    {
    }

    public function storeMkImg($imgDel = false, $imgFile = '', $prefix = '', $id = 0)
    {
        if (!empty($imgDel)) {
            $file = Storage::disk('public')->delete($prefix . '_' . $id . '.png');
        }
        if (!empty($imgFile)) {
            $file = base64_decode(substr($imgFile, strpos($imgFile, ",") + 1));
            $file = Storage::disk('public')->put($prefix . '_' . $id . '.png', $file, 'public');
        }
    }
    public function store(Request $request)
    {
        $this->proteger();
        DB::beginTransaction();
        try {
            $datos = new $this->__modelo();
            $rules = $datos->getRules($request);
            if (!empty($rules)) {
                $validatedData = $request->validate($rules);
            }
            $datos->fill($request->only($datos->getfill()));
            $id     = 0;
            $grabar = $this->beforeSave($request, $datos, $id);
            Mk_debug::msgApi(['Grabar:', $grabar]);
            if ((!$grabar) or ($grabar == 1)) {
                Mk_debug::msgApi(['Entro a Save:', $request]);
                if ($datos->timestamps && !isset($this->_notBy)) {
                    $datos->created_by = $datos->getUser(isset($this->_autorizar));
                }
                $r = $datos->save();
            } else {
                $r = $grabar;
                if ($grabar < 0) {
                    $r = false;
                }
            }
            Mk_debug::warning(['Grabar es:', $grabar]);
            if ($r) {
                if ((!$grabar) or ($grabar == 1)) {
                    $_key = $datos->getKeyName();
                    $r    = $datos->$_key;
                }
                $msg = '';
                $this->afterSave($request, $datos, 0, $r);
                DB::commit();
                $this->clearCache();
                //modulo adicionales
                //MkImg
                $this->storeMkImg($request->imgDel, $request->imgFile, $datos->getTable(), $r);
            } else {
                DB::rollback();
                $r   = _errorAlGrabar;
                $msg = 'Error Al Grabar';
            }
        } catch (\Throwable $th) {
            DB::rollback();
            $r        = _errorAlGrabar2;
            $msgError = '';
            Mk_debug::msgApi(['Error:', $th]);
            if (@$th->status == 422) {
//todo: revisar nueva estrutiura de th en laravel 8 y lumens
                foreach ($th->errors() as $key => $value) {
                    $msgError .= "\n " . $key . ':' . join('.', $value);
                }
                Mk_debug::error($msgError, 'Validacion');
            }
            //  else {
            //     Mk_debug::msgApi(['Error:',$th]);
            // }

            $msg = "Error mientras se Grababa: \n" . $th->getMessage() . $msgError;
        }

        if (!$request->ajax()) {
            if ($request->has('_noData')) {
                return Mk_db::sendData($r, [], $msg);
            }
            return Mk_db::sendData($r, $this->index($request, false), $msg);
        }
    }

    public function show($id, Request $request)
    {
        try {
            $this->proteger();
            $datos = new $this->__modelo;
            $key   = $datos->getKeyName();

            $cols = array_merge([$datos->getKeyName()], $datos->getFill());

            $cols      = Mk_db::tableCol($cols, $datos);
            $_custom   = $datos->_customFields;
            $_rel      = $datos->_withRelations;
            $_relExtra = $datos->_withRelationsExtra;
            $datos     = $datos->select($cols);
            if (!empty($_custom)) {

                foreach ($_custom as $field) {

                    $datos = $datos->addSelect(DB::raw($field));
                }

            }
            if (!empty($request->where) && !empty($request->valor)) {
                $datos = $datos->where(
                    str_replace("'", "", DB::connection()->getPdo()->quote($request->where)),
                    str_replace("'", "", DB::connection()->getPdo()->quote($request->valor))
                );
            }
            if (empty($request->existe)) {
                $datos = $datos->where($key, '=', $id);
            }
            if (isset($_rel)) {
                $datos = $datos->with($_rel);
            }
            if (isset($_relExtra)) {
                $datos = $datos->with($_relExtra);
            }

            $prefix = $this->addCacheList($this->__modelo, [$id, $request->existe, DB::connection()->getPdo()->quote($request->where), DB::connection()->getPdo()->quote($request->valor)]);
            if (_cacheQueryDebugInactive) {
                Cache::forget($prefix);
                Mk_debug::warning('Cache del BACKEND Desabilitado!', 'CACHE', 'BackEnd');
            }
            $datos = Cache::remember($prefix, _cachedTime, function () use ($datos) {
                return $datos->first();
            });

            if (!$datos) {
                $id = -1;
            } else {
                $id = $datos->$key;
            }
            if (empty($request->existe)) {
                return Mk_db::sendData($id, $datos);

            } else {
                return Mk_db::sendData($id);
            }
        } catch (\Throwable $th) {
            return Mk_db::sendData(-2, null, $th->getMessage());
        }
    }

    public function edit($id)
    {
        $this->proteger();
        $datos = $this->__modelo::findOrFail($id);
        return $datos;
    }

    public function update(Request $request, $id)
    {
        $this->proteger();

        DB::beginTransaction();
        try {
            $datos = new $this->__modelo();
            $_key  = $datos->getKeyName();
            if (!$id) {
                $id = $request->$_key;
            }
            $rules = $datos->getRules($request);
            if (!empty($rules)) {
                $validatedData = $request->validate($rules);
            }

            //$newDatos=new stdobjet();
            $dataUpdate = $request->only($datos->getfill());
            Mk_debug::msgApi(['request antesd', $dataUpdate]);
            $this->beforeSave($request, $dataUpdate, $id);

            Mk_debug::msgApi(['requestDESP', $dataUpdate, $id]);
            if (!empty($dataUpdate)) {
                if ($datos->timestamps && !isset($this->_notBy)) {
                    $dataUpdate['updated_by'] = $datos->getUser(isset($this->_autorizar));
                }
                $r = $datos->where($_key, '=', $id)
                    ->update(
                        $dataUpdate //$request->only($datos->getfill())
                    );
            } else {
                $r = 1000000;
            }
            $msg = '';
            if ($r == 0) {
                $r   = _errorNoExiste;
                $msg = 'Registro ya NO EXISTE';
                DB::rollback();
            } else {

                $this->afterSave($request, $datos, 1, $id);
                DB::commit();
                $this->clearCache();
                //modulo adicionales
                //MkImg
                $this->storeMkImg($request->imgDel, $request->imgFile, $datos->getTable(), $id);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            $r        = _errorAlGrabar2;
            $msgError = '';
            if ((!empty($th->status)) && ($th->status == 422)) {
                foreach ($th->errors() as $key => $value) {
                    $msgError .= "\n " . $key . ':' . join(',', $value);
                }
                Mk_debug::error($msgError, 'Validacion');
            } else {
                Mk_debug::msgApi(['Error:', $th]);
            }
            $msg = 'Error mientras se Actualizaba: ' . $th->getLine() . ':' . $th->getFile() . '=' . $th->getMessage();
        }
        if (!$request->ajax()) {
            if ($request->has('_noData')) {
                return Mk_db::sendData($r, [], $msg);
            }
            return Mk_db::sendData($r, $this->index($request, false), $msg);
        }
    }

    public function destroy(Request $request)
    {
        $this->proteger();
        $recycled = $request->recycled;
        $id       = explode(',', $request->id); //TODO::bajar mas abajoy cambiar id por $key
        DB::beginTransaction();
        try {
            $datos = new $this->__modelo();
            $_key  = $datos->getKeyName();

            $this->beforeDel($id, $datos);
            if ($recycled == 1) {
                $r = $datos->onlyTrashed()->wherein($_key, $id)
                    ->forceDelete();
            } else {
                $datos->runCascadingDeletes($id);
                if ($datos->timestamps && !isset($this->_notBy)) {
                    $datos->where($_key, '=', $id)
                        ->update(
                            ['deleted_by' => $datos->getUser(isset($this->_autorizar))]
                        );
                }
                $r = $datos->wherein($_key, $id)
                    ->delete();
            }
            $msg = '';
            if ($r == 0) {
                $r   = _errorNoExiste;
                $msg = 'Registro ya NO EXISTE';
                DB::rollback();
            } else {
                $this->afterDel($id, $datos, $r);
                DB::commit();
                $this->clearCache(null, true);

            }
        } catch (\Throwable $th) {
            DB::rollback();
            $r   = _errorAlGrabar2;
            $msg = 'Error mientras se Eliminaba: ' . $th->getMessage();
        }

        if (!$request->ajax()) {
            return Mk_db::sendData($r, $this->index($request, false), $msg);
        }
    }

    public function restore(Request $request)
    {
        $this->proteger();
        $recycled = $request->recycled;
        $id       = explode(',', $request->id);

        DB::beginTransaction();
        try {
            if ($recycled != 1) {
                throw new Exception("Debe estar en Papelera de Reciclaje", 1);
            }
            $datos = new $this->__modelo();
            $_key  = $datos->getKeyName();

            $this->beforeRestore($id, $datos);
            $datos->runCascadingDeletes($id, true);
            $r = $datos->onlyTrashed()->wherein($_key, $id)
                ->restore();
            $msg = '';
            if ($r == 0) {
                $r   = _errorNoExiste;
                $msg = 'Registro ya NO EXISTE';
                $this->clearCache(null, true);
                DB::rollback();
            } else {
                $this->afterRestore($id, $datos, $r);
                DB::commit();
                $this->clearCache(null, true);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            $r   = _errorAlGrabar2;
            $msg = 'Error mientras se Restauraba: ' . $th->getMessage();
        }

        if (!$request->ajax()) {
            return Mk_db::sendData($r, $this->index($request, false), $msg);
        }
    }

    public function setStatus(Request $request)
    {
        $this->proteger();
        $newStatus = $request->status;
        $id        = explode(',', $request->id);
        DB::beginTransaction();
        $datos = new $this->__modelo();
        $_key  = $datos->getKeyName();

        if ($datos->timestamps && !isset($this->_notBy)) {
            $r = $datos->wherein($_key, $id)
                ->update([
                    'status'     => $newStatus,
                    'updated_by' => $datos->getUser(isset($this->_autorizar)),
                ]);
        } else {
            $r = $datos->wherein($_key, $id)
                ->update([
                    'status' => $newStatus,
                ]);
        }
        $msg = '';
        if ($r == 0) {
            $r   = _errorNoExiste;
            $msg = 'Registro ya NO EXISTE';
            DB::rollback();
        } else {
            DB::commit();
            $this->clearCache();
        }
        if (!$request->ajax()) {
            return Mk_db::sendData($r, $this->index($request, false), $msg);
        }
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

    public function listData(Request $request)
    {
        //$x=DB::table('usuarios')->getModel();
        //Mk_debug::msgApi(['listada comercial',$this->loadMod()]);

        //return Mk_db::sendData(2, $request->lista, '');
        $this->proteger('show');
        $r = [];
        foreach ($request->lista as $key => $lista) {
            $modulo        = !empty($lista['modulo']) ? $lista['modulo'] : 'mk' . $lista['mod'];
            $modelo        = 'App\Modules\\' . $modulo . '\\' . $lista['mod'];
            $cols          = !empty($lista['campos']) ? $lista['campos'] : '';
            $_customFields = !empty($lista['_customFields']) ? $lista['_customFields'] : false;
            $rel           = !empty($lista['rel']) ? $lista['rel'] : false;
            $relations     = !empty($lista['relations']) ? $lista['relations'] : false;
            $filtros       = !empty($lista['filtros']) ? $lista['filtros'] : [];
            $l             = !empty($lista['l']) ? $lista['l'] : $lista['mod'];
            $r[$l]         = $this->getDatosDbCache($request, $modelo, $cols, ['relations' => $relations, 'filtros' => $filtros, 'rel' => $rel, '_customFields' => $_customFields, 'send' => false], $lista['ct']);
        }
        return Mk_db::sendData(2, $r, '');
    }

    public function getDatosDbCache(Request $request, $model, $cols = '', $options = [], $mod = '', $debug = true)
    {
        $filtros       = !empty($options['filtros']) ? $options['filtros'] : [];
        $relations     = !empty($options['relations']) ? $options['relations'] : [];
        $_send         = !empty($options['send']) ? $options['send'] : false;
        $perPage       = !empty($options['perPage']) ? $options['perPage'] : _maxRowTable;
        $page          = !empty($options['page']) ? $options['page'] : 1;
        $_customFields = !empty($options['_customFields']) ? $options['_customFields'] : false;
        $rel           = !empty($options['rel']) ? $options['rel'] : false;
        $sortBy        = !empty($options['sortBy']) ? $options['sortBy'] : 'id';
        $sortDir       = !empty($options['sortDir']) ? $options['sortDir'] : 'desc';

        $prefix = $this->addCacheList($model, [$page, $perPage, $sortBy, $sortDir, '', 0, $cols, $options, $rel]);
        if (_cacheQueryDebugInactive) {
            Cache::forget($prefix);
            Mk_debug::warning('Cache del BACKEND Desabilitado!', 'CACHE', 'BackEnd');
        }

        $datos = Cache::remember($prefix, _cachedTime, function () use ($cols, $model, $page, $sortBy, $sortDir, $perPage, $filtros, $relations, $_customFields, $rel) {
            Mk_debug::warning('Se cargo de la BD! ' . $model, 'CACHE ACTIVO', 'BackEnd');
            $modelo = new $model();
            if ($_customFields == 1) {
                $_customFields = !empty($modelo->_customFields) ? $modelo->_customFields : [];
            }

            // if ($cols=='*') {
            //   $cols='';
            // }
            if (!empty($cols) && $cols != '*') {
                $cols = explode(',', $cols);
                $cols = array_merge([$modelo->getKeyName()], $cols);
            } else {
                if ($cols == '*') {
                    $modelo->_listTable = $modelo->getFill();
                } else {
                    if (!$modelo->_listTable) {
                        $modelo->_listTable = $modelo->getFill();
                    }
                }
                $cols = array_merge([$modelo->getKeyName()], $modelo->_listTable);
            }

            // $cols = explode(',', $cols);
            // $cols = array_merge([$modelo->getKeyName()], $cols);
            //Mk_debug::warning('filtros', $model, $filtros);
            if ($rel) {
                Mk_debug::warning('Entro a rel', 'CACHE', $rel, $model);
                if (isset($modelo->_withRelations)) {
                    Mk_debug::warning('Entro a relaciones', 'CACHE', $modelo->_withRelations);
                    $modelo = $modelo->with($modelo->_withRelations);
                }
            }

            $modelo = $modelo->orderBy(Mk_db::tableCol($sortBy, $modelo), $sortDir);
            foreach ($filtros as $key => $filtro) {
                if ($filtro[0] != 'OR') {
                    $modelo = $modelo->where($filtro[0], $filtro[1], $filtro[2]);
                } else {
                    $modelo = $modelo->
                        where(function ($query) use ($filtro) {
                        $query->where($filtro[1][0], $filtro[1][1], $filtro[1][2])
                            ->orWhere($filtro[2][0], $filtro[2][1], $filtro[2][2]);
                    });
                }
            }

            if ($relations) {
                Mk_debug::warning('Entro a relation', 'CACHE', $relations);
                $modelo = $modelo->with($relations);
            }

            //$cols=array_merge($cols, $colsJoin);
            $cols   = Mk_db::tableCol($cols, $modelo);
            $modelo = $modelo->select($cols);
            //Mk_debug::warning('CustumFields2', 'CACHE', $_customFields);
            if (!empty($_customFields)) {
                //Mk_debug::warning('CustumFields!', 'CACHE', $modelo->_customFields);
                foreach ($_customFields as $field) {
                    $modelo = $modelo->addSelect(DB::raw($field));
                }
            }
            // if ($_customFields==1) && !empty($modelo->_customFields)){
            //     Mk_debug::warning('CustumFields!', 'CACHE', $modelo->_customFields);
            //      foreach ($modelo->_customFields as $field){
            //         $modelo=$modelo->addSelect(DB::raw($field));
            //      }
            // }

            return $modelo->simplePaginate($perPage, Mk_db::tableCol($cols, $modelo), 'page', $page);
        });

        if ($request->ajax()) {
            return $datos;
        } else {
            $d         = $datos->toArray();
            $total     = count($d['data']);
            $d['data'] = $this->isCachedFront($d['data'], 1, $mod);
            if ($_send) {
                return Mk_db::sendData($total, $d['data'], '', $debug);
            } else {
                return $d['data'];
            }

        }

    }

    private function addCacheList($model, $key = ['ok'])
    {

        $prefixList = $this->getCacheKey($model); //_cachedQuerys.basename($this->__modelo);
        $prefix     = md5($model . collect($key)->__toString());
        $cached     = Cache::get($prefixList, []);
        //Mk_debug::Warning(['Se crea Cache: ',$prefixList]);
        if (!in_array($prefix, $cached)) {
            array_push($cached, $prefix);
            Cache::put($prefixList, $cached, _cachedTime);
            Cache::forget($prefix);
            Mk_debug::Warning(['Cache Enlistado: ', $prefixList]);
        }
        return $prefix;
    }

    public function getCacheKey($modelo = false)
    {
        if (empty($modelo)) {
            $modelo = $this->__modelo;
        }
        return _cachedQuerys . strtolower(basename($modelo));
    }

    private function clearCache($modelCached = null, $cascade = false) //mientras pondremos a true hasta ver las relaciones de cache entre modulos

    {
        if ($cascade) {
            $lista = Mk_debug::getGlobal('cascadeDelete');
        }
        if (empty($modelCached)) {
            $modelCached = $this->__modelo;
            $modelo      = new $modelCached();
            if (!empty($modelo->_cachedRelations)) {
                foreach ($modelo->_cachedRelations as $key => $relation) {
                    Mk_debug::msgApi(['ClearCache Request: ', request()->has($relation[1])]);
                    if (request()->has($relation[1])) {
                        $lista[] = $relation[0];
                    }
                }
            }
        }

        $lista[] = $modelCached;

        foreach ($lista as $key => $model) {
            $prefixList = $this->getCacheKey($model);
            $cached     = Cache::get($prefixList, []);
            //Mk_debug::msgApi(['ClearCache: ', $prefixList]);
            Mk_debug::Warning(['ClearCache: ', $prefixList]);
            foreach ($cached as $key => $value) {
                Cache::forget($value);
                //Mk_debug::msgApi(['limpiando '.$value,Cache::get($value, 'No existe')]);
            }
            Cache::forget($prefixList, []);
        }
        return true;
    }
}
//TODO: hacer que la Autorizacion en la variablke autproizar, cada caracter represente a un metodo de resource del controlador si esta vacio es todos, sino se mete cada uno que se desee, tal vez con un -cuando sea uno que no desee
//TODO: pasar todas las Constantes a una clase propia que se pueda importar
// public function is_cacheQueryDebugInactive(){
//     return _cacheQueryDebugInactive;
// }
// public function get_maxRowTable(){
//     return _maxRowTable;
// }
// public function get_cachedTime(){
//     return _cachedTime;
// }
