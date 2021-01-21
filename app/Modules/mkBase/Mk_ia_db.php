<?php
namespace App\Modules\mkBase;

use \Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use \App\Modules\mkBase\Mk_helpers\Mk_db;
use \App\Modules\mkBase\Mk_helpers\Mk_debug;
use \App\Modules\mkBase\Mk_helpers\Mk_forms;
use \App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;

const _errorNoExiste=-1;
const _errorAlGrabar=-10;
const _errorAlGrabar2=-11;
const _errorLogin=-1000;
const _errorNoAutenticado=-1001;

const _maxRowTable=1000;
const _cacheQueryDebugInactive=true;
const _cachedQuerys='cachedQuerys_';
const _cachedTime=30*24*60*60;

trait Mk_ia_db
{
    public function proteger($act='', $controler='')
    {
        if (isset($this->_autorizar)) {
            if (empty($controler)) {
                if (!empty($this->_autorizar)) {
                    $controler=$this->_autorizar;
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

    public function index(Request $request, $_debug=true)
    {
        $this->proteger();
        
        if ($request->has('flushCache')) {
            Cache::flush();
        }

        $page=Mk_forms::getParam('page', 1);
        $perPage=Mk_forms::getParam('per_page', 5);
        $sortBy=Mk_forms::getParam('sortBy', 'id');
        $order=Mk_forms::getParam('order', 'desc');
        $buscarA=Mk_forms::getParam('buscar', '');
        $recycled=$request->recycled;
        $cols=$request->cols;
        $disabled=$request->disabled;
        

        $prefix=$this->addCacheList($this->__modelo,[$page,$perPage,$sortBy,$order,$buscarA,$recycled,$cols,$disabled]);
        if (_cacheQueryDebugInactive) {
            Cache::forget($prefix);
            Mk_debug::warning('Cache del BACKEND Desabilitado!', 'CACHE', 'BackEnd');
        }

        //Mk_debug::msgApi(['Se busca si Existe Item Cache:'.$prefix,'Existe o no:'.Cache::has($prefix)]);
        $datos=Cache::remember($prefix, _cachedTime, function () use ($prefix,$page,$perPage,$sortBy,$order,$buscarA,$recycled,$cols,$disabled) {
            $modelo=new $this->__modelo();
            $table=$modelo->getTable();

            if ($cols!='') {
                $cols=explode(',', $cols);
                $cols=array_merge([$modelo->getKeyName()], $cols);
            } else {
                $cols=array_merge([$modelo->getKeyName()], $modelo->getFill());
            }
            $modelo->isJoined($buscarA);

            $consulta=$modelo->orderBy(Mk_db::tableCol($sortBy, $modelo), $order);

            $where=Mk_db::getWhere($buscarA, $modelo);

            if ($recycled==1) {
                $consulta=$consulta->onlyTrashed();
            }
            $colsJoin=[];
            if ($modelo->joined) {
                if (!empty($modelo->_joins)) {
                    //Mk_debug::msgApi('Entro a modelo Joins')
                    foreach ($modelo->_joins as $t => $d) {
                        //Mk_debug::msgApi(['Entro a modelo Joins'.$t,((empty($d['onSearh']))||(($d['onSearh']===true)&&($d[joined]===true)))]);
                        if ((empty($d['onSearh']))||(($d['onSearh']===true)&&($d[joined]===true))) {
                            //Mk_debug::msgApi(['Entro al if ',$d['on']]);
                            switch ($d['type']) {
                        case 'left':
                            $consulta=$consulta->leftJoin($t, ...$d['on']);
                            break;
                        case 'right':
                            $consulta=$consulta->rightJoin($t, ...$d['on']);
                            break;
                        default:
                            $consulta=$consulta->join($t, ...$d['on']);
                            break;
                         }
                            if (!empty($d['fields'])) {
                                $colsJoin=array_merge($colsJoin, $d['fields']);
                                //$consulta=$consulta->addSelect(...$d['fields']);
                            }
                        }
                    }
                }
            }

            if ($disabled==1) {
                if ($where != '') {
                    $where ='('. $where. ")and({$table}.status<>'0')";
                } else {
                    $where ="({$table}.status<>'0')";
                }
            }

            if ($where!='') {
                $consulta = $consulta->whereRaw($where);
            }

            if ($perPage<0) {
                $perPage=_maxRowTable;
            }


            if (isset($modelo->_withRelations)) {
                //Mk_debug::msgApi(['Entro a la relaciomn:',$modelo->_withRelations]);
                $consulta = $consulta->with($modelo->_withRelations);
            }

            $cols=array_merge($cols, $colsJoin);
//            Mk_debug::msgApi(['Datos:',$result]);
            //Mk_debug::msgApi(['Se añadio Item Cache:'.$prefix,$cols,Mk_db::tableCol($cols, $modelo)]);
            return $consulta->paginate($perPage, Mk_db::tableCol($cols, $modelo), 'page', $page);
            
        });
        
        if ($request->ajax()) {
            return  $datos;
        } else {
//            dd($datos,DB::getQueryLog());
            $d=$datos->toArray();
            //Mk_debug::msgApi([$request->input('_ct_', ''),md5(json_encode($d['data']))]);
            $d['data']=$this->isCachedFront($d['data']);

            return Mk_db::sendData($d['total'], $d['data'], '', $_debug, true);
        }
    }
    public function isCachedFront($data, $ct=1)
    {
        $_ct='_ct_';
        if ($ct!=1) {
            $_ct='_ct2_';
        }
        if (\Request::has($_ct)) {
            if (\Request::input($_ct, '')==md5(json_encode($data))) {
                $data='_ct_';
            }
        }
        return $data;
    }
    public function beforeDel($id, $modelo)
    {
    }
    public function afterDel($id, $modelo, $error=0)
    {
    }

    public function beforeRestore($id, $modelo)
    {
    }
    public function afterRestore($id, $modelo, $error=0)
    {
    }

    public function beforeSave(Request $request, $modelo, $id=0)
    {
    }
    public function afterSave(Request $request, $modelo, $error=0, $id=0)
    {
    }

    public function storeMkImg($imgDel=false,$imgFile='',$prefix='',$id=0){
        if (!empty($imgDel)) {
            $file=Storage::disk('public')->delete($prefix.'_'.$id.'.png');
        }
        if (!empty($imgFile)) {
            $file=base64_decode(substr($imgFile, strpos($imgFile, ",")+1));
            $file=Storage::disk('public')->put($prefix.'_'.$id.'.png', $file, 'public');
        }
    }
    public function store(Request $request)
    {
        $this->proteger();
        DB::beginTransaction();
        try {
            $datos = new $this->__modelo();
            $rules=$datos->getRules($request);
            if (!empty($rules)) {
                $validatedData = $request->validate($rules);
            }

            $datos->fill($request->only($datos->getfill()));
            $this->beforeSave($request, $datos, 0);
            $r=$datos->save();

            if ($r) {
                $_key=$datos->getKeyName();
                $r=$datos->$_key;
                $msg='';
                $this->afterSave($request, $datos, 0, $r);
                DB::commit();
                $this->clearCache();
                //modulo adicionales
                    //MkImg
                $this->storeMkImg($request->imgDel,$request->imgFile,$datos->getTable(),$r);
            } else {
                DB::rollback();
                $r=_errorAlGrabar;
                $msg='Error Al Grabar';
            }
        } catch (\Throwable $th) {
            DB::rollback();
            $r=_errorAlGrabar2;
            $msgError='';
            Mk_debug::msgApi(['Error:',$th]);
            if (@$th->status==422) {//todo: revisar nueva estrutiura de th en laravel 8 y lumens
                foreach ($th->errors() as $key => $value) {
                    $msgError.="\n ".$key.':'.join($value, ',');
                }
                Mk_debug::error($msgError, 'Validacion');
            } else {
                Mk_debug::msgApi(['Error:',$th]);
            }

            $msg="Error mientras se Grababa: \n".$th->getMessage().$msgError;
        }

        if (!$request->ajax()) {
            return Mk_db::sendData($r, $this->index($request, false), $msg);
        }
    }

    public function show($id, Request $request)
    {
        try {
            $this->proteger();
            $datos= new $this->__modelo;
            $key=$datos->getKeyName();
            $datos = $datos->where(
                str_replace("'", "", DB::connection()->getPdo()->quote($request->where)),
                str_replace("'", "", DB::connection()->getPdo()->quote($request->valor))
            )
                        ->where($key, '!=', $id);
            if (empty($request->existe)) {
                $datos = $datos->first();
                if (!$datos) {
                    $id=-1;
                } else {
                    $id=$datos->$key;
                }
                return Mk_db::sendData($id, $datos);
            } else {
                $datos = $datos->select($key)->first();
                if (!$datos) {
                    $id=-1;
                } else {
                    $id=$datos->$key;
                }

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
            $_key=$datos->getKeyName();
            if (!$id) {
                $id=$request->$_key;
            }
            $rules=$datos->getRules($request);
            if (!empty($rules)) {
                $validatedData = $request->validate($rules);
            }
            $this->beforeSave($request, $datos, $id);
            //Mk_debug::msgApi(['request',$request->only($datos->getfill())]);
            $dataUpdate=$request->only($datos->getfill());
            if (!empty($dataUpdate)) {
                $r=$datos->where($_key, '=', $id)
             ->update(
                $dataUpdate//$request->only($datos->getfill())
             );
            }else{
                $r=1000000;
            }
            $msg='';
            if ($r==0) {
                $r=_errorNoExiste;
                $msg='Registro ya NO EXISTE';
                DB::rollback();
            } else {

                $this->afterSave($request, $datos, $r, $id);
                DB::commit();
                $this->clearCache();
                //modulo adicionales
                    //MkImg
                $this->storeMkImg($request->imgDel,$request->imgFile,$datos->getTable(),$id);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            $r=_errorAlGrabar2;
            $msgError='';
            if ((!empty($th->status))&&($th->status==422)) {
                foreach ($th->errors() as $key => $value) {
                    $msgError.="\n ".$key.':'.join(',',$value);
                }
                Mk_debug::error($msgError, 'Validacion');
            } else {
                Mk_debug::msgApi(['Error:',$th]);
            }
            $msg='Error mientras se Actualizaba: '.$th->getLine().':'.$th->getFile().'='.$th->getMessage();
        }
        if (!$request->ajax()) {
            return Mk_db::sendData($r, $this->index($request, false), $msg);
        }
    }

    public function destroy(Request $request)
    {
        $this->proteger();
        $recycled=$request->recycled;
        $id=explode(',', $request->id);//TODO::bajar mas abajoy cambiar id por $key
        DB::beginTransaction();
        try {
            $datos = new $this->__modelo();
            $_key=$datos->getKeyName();

            $this->beforeDel($id, $datos);
            if ($recycled==1) {
                $r=$datos->onlyTrashed()->wherein($_key, $id)
                ->forceDelete();
            } else {
                $datos->runCascadingDeletes($id);
                $r=$datos->wherein($_key, $id)
                ->delete();
            }
            $msg='';
            if ($r==0) {
                $r=_errorNoExiste;
                $msg='Registro ya NO EXISTE';
                DB::rollback();
            } else {
                $this->afterDel($id, $datos, $r);
                DB::commit();
                $this->clearCache(true);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            $r=_errorAlGrabar2;
            $msg='Error mientras se Eliminaba: '.$th->getMessage();
        }

        if (!$request->ajax()) {
            return Mk_db::sendData($r, $this->index($request, false), $msg);
        }
    }

    public function restore(Request $request)
    {
        $this->proteger();
        $recycled=$request->recycled;
        $id=explode(',', $request->id);

        DB::beginTransaction();
        try {
            if ($recycled!=1) {
                throw new Exception("Debe estar en Papelera de Reciclaje", 1);
            }
            $datos = new $this->__modelo();
            $_key=$datos->getKeyName();

            $this->beforeRestore($id, $datos);
            $datos->runCascadingDeletes($id, true);
            $r=$datos->onlyTrashed()->wherein($_key, $id)
                ->restore();
            $msg='';
            if ($r==0) {
                $r=_errorNoExiste;
                $msg='Registro ya NO EXISTE';
                $this->clearCache();
                DB::rollback();
            } else {
                $this->afterRestore($id, $datos, $r);
                DB::commit();
                $this->clearCache(true);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            $r=_errorAlGrabar2;
            $msg='Error mientras se Restauraba: '.$th->getMessage();
        }

        if (!$request->ajax()) {
            return Mk_db::sendData($r, $this->index($request, false), $msg);
        }
    }

    public function setStatus(Request $request)
    {
        $this->proteger();
        $newStatus=$request->status;
        $id=explode(',', $request->id);
        DB::beginTransaction();
        $datos = new $this->__modelo();
            $_key=$datos->getKeyName();

        
        $r=$datos->wherein($_key, $id)
        ->update([
        'status' => $newStatus,
        ]);
        $msg='';
        if ($r==0) {
            $r=_errorNoExiste;
            $msg='Registro ya NO EXISTE';
            DB::rollback();
        }else{
            DB::commit();
            $this->clearCache();
        }
        if (!$request->ajax()) {
            return Mk_db::sendData($r, $this->index($request, false), $msg);
        }
    }


    public function getDatosDbCache(Request $request,$model,$cols='',$filtros=[],$_debug=true){

        $perPage=_maxRowTable;
        $page=1;

        $prefix=$this->addCacheList($model,[$page,$perPage,'id','desc','',0,$cols,1]);
        if (_cacheQueryDebugInactive) {
            Cache::forget($prefix);
            Mk_debug::warning('Cache del BACKEND Desabilitado!', 'CACHE', 'BackEnd');
        }

        $datos=Cache::remember($prefix, _cachedTime, function () use ($cols, $model,$page,$perPage, $filtros) {
            $modelo=new $model();
            $cols=explode(',', $cols);
            $cols=array_merge([$modelo->getKeyName()], $cols);
            foreach ($filtros as $key => $filtro){
                if ($filtro[0]!='OR'){
                    $modelo=$modelo->where($filtro[0],$filtro[1],$filtro[2]);
                }else{
                    $modelo=$modelo->
                    where(function($query) use ($filtro) {
                        $query->where($filtro[1][0],$filtro[1][1],$filtro[1][2])
                              ->orWhere($filtro[2][0],$filtro[2][1],$filtro[2][2]);
                    });
                }
                
            }
            return $modelo->paginate($perPage, Mk_db::tableCol($cols, $modelo), 'page', $page);
        });

        if ($request->ajax()) {
            return  $datos;
        } else {
            $d=$datos->toArray();
            $d['data']=$this->isCachedFront($d['data']);
            return Mk_db::sendData($d['total'], $d['data'], '', $_debug, true);
        }

    }

    private function addCacheList($model,$key=['ok'])
    {
        
        $prefixList=$this->getCacheKey($model);//_cachedQuerys.basename($this->__modelo);
        $prefix=md5($model.collect($key)->__toString());
        $cached=Cache::get($prefixList, []);
        //$cachedToken=Cache::get($prefixList.'Token', 1);
        //Mk_debug::msgApi(['Intentando Añadir: '.$prefix.'('.$cachedToken.')',$cached]);
        //Mk_debug::msgApi(['Intentando Añadir: '.$prefix,$cached]);
        if (!in_array($prefix, $cached)) {
            array_push($cached, $prefix);
            Cache::put($prefixList, $cached, _cachedTime);
            Cache::forget($prefix);
            //Mk_debug::msgApi(['Cache Lista Añadido: '.$prefix,Cache::get($prefixList, []),$cached]);
        }
        return $prefix;
    }

    public function getCacheKey($modelo=false)
    {
        if (empty($modelo)) {
            $modelo=$this->__modelo;
        }
        return _cachedQuerys.strtolower(basename($modelo));
    }

    // public function getCacheTokenKey()
    // {
    //     return $this.getCacheKey().'Token';
    // }

    // public function getCacheToken()
    // {
    //     return $Cache::get($this.getCacheTokenKey(), 1);
    // }

    // public function setCacheToken($valor)
    // {
    //     return $Cache::put($this.getCacheTokenKey(), $valor);
    // }

    private function clearCache($cascade=false)//mientras pondremos a true hasta ver las relaciones de cache entre modulos
    {

        $lista[]=$this->__modelo;
        $modelo=new $this->__modelo();
        //$this->getCacheKey();
        if ($cascade) {
            $lista=$modelo->getCascadingTables();
        }
        Mk_debug::msgApi(['ClearCache: ',$modelo->_cachedRelations],$this->__modelo);
        if (!empty($modelo->_cachedRelations)){
            foreach ($modelo->_cachedRelations as $key => $relation) {
                Mk_debug::msgApi(['ClearCache Request: ',request()->has($relation[1])]);
                if (request()->has($relation[1])){
                    $lista[]=$relation[0];
                }
            }    
        }
        foreach ($lista as $key => $model) {
            $prefixList=$this->getCacheKey($model);
            $cached=Cache::get($prefixList, []);
            //$cachedToken=$this->getCacheToken();
            Mk_debug::msgApi(['se limpia cache de: '.$model]);
            //Mk_debug::msgApi(['se limpia cache de: '.$prefixList,$cached]);
            foreach ($cached as $key => $value) {
                Cache::forget($value);
                //Mk_debug::msgApi(['limpiando '.$value,Cache::get($value, 'No existe')]);
            }
            Cache::forget($prefixList, []);
            //Mk_debug::msgApi(['se limpio '.$prefixList,Cache::get($prefixList, 'Vacio')]);
        }

        return true;
    }
}

// public function is_cacheQueryDebugInactive(){
    //     return _cacheQueryDebugInactive;
    // }
    // public function get_maxRowTable(){
    //     return _maxRowTable;
    // }
    // public function get_cachedTime(){
    //     return _cachedTime;
    // }