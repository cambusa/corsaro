<?php 
/****************************************************************************
* Name:            quiversex.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_execlib.php";
include_once $tocambusa."ryquiver/quiverlib.php";
include_once $tocambusa."ryego/ego_validate.php";
include_once $tocambusa."rymonad/monad_lib.php";

// UTENTEID E RUOLOID COME SONO REGISTRATI IN QUIVER
$global_quiveruserid="";
$global_quiverroleid="";

// GESTIONE ERRORI
$babelcode="QVERR_UNKNOWN";
$babelparams=array();

// GENERAZIONE DI SYSID DI MASSA
$global_baseid="";
$global_progrid=0;

// DEFINIZIONE COSTANTI
define("QVSYS_PROGRLEN", 4);
define("QVSYS_PROGRMAX", 1679615);

function qv_getbaseid($maestro){
    global $url_rymonad;
    
    $lenbase=$maestro->lenid-QVSYS_PROGRLEN;
    
    // REPERISCO LA BASE PER I SYSID
    if($url_rymonad!=""){
        @$json=file_get_contents($url_rymonad."rymonad.php?l=".$lenbase."&f=1");
        $v=json_decode($json);
        $baseid=$v->SYSID;
    }
    else{
        $baseid=monadcall($lenbase, 0);
    }
    if(strlen($baseid)!=$lenbase){
        $babelcode="QVERR_MONADID";
        $b_params=array();
        $b_pattern="Fallito il reperimento della base univoca";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
    return $baseid;
}

function qv_bulkinitialize($maestro){
    global $global_baseid, $global_progrid;
    $global_baseid=qv_getbaseid($maestro);
    $global_progrid=0;
}

function qv_createsysid($maestro){
    global $babelcode, $babelparams;
    global $url_rymonad, $global_baseid, $global_progrid;
    // INIZIALIZZO SYSID
    $SYSID="";
    if($global_baseid!=""){
        // GENERAZIONE SU UNA BASE PRIVATA DEL PROCESSO (UTILE PER ACQUISIZIONI MASSICCE)
        if($global_progrid>QVSYS_PROGRMAX){
            qv_bulkinitialize($maestro);
        }
        $LASTBASE=$global_baseid;
        $LASTPROGR=$global_progrid;
        $global_progrid+=1;
    }
    else{
        // DETERMINO L'ULTIMO PROGRESSIVO GENERATO
        $sql="SELECT * FROM QVSYSTEM";
        maestro_query($maestro, $sql, $r);
        if(count($r)==0){
            // INIZIALIZZO IL SISTEMA
            $MONADID=str_repeat("0", $maestro->lenid);
            $LASTBASE=qv_getbaseid($maestro);
            $LASTPROGR=0;
            $sql="INSERT INTO QVSYSTEM(SYSID,LASTBASE,LASTPROGR) VALUES('$MONADID', '$LASTBASE', '$LASTPROGR')";
            maestro_execute($maestro, $sql);
        }
        else{
            // REPERISCO BASE UNIVOCA E PROGRESSIVO
            $LASTBASE=$r[0]["LASTBASE"];
            $LASTPROGR=intval($r[0]["LASTPROGR"]);
            // INCREMENTO IL PROGRESSIVO
            $LASTPROGR+=1;
            // CONTROLLO CHE NON SIANO ESAURITI
            if($LASTPROGR>QVSYS_PROGRMAX){
                $LASTBASE=qv_getbaseid($maestro);
                $LASTPROGR=0;
                $sql="UPDATE QVSYSTEM SET LASTBASE='$LASTBASE',LASTPROGR='$LASTPROGR'";
            }
            else{
                $sql="UPDATE QVSYSTEM SET LASTPROGR='$LASTPROGR'";
            }
            maestro_execute($maestro, $sql);
        }
    }
    // COSTRUISCO IL SYSID
    $SYSID=$LASTBASE.substr( str_repeat("0", QVSYS_PROGRLEN) . strtoupper(base_convert($LASTPROGR, 10, 36)), -QVSYS_PROGRLEN );
    
    if(strlen($SYSID)!=$maestro->lenid){
        $babelcode="QVERR_CREATEID";
        $b_params=array();
        $b_pattern="Fallita la creazione del codice univoco";
        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
    }
    return $SYSID;
}

function qv_babeltranslate($pattern, $params){
    global $babelcode, $babelparams, $global_lastlanguage;
    $i=1;
    if($global_lastlanguage!="default"){
        try{
            // CERCO DI REPERIRE IL PATTERN TRADOTTO
            // APERTURA DATABASE
            $maestro_babel=maestro_opendb($global_lastlanguage);

            if($maestro_babel->conn!==false){
                $ucode=strtoupper($babelcode);
                maestro_query($maestro_babel, "SELECT CAPTION FROM BABELITEMS WHERE SYSID='$ucode' OR [:UPPER(NAME)]='$ucode'", $r);
                if(count($r)>0){
                    $pattern=$r[0]["CAPTION"];
                }
            }

            // CHIUSURA DATABASE
            maestro_closedb($maestro_babel);
        }catch(Exception $e){}
    }
    foreach($params as $key=>$value){
        $pattern=str_replace("{".($i++)."}", $value, $pattern);
        $babelparams[$key]=$value;
    }
    return $pattern;
}

function qv_validatesession($maestro, $SESSIONID, $context=""){
    global $babelcode, $babelparams;
    global $public_sessionid,
           $ryque_sessionid,
           $global_lastuserid,
           $global_lastusername,
           $global_lastadmin,
           $global_lastemail,
           $global_lastroleid,
           $global_lastenvid,
           $global_lastlanguage,
           $global_lastcountrycode,
           $global_lastdebugmode,
           $global_lastclientip,
           $global_lastrolename;

    $ret=false;
           
    if($SESSIONID!=""){
        if($SESSIONID==$public_sessionid || ($SESSIONID==$ryque_sessionid && $context=="ryque")){
            $global_lastuserid=qv_actualid($maestro, "0SERVERID000");
            $global_lastusername="SERVER";
            $global_lastadmin=0;
            $global_lastemail="";
            $global_lastroleid=qv_actualid($maestro, "0NOROLEID000");
            $global_lastenvid="";
            $global_lastlanguage="";
            $global_lastcountrycode="";
            $global_lastdebugmode=false;
            $global_lastclientip=get_ip_address();
            $global_lastrolename="NOROLEID";
            $ret=true;
        }
        else{
            if($maestro->ego){
                // CANCELLO LE SESSIONI SCADUTE DALLA CACHE
                $sql="DELETE FROM QVSESSIONS WHERE [:TIME(RENEWALTIME,1HOURS)]<[:NOW()]";
                maestro_execute($maestro, $sql, false);
                
                // CONTROLLO CHE LA SESSIONE RICHIESTA ESISTA
                maestro_query($maestro,"SELECT * FROM QVSESSIONS WHERE SESSIONID='$SESSIONID'", $r);
                if(count($r)>0){
                    // CONTROLLO IP
                    $ip=$r[0]["CLIENTIP"];
                    $currip=get_ip_address();
                    if(isset($_SERVER['SERVER_ADDR']))
                        $serverip=$_SERVER['SERVER_ADDR'];
                    else
                        $serverip=$currip;
                    if($ip==$currip || $serverip==$currip){
                        $global_lastuserid=$r[0]["USERID"];
                        $global_lastusername=$r[0]["USERNAME"];
                        $global_lastadmin=intval($r[0]["ADMINISTRATOR"]);
                        $global_lastemail=$r[0]["EMAIL"];
                        $global_lastroleid=$r[0]["ROLEID"];
                        $global_lastlanguage=$r[0]["LANGUAGENAME"];
                        $global_lastcountrycode=$r[0]["COUNTRYCODE"];
                        $global_lastdebugmode=intval($r[0]["DEBUGMODE"]);
                        $global_lastclientip=$r[0]["CLIENTIP"];
                        $global_lastrolename=$r[0]["ROLENAME"];
                        $ret=true;
                    }
                }
                else{
                    // NON ESISTE: RICHIEDO LA VALIDAZIONE A EGO
                    if(ext_validatesession($SESSIONID, true, $context)){
                        if(qv_validateenviron($maestro, $context)){
                            // MEMORIZZO I DATI DI EGO
                            $SYSID=qv_createsysid($maestro);
                            $USERID=$global_lastuserid;
                            $USERNAME=ryqEscapize($global_lastusername);
                            if($global_lastadmin)
                                $ADMINISTRATOR="1";
                            else
                                $ADMINISTRATOR="0";
                            $EMAIL=ryqEscapize($global_lastemail);
                            $ROLEID=$global_lastroleid;
                            $LANGUAGENAME=$global_lastlanguage;
                            $COUNTRYCODE=$global_lastcountrycode;
                            $DEBUGMODE=$global_lastdebugmode;
                            $CLIENTIP=$global_lastclientip;
                            $ROLENAME=ryqEscapize($global_lastrolename);
                            $RENEWALTIME="[:NOW()]";

                            $sql="INSERT INTO QVSESSIONS(SYSID,SESSIONID,USERID,USERNAME,ADMINISTRATOR,EMAIL,ROLEID,LANGUAGENAME,COUNTRYCODE,DEBUGMODE,CLIENTIP,ROLENAME,RENEWALTIME) VALUES('$SYSID','$SESSIONID','$USERID','$USERNAME','$ADMINISTRATOR','$EMAIL','$ROLEID','$LANGUAGENAME','$COUNTRYCODE','$DEBUGMODE','$CLIENTIP','$ROLENAME',$RENEWALTIME)";
                            maestro_execute($maestro, $sql, false);
                            $ret=true;
                        }
                    }
                }
            }
            else{
                $ret=ext_validatesession($SESSIONID, false, $context);
            }
        }
    }
    return $ret;
}
function qv_validateenviron($maestro, $context){
    global $global_lastenvid;
    $ret=true;
    if($context=="quiver"){
        $envid=qv_setting($maestro, "_ENVIRONID", "######");
        if($envid=="######"){
            $sysid=qv_createsysid($maestro);
            $sql="INSERT INTO QVSETTINGS(SYSID,NAME,DESCRIPTION,DATATYPE,DATAVALUE,TAG) VALUES('$sysid', '_ENVIRONID', 'Identificatore ambiente', 'STRING', '$global_lastenvid', 'SYSTEM')";
            maestro_execute($maestro, $sql, false);
        }
        elseif($envid==""){
            $sql="UPDATE QVSETTINGS SET DATAVALUE='$global_lastenvid' WHERE [:UPPER(NAME)]='_ENVIRONID'";
            maestro_execute($maestro, $sql, false);
        }
        elseif($envid!=$global_lastenvid){
            $ret=false;
        }
    }
    return $ret;
}
?>