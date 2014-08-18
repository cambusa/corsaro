<?php 
/****************************************************************************
* Name:            qv_messages_empty.php                                     *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverdel.php";
include_once "../rymaestro/maestro_querylib.php";
function qv_messages_empty($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // INDIVIDUAZIONE RECORD
        qv_solverecord($maestro, $data, "QVMESSAGES", "SYSID", "", $SYSID);
        
        // DETERMINO STATUS
        if(isset($data["STATUS"])){
            $STATUS=intval($data["STATUS"]);
            if($STATUS<0 || $STATUS>3 )
                $STATUS=3;
        }
        else{
            $STATUS=3;
        }
        
        $arrdel=array();
        
        if($SYSID!=""){
            // CANCELLAZIONE SINGOLA PER SYSID
            $arrdel[]=$SYSID;
        }
        else{
            // CANCELLAZIONE MASSIVA PER DATA CANCELLAZIONE
            if(isset($data["DATE"]))
                $date=ryqEscapize($data["DATE"]);
            else
                $date=HIGHEST_DATE;
            $cnt=0;
            $res=maestro_unbuffered($maestro, "SELECT SYSID FROM QVMESSAGES WHERE STATUS>=$STATUS AND SENDINGTIME<[:DATE($date)]");
            while( $row=maestro_fetch($maestro, $res) ){
                $arrdel[]=$row["SYSID"];
                $cnt+=1;
                if($cnt>10000){
                    break;
                }
            }
            maestro_free($maestro, $res);
        }
        for($i=0;$i<count($arrdel);$i++){
            $SYSID=$arrdel[$i];
            $sql="DELETE FROM QVMESSAGES WHERE SYSID='$SYSID'";
            if(!maestro_execute($maestro, $sql, false)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"] );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
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