<?php 
/****************************************************************************
* Name:            quiverext.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_extension($maestro, $data, $prefix, $SYSID, $TYPOLOGYID, $oper){
    global $babelcode, $babelparams, $global_cache;
    if($oper!=2){   // 0 insert, 1 update, 2 virtual delete, 3 delete
        $tabletypes=$prefix."TYPES";
        $tableviews=$prefix."VIEWS";
        if($t=_qv_cacheloader($maestro, $tabletypes, $TYPOLOGYID)){
            $TABLENAME=$t["TABLENAME"];
            $DELETABLE=$t["DELETABLE"];
            unset($t);
            if($TABLENAME!=""){
                if($oper==3){
                    if($DELETABLE)
                        $sql="DELETE FROM $TABLENAME WHERE SYSID='$SYSID'";
                    else
                        $sql="UPDATE $TABLENAME SET SYSID=NULL WHERE SYSID='$SYSID'";
                    if(!maestro_execute($maestro, $sql, false)){
                        $babelcode="QVERR_EXECUTE";
                        $trace=debug_backtrace();
                        $b_params=array("FUNCTION" => $trace[0]["function"] );
                        $b_pattern=$maestro->errdescr;
                        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                    }
                }
                elseif($DELETABLE || $oper==1){
                    if(isset($global_cache["$tableviews-$TYPOLOGYID"])){
                        // REPERISCO LE INFO DALLA CACHE
                        $infos=$global_cache["$tableviews-$TYPOLOGYID"];
                    }
                    else{
                        // REPERISCO LE INFO DAL DATABASE
                        $infos=array();
                        maestro_query($maestro,"SELECT * FROM $tableviews WHERE TYPOLOGYID='$TYPOLOGYID'",$r);
                        for($i=0;$i<count($r);$i++){
                            $FIELDTYPE=$r[$i]["FIELDTYPE"];
                            $TABLEREF="";
                            $NOTEMPTY=false;
                            if(substr($FIELDTYPE,0,5)=="SYSID"){
                                $TABLEREF=substr($FIELDTYPE, 6, -1);
                                if(substr($TABLEREF,0,1)=="#"){
                                    $NOTEMPTY=true;
                                    $TABLEREF=substr($TABLEREF,1);
                                }
                            }
                            $infos[$i]["FIELDNAME"]=$r[$i]["FIELDNAME"];
                            $infos[$i]["FIELDTYPE"]=$FIELDTYPE;
                            $infos[$i]["FORMULA"]=$r[$i]["FORMULA"];
                            $infos[$i]["WRITABLE"]=intval($r[$i]["WRITABLE"]);
                            $infos[$i]["TABLEREF"]=$TABLEREF;
                            $infos[$i]["NOTEMPTY"]=$NOTEMPTY;
                        }
                        // INSERISCO LE INFO NELLA CACHE
                        $global_cache["$tableviews-$TYPOLOGYID"]=$infos;
                    }
                    if(count($infos)>0 || $oper==0){    // Ci sono campi estensione oppure solo il SYSID in inserimento
                        if($oper==0){
                            $columns="SYSID";
                            $values="'$SYSID'";
                            $helpful=true;
                        }
                        else{
                            $sets="";
                            $where="SYSID='$SYSID'";
                            $helpful=false;
                        }
                        $clobs=false;
                        for($i=0;$i<count($infos);$i++){
                            $FIELDNAME=$infos[$i]["FIELDNAME"];
                            $FIELDTYPE=$infos[$i]["FIELDTYPE"];
                            $FORMULA=$infos[$i]["FORMULA"];
                            $WRITABLE=$infos[$i]["WRITABLE"];
                            $TABLEREF=$infos[$i]["TABLEREF"];
                            $NOTEMPTY=$infos[$i]["NOTEMPTY"];
                            if($WRITABLE){
                                // IL CAMPO PUO' ESSERE AGGIORNATO
                                
                                // DETERMINO IL NOME DEL CAMPO
                                if($FORMULA=="")
                                    $ACTUALNAME=$FIELDNAME;
                                else
                                    $ACTUALNAME=str_replace("$TABLENAME.", "", $FORMULA);

                                // DETERMINO IL VALORE DEL CAMPO
                                if(isset($data[$ACTUALNAME])){
                                    // FORMATTAZIONE IN BASE AL TIPO
                                    if($TABLEREF!=""){    // SYSID(Tabella referenziata)
                                        $value=ryqEscapize($data[$ACTUALNAME]);
                                        if($value!=""){
                                            // Controllo che esista l'oggetto con SYSID=$value nella tabella $TABLEREF
                                            qv_linkable($maestro, $TABLEREF, $value);
                                        }
                                        elseif($NOTEMPTY){
                                            $babelcode="QVERR_EMPTYSYSID";
                                            $trace=debug_backtrace();
                                            $b_params=array("FUNCTION" => $trace[0]["function"], "ACTUALNAME" => $ACTUALNAME );
                                            $b_pattern="Campo [{2}] obbligatorio";
                                            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                                        }
                                        $value="'$value'";
                                    }
                                    elseif($FIELDTYPE=="TEXT"){
                                        $value=ryqNormalize($data[$ACTUALNAME]);
                                        qv_setclob($maestro, $ACTUALNAME, $value, $value, $clobs);
                                    }
                                    elseif(substr($FIELDTYPE, 0, 4)=="JSON"){
                                        $value=ryqNormalize($data[$ACTUALNAME]);
                                        if(substr($FIELDTYPE, 0, 5)=="JSON("){
                                            $len=intval(substr($FIELDTYPE, 5));
                                            $value=substr($value, 0, $len);
                                        }
                                        if($value!=""){
                                            if(!json_decode($value)){
                                                $babelcode="QVERR_JSON";
                                                $trace=debug_backtrace();
                                                $b_params=array("FUNCTION" => $trace[0]["function"], "ACTUALNAME" => $ACTUALNAME );
                                                $b_pattern="Documento JSON [{2}] non corretto o troppo esteso";
                                                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                                            }
                                        }
                                        qv_setclob($maestro, $ACTUALNAME, $value, $value, $clobs);
                                    }
                                    else{
                                        $value=ryqEscapize($data[$ACTUALNAME]);
                                        $value=qv_sqlize($value, $FIELDTYPE);
                                    }
                                    if($oper==0){
                                        qv_appendcomma($columns, $ACTUALNAME);
                                        qv_appendcomma($values, $value);
                                    }
                                    else{
                                        qv_appendcomma($sets,"$ACTUALNAME=$value");
                                        $helpful=true;
                                    }
                                }
                                else{
                                    if($oper==0){
                                        if($NOTEMPTY){
                                            $babelcode="QVERR_EMPTYSYSID";
                                            $trace=debug_backtrace();
                                            $b_params=array("FUNCTION" => $trace[0]["function"], "ACTUALNAME" => $ACTUALNAME );
                                            $b_pattern="Campo [{2}] obbligatorio";
                                            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                                        }
                                        $value=qv_sqlize("", $FIELDTYPE);
                                        qv_appendcomma($columns, $ACTUALNAME);
                                        qv_appendcomma($values, $value);
                                    }
                                }
                            }
                        }
                        unset($r);
                        if($helpful){
                            if($oper==0)
                                $sql="INSERT INTO $TABLENAME ($columns) VALUES($values)";
                            else
                                $sql="UPDATE $TABLENAME SET $sets WHERE $where";
                            if(!maestro_execute($maestro, $sql, false, $clobs)){
                                $babelcode="QVERR_EXECUTE";
                                $trace=debug_backtrace();
                                $b_params=array("FUNCTION" => $trace[0]["function"] );
                                $b_pattern=$maestro->errdescr;
                                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                            }
                        }
                    }
                }
            }
        }
    }
}

function qv_sqlize($value, $FIELDTYPE){
    $FIELDTYPE=strtoupper($FIELDTYPE);
    switch($FIELDTYPE){
    case "INTEGER":
        $value=strval(intval($value));
        break;
    case "RATIONAL":
        $value=strval(round(floatval($value),7));
        break;
    case "DATE":
        if(strlen($value)<8)
            $value=LOWEST_DATE;
        $value="[:DATE($value)]";
        break;
    case "TIMESTAMP":
        if(strlen($value)<8)
            $value=LOWEST_TIME;
        $value="[:TIME($value)]";
        break;
    case "BOOLEAN":
        if(intval($value)!=0)
            $value="1";
        else
            $value="0";
        break;
    default:
        if(substr($FIELDTYPE, 0, 9)=="RATIONAL("){
            $dec=intval(substr($FIELDTYPE, 9));
            $value=strval(round(floatval($value), $dec));
        }
        elseif(substr($FIELDTYPE, 0, 5)=="CHAR("){
            $len=intval(substr($FIELDTYPE, 5));
            $value="'".substr(qv_inputUTF8($value), 0, $len)."'";
        }
        elseif(substr($value, 0, 2)!="[:" || substr($value, -1, 1)!="]"){
            $value="'$value'";
        }
        break;
    }
    return $value;
}

function _qv_historicizing($maestro, $prefix, $SYSID, $TYPOLOGYID, $oper){
    global $global_quiveruserid, $global_quiverroleid;
    global $babelcode, $babelparams;
    
    $tablebase=$prefix."S";
    $tabletypes=$prefix."TYPES";
    if($t=_qv_cacheloader($maestro, $tabletypes, $TYPOLOGYID)){
        if( intval($t["HISTORICIZING"]) ){
            $TABLE=$t["VIEWNAME"];
            if($TABLE==""){
                $TABLE=$tablebase;
            }
            if($r=_qv_cacheloader($maestro, $TABLE, $SYSID)){
                $clobs=false;
                $DATABAG=json_encode($r);
                qv_setclob($maestro, "DATABAG", $DATABAG, $DATABAG, $clobs);
                
                $HISTORYID=qv_createsysid($maestro);
                $DESCRIPTION=ryqEscapize($r["DESCRIPTION"]);
                $TIMEINSERT=qv_strtime($r["TIMEINSERT"]);
                $RECORDTIME=qv_strtime($r["TIMEUPDATE"]);
                if($RECORDTIME<$TIMEINSERT){
                    $RECORDTIME=$TIMEINSERT;
                }
                $RECORDTIME="[:TIME($RECORDTIME)]";
                $EVENTTIME="[:NOW()]";
                    
                // PREDISPONGO COLONNE E VALORI DA REGISTRARE
                $columns="SYSID,RECORDID,DESCRIPTION,RECORDTIME,TABLEBASE,TYPOLOGYID,OPERTYPE,ROLEID,USERID,EVENTTIME,DATABAG";
                $values="'$HISTORYID','$SYSID','$DESCRIPTION',$RECORDTIME,'$tablebase','$TYPOLOGYID','$oper','$global_quiverroleid','$global_quiveruserid',$EVENTTIME,$DATABAG";
                $sql="INSERT INTO QVHISTORY($columns) VALUES($values)";

                if(!maestro_execute($maestro, $sql, false, $clobs)){
                    $babelcode="QVERR_EXECUTE";
                    $trace=debug_backtrace();
                    $b_params=array("FUNCTION" => $trace[0]["function"] );
                    $b_pattern=$maestro->errdescr;
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
        }
    }
}
?>