<?php 
/****************************************************************************
* Name:            quiverlib.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/quiversex.php";
include_once $path_cambusa."rygeneral/writelog.php";

// Cache views delle estensioni dati
$global_cache=array();

// Cache dei dati estesi puntatori a record
$global_pointers=array();

// Cache dei record
$global_cacherecord=array();

// Spacename corrente
$global_spacename="";

define("LOWEST_TIME",  "19000101000000");
define("LOWEST_DATE",  "19000101");
define("HIGHEST_TIME", "99991231000000");
define("HIGHEST_DATE", "99991231");

function qv_appendcomma(&$sql,$chunk){
    if($sql=="")
        $sql=$chunk;
    else
        $sql.=",".$chunk;
}

function qv_setting($maestro, $settingname, $default=""){
    // REPERISCO IL SETTAGGIO SPECIFICATO E LO CONVERTO 
    $settingname=strtoupper($settingname);
    maestro_query($maestro,"SELECT DATAVALUE,DATATYPE FROM QVSETTINGS WHERE [:UPPER(NAME)]='$settingname'", $r);
    if(count($r)==1){
        $value=$r[0]["DATAVALUE"];
        switch( strtoupper($r[0]["DATATYPE"]) ){
        case "INTEGER":
            $value=intval($value);break;
        case "RATIONAL":
            $value=floatval($value);break;
        case "BOOLEAN":
            $value=(bool)intval($value);break;
        }
    }
    else{
        $value=$default;
    }
    return $value;
}

function qv_checkname($maestro, $table, $SYSID, &$NAME){
    global $babelcode, $babelparams;
    // IMPONGO CHE NON ABBIA UN FORMATO RISERVATO O CHE NON SIA VUOTO ASSIEME AL SYSID
    if( substr($NAME,0,2)=="__" || ($SYSID=="" && trim($NAME)=="") || preg_match("/[ ,-]/", $NAME)==1 ){
        $babelcode="QVERR_INVALIDNAME";
        $b_params=array("NAME" => $NAME);
        $b_pattern="Nome non valido";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
    // SE E' VUOTO LO IMPOSTO IN BASE AL SYSID
    if(trim($NAME)==""){
        $NAME="__$SYSID";
    }
    // CONTROLLO L'UNICITA'
    maestro_query($maestro,"SELECT SYSID FROM $table WHERE SYSID<>'$SYSID' AND [:UPPER(NAME)]='".strtoupper($NAME)."'",$r);
    if(count($r)>0){
        $babelcode="QVERR_NOTUNIQUE";
        $b_params=array("NAME" => $NAME, "table" => $table);
        $b_pattern="Nome [{1}] non univoco in [{2}]";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}

function qv_linkable($maestro, $table, $SYSID){
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
    maestro_query($maestro,"SELECT SYSID FROM $table WHERE SYSID='$SYSID' $deleted", $r);
    if(count($r)==0){
        $babelcode="QVERR_NOREF";
        $b_params=array("SYSID" => $SYSID, "table" => $table);
        $b_pattern="Riferimento non trovato in [{2}]";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}

function qv_modifiabletype($maestro, $table, $typology, $field){
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
    maestro_query($maestro,"SELECT {AS:TOP 1} SYSID FROM $table WHERE TYPOLOGYID='$typology' AND $field<>'' $deleted {LM:LIMIT 1}{O: AND ROWNUM=1}{D:FETCH FIRST 1 ROWS ONLY}",$r);
    if(count($r)>0){
        $babelcode="QVERR_USEDTYPE";
        $b_params=array("field" => $field, "table" => $table, "typology" => $typology);
        $b_pattern="Impossibile modificare una tipologia in uso";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}

function qv_solverecord($maestro, $data, $table, $postid, $postname, &$SYSID, $fields=false){
    global $babelcode, $babelparams;
    // INDIVIDUA UN RECORD PER "SYSID" O PER "NAME"
    // A PARTIRE DAI DATI PASSATI IN $data
    // SE ENTRAMBI MANCANO LA FUNZIONE RESTITUISCE "false"
    // SE "SYSID" E' PASSATO PIENO VIENE USATO PER LEGGERE E VIENE VALIDATO
    // SE "SYSID" E' PASSATO VUOTO (POTREBBE VOLER DIRE "LASCIARE SYSID VUOTO") LA FUNZIONE RESTITUISCE "true"
    // ALTRIMENTI SE E' PASSATO "NAME" VIENE USATO PER LEGGERE E VIENE VALIDATO
    // INIZIALIZZO I VALORI IN USCITA
    $SYSID="";
    $values=false;
    // STABILISCO SE TRA I VINCOLI DEVO IMPORRE CHE IL RECORD NON SIA CANCELLATO
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
    // INIZIALIZZO I CAMPI DA ESTRARRE; SE $postname=="" LA TABELLA NON HA IL CAMPO "NAME"
    if($postname!="")
        $select="SYSID,NAME";
    else
        $select="SYSID";
    // PREDISPONGO LA SELECT
    if($fields){
        if($fields!="*"){
            $fields=explode(",", $fields);
            foreach($fields as $key)
                $select.=",$key";
        }
        else{
            $select="*";
        }
    }
    if(isset($data[$postid])){
        // CERCO PER SYSID
        $SYSID=ryqEscapize($data[$postid]);
        if($SYSID!=""){ // SYSID E' PIENO
            maestro_query($maestro,"SELECT $select FROM $table WHERE SYSID='$SYSID' $deleted", $r);
            if(count($r)==1){   // PRETENDO CHE ESISTA
                if($fields){
                    if(is_array($fields)){
                        $values=array();
                        foreach($fields as $key)
                            $values[$key]=$r[0][$key];
                    }
                    else{
                        $values=$r[0];
                    }
                }
                else{
                    $values=true;
                }
            }
            else{
                $babelcode="QVERR_NOREF";
                $b_params=array("SYSID" => $SYSID, "table" => $table);
                $b_pattern="Riferimento [{1}] non trovato in [{2}]";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{   // SYSID E' VUOTO: CHI CHIAMA SAPRA' SE ACCETTARLO O NO
            $values=true;
        }
    }
    elseif($postname!=""){  // LA TABELLA HA UN CAMPO "NAME"
        if(isset($data[$postname])){    // VOGLIO CERCARE PER NOME
            $NAME=ryqEscapize($data[$postname]);
            if($NAME!=""){
                maestro_query($maestro,"SELECT $select FROM $table WHERE [:UPPER(NAME)]='".strtoupper($NAME)."' $deleted",$r);
                if(count($r)==1){   // PRETENDO CHE ESISTA
                    $SYSID=$r[0]["SYSID"];
                    if($fields){
                        if(is_array($fields)){
                            $values=array();
                            foreach($fields as $key)
                                $values[$key]=$r[0][$key];
                        }
                        else{
                            $values=$r[0];
                        }
                    }
                }
                else{
                    $babelcode="QVERR_NONAME";
                    $b_params=array("NAME" => $NAME, "table" => $table);
                    $b_pattern="Nome [{1}] non trovato in [{2}]";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
            else{
                $babelcode="QVERR_EMPTYNAME";
                $b_params=array("table" => $table);
                $b_pattern="Nome non specificato per [{1}]";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
    }
    return $values;
}

function qv_solvetimeunit($maestro, $TYPOLOGYID, &$TIMEUNIT){
    global $babelcode, $babelparams;
    maestro_query($maestro,"SELECT TIMEUNIT FROM QVOBJECTTYPES WHERE SYSID='$TYPOLOGYID'",$r);
    if(count($r)==1){
        $TIMEUNIT=$r[0]["TIMEUNIT"];
    }
    else{
        $babelcode="QVERR_NOTYPOLOGY";
        $b_params=array("SYSID" => $TYPOLOGYID, "table" => "QVOBJECTTYPES");
        $b_pattern="Tipologia non trovata";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}

function qv_solverounding($maestro, $GENREID, &$ROUNDING){
    global $babelcode, $babelparams;
    $ROUNDING=0;
    maestro_query($maestro,"SELECT ROUNDING FROM QVGENRES WHERE SYSID='$GENREID'",$r);
    if(count($r)==1){
        $ROUNDING=$r[0]["ROUNDING"];
    }
    else{
        $babelcode="QVERR_NOGENRE";
        $b_params=array("GENREID" => $GENREID, "table" => "QVGENRES");
        $b_pattern="Genere non trovato";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}

function qv_solvelife($maestro, $table, $SYSID, &$BEGINTIME, &$ENDTIME){
    global $babelcode, $babelparams;
    maestro_query($maestro,"SELECT BEGINTIME,ENDTIME FROM $table WHERE SYSID='$SYSID'",$r);
    if(count($r)==1){
        $BEGINTIME=strtr( $r[0]["BEGINTIME"], array("-" => "", ":" => "", "T" => "", " " => "") );
        $ENDTIME=strtr( $r[0]["ENDTIME"], array("-" => "", ":" => "", "T" => "", " " => "") );
        // NORMALIZZO COME TIME
        $BEGINTIME=substr($BEGINTIME."000000", 0, 14);
        $ENDTIME=substr($ENDTIME."000000", 0, 14);
    }
    else{
        $babelcode="QVERR_NORECORD";
        $b_params=array("SYSID" => $SYSID, "table" => $table);
        $b_pattern="Record non trovato";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
}

function qv_strtime($sqltime){
    $sqltime=strtr( $sqltime, array("-" => "", ":" => "", "T" => "", " " => "", "'" => "") );
    return substr($sqltime."000000", 0, 14);
}

function qv_strdate($sqltime){
    $sqltime=strtr( $sqltime, array("-" => "", ":" => "", "T" => "", " " => "", "'" => "") );
    return substr($sqltime."000000", 0, 8);
}
function qv_getrecord($maestro, $table, $SYSID, $fields="*"){
    global $babelcode, $babelparams;
    $values=false;
    // STABILISCO SE TRA I VINCOLI DEVO IMPORRE CHE IL RECORD NON SIA CANCELLATO
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
    // PREDISPONGO LA SELECT
    if($fields!="*"){
        $fields=explode(",", $fields);
        $select="SYSID";
        foreach($fields as $key)
            $select.=",$key";
    }
    else{
        $select="*";
    }
    maestro_query($maestro,"SELECT NAME $select FROM $table WHERE SYSID='$SYSID' $deleted", $r);
    if(count($r)==1){
        if(is_array($fields)){
            $values=array();
            foreach($fields as $key)
                $values[$key]=$r[0][$key];
        }
        else{
            $values=$r[0];
        }
    }
    else{
        $babelcode="QVERR_NOREF";
        $b_params=array("SYSID" => $SYSID, "table" => $table);
        $b_pattern="Riferimento [{1}] non trovato in [{2}]";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
    return $values;
}

function qv_journal($env, $text){
    global $path_databases;
    try{
        $pathfile=$path_databases . "_syslog/" . $env . "-" . date("Y-m-d") . ".log";
        $pt=fopen($pathfile, "a");
        $text=str_replace("\r\n", " ", $text);
        $text=str_replace("\r", " ", $text);
        $text=str_replace("\n", " ", $text);
        $text.="\r\n";
        fwrite($pt, $text);
        fclose($pt);
    }
    catch(Exception $e){}
}
function qv_escapizetime($var, $def){
    $var=str_replace("'", "''", strtr(trim($var), array("\'" => "'", "\\\"" => "\"", "\\\\" => "\\")));
    if($var=="")
        $var=$def;
    else
        $var=substr($var."000000", 0, 14);
    return $var;
}
function qv_actualid($maestro, $id){
    if($id!=""){
        $l=$maestro->lenid;
        if(strlen($id)!=$l){
            $id=substr($id . str_repeat("0", $l), 0, $l);
        }
    }
    return $id;
}
function qv_escapizeUTF8(&$v){
    $v=html_entity_decode(utf8_decode($v));
}
function qv_inputUTF8($v){
    if($v!=""){
        if(!mb_check_encoding($v, "UTF-8")){
            // CI SONO CARATTERI NON UNICODE
            return utf8_encode($v);
        }
    }
    return $v;
}
function qv_striptags(&$v){
    $v=strip_tags($v);
}
function qv_setclob($maestro, $id, $value, &$set, &$clobs){
    $value=qv_inputUTF8($value);
    // VALUE DEVE ESSERE SENZA APICI E NON ESCAPIZZATO
    switch($maestro->provider){
    case "oracle":
        if($clobs===false){
            $clobs=array();
        }
        $set=":REGISTRY";
        $clobs[$id]=ryqNormalize($value);
        break;
    case "db2odbc":
        if($clobs===false){
            $clobs=array();
        }
        $set="?";
        $clobs[$id]=ryqNormalize($value);
        break;
    case "mssql":  // mosca mssql
        if($clobs===false){
            $clobs=array();
        }
        $set="?";
        $clobs[]=ryqNormalize($value);
        break;
    default:
        $set="'".ryqEscapize($value)."'";
    }
}
function _qv_cacheloader($maestro, $table, $SYSID){
    global $global_cacherecord;
    
    $record=false;
    
    if(isset($global_cacherecord[$SYSID][$table])){
        $record=$global_cacherecord[$SYSID][$table];
    }
    else{
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
        maestro_query($maestro,"SELECT * FROM $table WHERE SYSID='$SYSID' $deleted", $r);
        if(count($r)==1){
            $record=$r[0];
            $global_cacherecord[$SYSID]=array($table => $record);
        }
    }
    return $record;
}
function _qv_cacheremove($maestro, $SYSID){
    global $global_cacherecord;
    unset($global_cacherecord[$SYSID]);
}
function _qv_cacheempty($maestro){
    global $global_cacherecord;
    $global_cacherecord=array();
}
?>