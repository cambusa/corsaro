<?php 
/****************************************************************************
* Name:            quiverval.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_validateobject($maestro, &$data, $SYSID, $TYPOLOGYID, $oper){
    global $global_lastusername,$global_lastrolename,$path_customize,$path_applications;
    global $babelcode, $babelparams;
    $prevdata=false;
    $ret=qv_extravalidate($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "app", "object", $prevdata);
    if($ret===1){
        $ret=qv_extravalidate($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "cust", "object", $prevdata);
    }
    return $ret;
}
function qv_validatearrow($maestro, &$data, $SYSID, $TYPOLOGYID, $oper){
    global $global_lastusername,$global_lastrolename,$path_customize;
    global $babelcode, $babelparams;
    $prevdata=false;
    $ret=qv_extravalidate($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "app", "arrow", $prevdata);
    if($ret===1){
        $ret=qv_extravalidate($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "cust", "arrow", $prevdata);
    }
    return $ret;
}
function qv_validatequiver($maestro, &$data, $SYSID, $TYPOLOGYID, $oper){
    global $global_lastusername,$global_lastrolename,$path_customize;
    global $babelcode, $babelparams;
    $prevdata=false;
    $ret=qv_extravalidate($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "app", "quiver", $prevdata);
    if($ret===1){
        $ret=qv_extravalidate($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "cust", "quiver", $prevdata);
    }
    return $ret;
}
function qv_validategenre($maestro, &$data, $SYSID, $TYPOLOGYID, $oper){
    global $global_lastusername,$global_lastrolename,$path_customize;
    global $babelcode, $babelparams;
    $prevdata=false;
    $ret=qv_extravalidate($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "app", "genre", $prevdata);
    if($ret===1){
        $ret=qv_extravalidate($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "cust", "genre", $prevdata);
    }
    return $ret;
}

function qv_validatemotive($maestro, &$data, $SYSID, $TYPOLOGYID, $oper){
    global $global_lastusername,$global_lastrolename,$path_customize;
    global $babelcode, $babelparams;
    $prevdata=false;
    $ret=qv_extravalidate($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "app", "motive", $prevdata);
    if($ret===1){
        $ret=qv_extravalidate($maestro, $data, $SYSID, $TYPOLOGYID, $oper, "cust", "motive", $prevdata);
    }
    return $ret;
}
function _qv_storeddata($maestro, $SYSID, $TYPOLOGYID, $oper, $funct, $prefix, &$prevdata){
    // REPERISCO I DATI COME SONO REGISTRATI
    if($oper>0){
        $tabletype=$prefix."TYPES";
        $table=$prefix."S";
        // REPERSICO IL NOME DELLA VIEW
        $view="";
        if($t=_qv_cacheloader($maestro, $tabletype, $TYPOLOGYID))
            $view=$t["VIEWNAME"];
        if($view=="")
            $view=$table;
        // REPERSICO IL RECORD
        if($r=_qv_cacheloader($maestro, $view, $SYSID))
            $prevdata=$r;
        else
            $prevdata=array();
    }
    else{
        $prevdata=array();
    }
}
function qv_extravalidate($maestro, &$data, $SYSID, $TYPOLOGYID, $oper, $position, $entity, &$prevdata){
    global $global_lastusername,$global_lastrolename,$path_customize,$path_applications;
    global $babelcode, $babelparams;
    
    $ret=true;
    
    switch($position){
    case "app":$dir=$path_applications;break;
    case "cust":$dir=$path_customize;break;
    }
    switch($entity){
    case "object":$prefix="QVOBJECT";break;
    case "genre":$prefix="QVGENRE";break;
    case "motive":$prefix="QVMOTIVE";break;
    case "arrow":$prefix="QVARROW";break;
    case "quiver":$prefix="QVQUIVER";break;
    }
    $inc=$dir."ryquiver/".$position."validate".$entity.".php";
    if(is_file($inc)){
        include_once $inc;
        $funct=$position."validate".$entity;
        if(function_exists($funct)){
            // REPERISCO I DATI COME SONO REGISTRATI
            _qv_storeddata($maestro, $SYSID, $TYPOLOGYID, $oper, $funct, $prefix, $prevdata);
            $babelcode="";
            $failure="";
            $ret=$funct($maestro, $data, $prevdata, $SYSID, $TYPOLOGYID, $oper, $global_lastusername, $global_lastrolename, $babelcode, $failure);
            if($ret==0){
                if($babelcode=="")
                    $babelcode="QVERR_USERDEFINED";
                if($failure=="")
                    $failure="Fallita validazione per motivi non specificati";
                $b_params=array();
                $b_pattern=$failure;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
    }
    return $ret;
}
function qv_cyclicity($maestro, $table, $field, $SYSID, $REFID){
    global $babelcode, $babelparams;
    // ELENCO DI SICUREZZA PER EVITARE CICLI INFINITI
    // MA SE LE REGISTRAZIONE PRECEDENTI SONO STATE REGOLARI NON PUO' SUCCEDERE
    $list="";
    // CICLICITA' BANALE
    if($SYSID==$REFID){
        $babelcode="QVERR_CYCLICITY";
        $b_params=array("REFID" => $REFID);
        $b_pattern="Riferimenti circolari";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
    // CICLICITA' PROFONDA
    while($REFID!=""){
        maestro_query($maestro,"SELECT $field FROM $table WHERE SYSID='$REFID'",$r);
        if(count($r)==1){
            $REFID=$r[0][$field];
            if($REFID!=""){
                if(strpos($list, $REFID)===false)
                    $list.="|".$REFID;
                else
                    $REFID="";  // QUALCOSA E' STATO SOFISTICATO NELLA GERARCHIA
                if($SYSID==$REFID){
                    $babelcode="QVERR_CYCLICITY";
                    $b_params=array("REFID" => $REFID);
                    $b_pattern="Riferimenti circolari";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
        }
        else{
            $REFID="";
        }
    }
}
function qv_recordexists($maestro, $table, $where){
    global $babelcode, $babelparams;
    switch($table){
    case "QVGENRES":
    case "QVOBJECTS":
    case "QVMOTIVES":
    case "QVARROWS":
    case "QVQUIVERS":
    case "QVFILES":
        $deleted="AND DELETED=0";
        break;
    default:
        $deleted="";
    }
    // CERCO IL SYSID NELLA TABELLA SPECIFICATA
    maestro_query($maestro,"SELECT {AS:TOP 1} SYSID FROM $table WHERE $where $deleted {LM:LIMIT 1}{O: AND ROWNUM=1}{D:FETCH FIRST 1 ROWS ONLY}", $r);
    return (count($r)>0);
}
function qv_uniquity($maestro, $table, $SYSID, $where){
    global $babelcode, $babelparams;
    switch($table){
    case "QVGENRES":
    case "QVOBJECTS":
    case "QVMOTIVES":
    case "QVARROWS":
    case "QVQUIVERS":
    case "QVFILES":
        $deleted="AND DELETED=0";
        break;
    default:
        $deleted="";
    }
    // CERCO IL SYSID NELLA TABELLA SPECIFICATA
    maestro_query($maestro,"SELECT {AS:TOP 1} SYSID FROM $table WHERE SYSID<>'$SYSID' AND ($where) $deleted {LM:LIMIT 1}{O: AND ROWNUM=1}{D:FETCH FIRST 1 ROWS ONLY}", $r);
    return (count($r)==0);
}
function qv_actualvalue($data, $prevdata, $id){
    if(isset($data[$id]))
        return ryqEscapize($data[$id]);
    elseif(isset($prevdata[$id]))
        return ryqEscapize($prevdata[$id]);
    else
        return "";
}
?>