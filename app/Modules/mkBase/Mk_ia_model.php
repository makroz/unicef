<?php
namespace App\Modules\mkBase;

use Illuminate\Support\Facades\DB;
use App\Modules\mkBase\Mk_helpers\Mk_debug;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\mkBase\Mk_helpers\Mk_auth\Mk_auth;
use Illuminate\Database\Eloquent\Relations\Relation;

trait Mk_ia_model
{
    use SoftDeletes;

    public $joined=false;

    //public $_cachedRelations=[];
    //protected $_cachedRelations = [];

    public static function getUser($auth=true){
      $user=Mk_auth::get()->getUser($auth);
      if (!empty($user->id)){
        return $user->id;
      }
      return 0;
    }

    public function getFill()
    {
        return $this->fillable;
    }

    public function scopeEnabled($query)
    {
        return $query->where('status', '<>', 0);
    }
    public function scopeDisabled($query)
    {
        return $query->where('status', '=', 0);
    }

    public function getJoinFields()
    {
        $colsJoin=[];
        if (!empty($this->_joins)) {
            foreach ($this->_joins as $t => $d) {
                if (!empty($d['fields'])) {
                    $colsJoin=array_merge($colsJoin, $d['fields']);
                }
            }
        }
        return $colsJoin;
    }

    public function isJoined($buscar=null,$orderBy=null)
    {

        $r=false;
//        $colsJoin=[];
        $cols=[];
        // echo "antes:";
        // print_r($buscar);
        if (!empty($buscar)) {
            $buscar=json_decode($buscar, true);
            foreach ($buscar as $key => $value) {
                $cols[]=$value['campo'];
            }
        }
        // echo "despues:";
        // print_r($cols);

        if (!empty($orderBy)) {
                $cols[]=$orderBy;
        }
        

        if (!empty($this->_joins)) {
            foreach ($this->_joins as $table => $join) {
                if (!empty($join['fields'])) {
                    //$colsJoin=array_merge($colsJoin, $join['fields']);
                    //
                    foreach ($join['fields'] as $fkey => $field) {
                        $nameField1='';$nameField2='';
                        if (\strpos($field,' as ')===false){
                            $nameField1=$field;
                            $nameField2=str_replace('.','_',$field);
                            $field=$field.' as '.$nameField2;
                            $join['fields'][$fkey]=$field;
                        }else{
                            $nameField1=explode(' as ',$field);
                            $nameField2=$nameField1[1];
                            $nameField1=$nameField1[0];
                        }
                        if ((\in_array($nameField1,$cols))||(\in_array($nameField2,$cols))){
                            // echo "$nameField1,$nameField2";
                            // print_r($cols);
                            $r=true;
                            $join['joined']=true;
                        }else{
                            $join['joined']=false;
                        }
                    }
                    $this->_joins[$table]=$join;
                }
            }
            $this->joined=$r;
            //dd( $this->_joins,$r);
            return $r;
        }
    }

    public function getRules($request)
    {
        return null;
    }

    public function toArray()
    {
        $attributes = $this->attributesToArray();
        $attributes = array_merge($attributes, $this->relationsToArray());
        //Mk_debug::msgApi(['2array',$attributes]);
        //if (!empty($this->_withRelations)&&!empty($this->_pivot2Array)) {
          if (!empty($this->_pivot2Array)) {
           
            foreach ($this->_pivot2Array as $key1 => $value1) {
                $dat=explode(':', $value1.':id');
                $rel=$dat[0];
                $piv=$dat[1];
                $dat=explode('.', '.'.$piv);
                $piv=$dat[count($dat)-1];

                if (isset($attributes[$rel])) {
                    if (!is_object($attributes[$rel])) {
                        $i=[];
                        foreach ($attributes[$rel] as $key => $value) {
                            if (is_array($value)) {
                              //if (!empty($value[$piv])) {
                                  $i[]=$value[$piv];
                             // }
                            } else {
                                if ($key==$piv) {
                                    $i=$value;
                                }
                            }
                        }
                    }else{
                        $i=$attributes[$rel][$piv];
                    }
                    $attributes[$rel] = $i;
                }
            }
        
        }
        return $attributes;
    }

    //****softdelete cascade ***

    public function getCascadingTables($first=null)
    {
        $cascade[]=get_class($this);

        foreach ($this->getCascadingDeletes() as $relationship) {
            $cascade[]=get_class($this->{$relationship}()->getRelated());
        }
        return $cascade;
    }

    public function runCascadingDeletes($ids, $restore=false)
    {
        if ($invalidCascadingRelationships = $this->hasInvalidCascadingRelationships()) {
            throw new \Exception($invalidCascadingRelationships);
        }
        foreach ($this->getActiveCascadingDeletes() as $relationship) {
            $this->cascadeSoftDeletes($relationship, $ids, $restore);
        }
    }

    protected function cascadeSoftDeletes($relationship, $ids, $restore=false)
    {
        $dato=$this->fromDateTime($this->freshTimestamp());
        if ($restore) {
            $dato=null;
        }
        //Mk_debug::msgApi(['cacadedelete1',$this->{$relationship}()->getExistenceCompareKey()]);
        try {
            $table=$this->{$relationship}()->getTable();
            $idrel=$this->{$relationship}()->getForeignPivotKeyName();
            $id=$this->{$relationship}()->getKeyName();
        } catch (\Throwable $th) {
            $table=$this->{$relationship}()->getRelated()->getTable();
            $idrel=$this->{$relationship}()->getExistenceCompareKey();
            $id=$this->{$relationship}()->getRelated()->getKeyName();
        }

        //Mk_debug::msgApi(['cacadedelete2',$table, $id]);
        //$this->{$relationship}()->
        $lids=DB::table($table)
               ->whereIn($idrel, $ids)
               ->select([$id])->get();
               $lids=array_column($lids->toArray(), $id);
        Mk_debug::msgApi(['cacadedelete2',$table, get_class($this->{$relationship}()->getRelated())]);
        Mk_debug::setGlobal(get_class($this->{$relationship}()->getRelated()),'cascadeDelete');
        $this->{$relationship}()->getRelated()->runCascadingDeletes($lids,$restore);
        DB::table($table)
               ->whereIn($idrel, $ids)
               ->update([$this->getDeletedAtColumn() =>  $dato]);
    }

    protected function hasInvalidCascadingRelationships()
    {
        return array_filter($this->getCascadingDeletes(), function ($relationship) {
            return  !method_exists($this, $relationship) ||  !$this->{$relationship}() instanceof Relation;
        });
    }

    protected function getCascadingDeletes()
    {
        return isset($this->cascadeDeletes) ? (array) $this->cascadeDeletes : [];
    }

    protected function getActiveCascadingDeletes()
    {
        return array_filter($this->getCascadingDeletes(), function ($relationship) {
            return ! is_null($this->{$relationship});
        });
    }
}
//TODO: hacer que el restore solo recupere los borrados en el momento del destroy en este habra que guardar la misma fecha en todos y al restaurar verificar que sea de la misma fecha
//TODO: Revisar cuando el token esta vencido que el frotn actue de acuerdo, tambien sino esta autenticado o no tiene permisos para mostrar ciertos datos
