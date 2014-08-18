<?php 
/****************************************************************************
* Name:            qv_quivers_empty.php                                     *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverdel.php";
include_once "quiverext.php";
include_once "../rymaestro/maestro_querylib.php";
function qv_quivers_empty($maestro, $data){
    global $babelcode, $babelparams;
    global $global_lastadmin;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // GESTIONE AMMINISTRATORE
        if($global_lastadmin==0){
            $babelcode="QVERR_FORBIDDEN";
            $b_params=array();
            $b_pattern="Autorizzazioni insufficienti";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // INDIVIDUAZIONE RECORD
        qv_solverecord($maestro, $data, "QVQUIVERS", "SYSID", "NAME", $SYSID);
        
        $arrdel=array();
        
        if($SYSID!=""){
            // CANCELLAZIONE SINGOLA PER SYSID O PER NAME
            $arrdel[]=$SYSID;
        }
        else{
            // CANCELLAZIONE MASSIVA PER DATA CANCELLAZIONE
            if(isset($data["DATE"]))
                $date=ryqEscapize($data["DATE"]);
            else
                $date=HIGHEST_DATE;
            $res=maestro_unbuffered($maestro, "SELECT SYSID FROM QVQUIVERS WHERE DELETED=1 AND TIMEDELETE<[:DATE($date)]");
            while( $row=maestro_fetch($maestro, $res) ){
                $arrdel[]=$row["SYSID"];
            }
            maestro_free($maestro, $res);
        }
        for($i=0;$i<count($arrdel);$i++){
            $SYSID=$arrdel[$i];
            maestro_query($maestro,"SELECT TYPOLOGYID FROM QVQUIVERS WHERE SYSID='$SYSID'", $r);
            if(count($r)==1){
                $TYPOLOGYID=$r[0]["TYPOLOGYID"];
                $sql="DELETE FROM QVQUIVERS WHERE SYSID='$SYSID'";
                if(!maestro_execute($maestro, $sql, false)){
                    $babelcode="QVERR_EXECUTE";
                    $trace=debug_backtrace();
                    $b_params=array("FUNCTION" => $trace[0]["function"] );
                    $b_pattern=$maestro->errdescr;
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }

                // GESTIONE DEI DATI ESTESI
                qv_extension($maestro, $data, "QVQUIVER", $SYSID, $TYPOLOGYID, 3);
            }
        }
    }
    catch(Exception $e){
        $success=0;
        $message=$e->getMessage();
    }
    // USCITA JSON
    $j=array();
    $j["success"]=$success;
    $j["code"]=$babelcode;
    $j["params"]=$babelparams;
    $j["message"]=$message;
    $j["SYSID"]=$SYSID;
    return $j; //ritorno standard
}
?>