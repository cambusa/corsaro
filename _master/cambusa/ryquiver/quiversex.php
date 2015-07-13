<?php 
/****************************************************************************
* Name:            quiversex.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$path_cambusa=realpath(dirname(__FILE__)."/..");
$path_cambusa=str_replace("\\", "/", $path_cambusa);
$path_cambusa.="/";
include_once $path_cambusa."rymaestro/maestro_execlib.php";
include_once $path_cambusa."ryquiver/quiverlib.php";
include_once $path_cambusa."ryego/ego_validate.php";
include_once $path_cambusa."rymonad/monad_lib.php";

// UTENTEID E RUOLOID COME SONO REGISTRATI IN QUIVER
$global_quiveruserid="";
$global_quiverroleid="";

// GESTIONE ERRORI
$babelcode="QVERR_UNKNOWN";
$babelparams=array();

// GENERAZIONE DI SYSID DI MASSA
$global_baseid="";
$global_progrid=0;

// GENERAZIONE SYSID PREALLOCATI
$global_preallocbase="";
$global_preallocmax=0;
$global_preallocprogr=0;
$global_preallocauto=10;

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
    
    // DETERMINO L'ULTIMO PROGRESSIVO GENERATO
    $sql="SELECT * FROM QVSYSTEM";
    maestro_query($maestro, $sql, $r);
    if(count($r)>0)
        $LASTBASE=$r[0]["LASTBASE"];
    else
        $LASTBASE="";
        
    $TESTBASE=qv_getbaseid($maestro);
    if($TESTBASE<=$LASTBASE){
        // PER QUALCHE MOTIVO IL MONAD ATTUALE NON E' QUELLO CHE HA GENERATO L'ULTIMO BASEID
        monadset($LASTBASE);
        $TESTBASE=qv_getbaseid($maestro);
    }
    $global_baseid=$TESTBASE;
    $global_progrid=0;
}

function qv_preallocauto($alloc){
    global $global_preallocauto;
    $global_preallocauto=$alloc;
}

function qv_createsysid($maestro, $alloc=0){
    global $babelcode, $babelparams;
    global $url_rymonad, $global_baseid, $global_progrid;
    global $global_preallocbase, $global_preallocmax, $global_preallocprogr, $global_preallocauto;
    
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
        if($global_preallocprogr==0){
            if($alloc==0){
                $alloc=$global_preallocauto;
            }
            // DETERMINO L'ULTIMO PROGRESSIVO GENERATO
            $sql="SELECT * FROM QVSYSTEM";
            maestro_query($maestro, $sql, $r);
            if(count($r)==0){
                // INIZIALIZZO IL SISTEMA
                $MONADID=str_repeat("0", $maestro->lenid);
                // DETERMINO L'ULTIMO ALLOCATO
                if($alloc>0)
                    $LASTALLOC=$alloc-1;
                else
                    $LASTALLOC=0;
                $LASTBASE=qv_getbaseid($maestro);
                $LASTPROGR=0;
                $sql="INSERT INTO QVSYSTEM(SYSID,LASTBASE,LASTPROGR) VALUES('$MONADID', '$LASTBASE', '$LASTALLOC')";
                maestro_execute($maestro, $sql);
            }
            else{
                // REPERISCO BASE UNIVOCA E PROGRESSIVO
                $LASTBASE=$r[0]["LASTBASE"];
                $LASTPROGR=intval($r[0]["LASTPROGR"]);

                // DETERMINO L'ULTIMO ALLOCATO
                if($alloc>0)
                    $LASTALLOC=$LASTPROGR+$alloc;
                else
                    $LASTALLOC=$LASTPROGR+1;

                // INCREMENTO IL PROGRESSIVO
                $LASTPROGR+=1;

                // CONTROLLO CHE NON SIANO ESAURITI I PROGRESSIVI
                if($LASTALLOC>QVSYS_PROGRMAX){
                    // RIDETERMINO L'ULTIMO ALLOCATO
                    if($alloc>0)
                        $LASTALLOC=$alloc-1;
                    else
                        $LASTALLOC=0;
                    $TESTBASE=qv_getbaseid($maestro);
                    
                    if($TESTBASE<=$LASTBASE){
                        // PER QUALCHE MOTIVO IL MONAD ATTUALE NON E' QUELLO CHE HA GENERATO L'ULTIMO BASEID
                        monadset($LASTBASE);
                        $TESTBASE=qv_getbaseid($maestro);
                    }

                    $LASTBASE=$TESTBASE;
                    $LASTPROGR=0;
                    $sql="UPDATE QVSYSTEM SET LASTBASE='$LASTBASE',LASTPROGR='$LASTALLOC'";
                }
                else{
                    $sql="UPDATE QVSYSTEM SET LASTPROGR='$LASTALLOC'";
                }
                maestro_execute($maestro, $sql);
            }
            
            // INIZIALIZZAZIONE GESTIONE PREALLOCATI
            if($alloc>0){
                $global_preallocbase=$LASTBASE;
                $global_preallocmax=$LASTALLOC;
                $global_preallocprogr=$LASTPROGR+1;
            }
        }
        else{
            // GESTIONE PREALLOCATI
            $LASTBASE=$global_preallocbase;
            $LASTPROGR=$global_preallocprogr;
            $global_preallocprogr+=1;

            // SE SONO ARRIVATO ALLA FINE DELLE ALLOCAZIONI
            // ESCO DALLA GESTIONE DEI PREALLOCATI
            if($global_preallocprogr>$global_preallocmax){
                $global_preallocbase="";
                $global_preallocmax=0;
                $global_preallocprogr=0;
            }
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

function qv_babeltranslate($pattern, $params=array()){
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
           $check_sessionip,
           $global_lastuserid,
           $global_lastusername,
           $global_lastadmin,
           $global_lastemail,
           $global_lastroleid,
           $global_lastrolename,
           $global_lastenvid,
           $global_lastenvname,
           $global_lastlanguage,
           $global_lastcountrycode,
           $global_lastdebugmode,
           $global_lastclientip,
           $path_databases,
           $global_backslash;

    $ret=false;
           
    if($SESSIONID!=""){
        if($SESSIONID==$public_sessionid || ($SESSIONID==$ryque_sessionid && $context=="ryque")){
            $global_lastuserid=qv_actualid($maestro, "0SERVERID000");
            $global_lastusername="SERVER";
            $global_lastadmin=0;
            $global_lastemail="";
            $global_lastroleid=qv_actualid($maestro, "0NOROLEID000");
            $global_lastrolename="NOROLEID";
            $global_lastenvid="";
            $global_lastenvname="";
            $global_lastlanguage="";
            $global_lastcountrycode="";
            $global_lastdebugmode=false;
            $global_lastclientip=get_ip_address();
            $global_backslash=intval(@file_get_contents($path_databases."_configs/backslash.par"));
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
                    if($ip==$currip || $serverip==$currip || $check_sessionip==false){
                        $global_lastuserid=$r[0]["USERID"];
                        $global_lastusername=$r[0]["USERNAME"];
                        $global_lastadmin=intval($r[0]["ADMINISTRATOR"]);
                        $global_lastemail=$r[0]["EMAIL"];
                        $global_lastroleid=$r[0]["ROLEID"];
                        $global_lastrolename=$r[0]["ROLENAME"];
                        $global_lastenvid=$r[0]["ENVID"];
                        $global_lastenvname=$r[0]["ENVNAME"];
                        $global_lastlanguage=$r[0]["LANGUAGENAME"];
                        $global_lastcountrycode=$r[0]["COUNTRYCODE"];
                        $global_lastdebugmode=intval($r[0]["DEBUGMODE"]);
                        $global_lastclientip=$r[0]["CLIENTIP"];
                        $ret=true;
                    }
                }
                else{
                    // NON ESISTE: RICHIEDO LA VALIDAZIONE A EGO
                    if(ext_validatesession($SESSIONID, true, $context)){
                        $SYSID=qv_createsysid($maestro);
                        $USERID=$global_lastuserid;
                        $USERNAME=ryqEscapize($global_lastusername);
                        if($global_lastadmin)
                            $ADMINISTRATOR="1";
                        else
                            $ADMINISTRATOR="0";
                        $EMAIL=ryqEscapize($global_lastemail);
                        $ROLEID=$global_lastroleid;
                        $ROLENAME=ryqEscapize($global_lastrolename);
                        $ENVID=$global_lastenvid;
                        $ENVNAME=ryqEscapize($global_lastenvname);
                        $LANGUAGENAME=$global_lastlanguage;
                        $COUNTRYCODE=$global_lastcountrycode;
                        $DEBUGMODE=$global_lastdebugmode;
                        $CLIENTIP=$global_lastclientip;
                        $RENEWALTIME="[:NOW()]";

                        $sql="INSERT INTO QVSESSIONS(SYSID,SESSIONID,USERID,USERNAME,ADMINISTRATOR,EMAIL,ROLEID,ROLENAME,ENVID,ENVNAME,LANGUAGENAME,COUNTRYCODE,DEBUGMODE,CLIENTIP,RENEWALTIME) VALUES('$SYSID','$SESSIONID','$USERID','$USERNAME','$ADMINISTRATOR','$EMAIL','$ROLEID','$ROLENAME','$ENVID','$ENVNAME','$LANGUAGENAME','$COUNTRYCODE','$DEBUGMODE','$CLIENTIP',$RENEWALTIME)";
                        maestro_execute($maestro, $sql, false);
                        $ret=true;
                    }
                }
                // GESTIONE BACKSLASH
                $global_backslash=intval(@file_get_contents($path_databases."_configs/backslash.par"));
            }
            else{
                $ret=ext_validatesession($SESSIONID, false, $context);
            }
        }
    }
    return $ret;
}
?>