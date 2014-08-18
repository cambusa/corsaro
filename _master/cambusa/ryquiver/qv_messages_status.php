<?php 
/****************************************************************************
* Name:            qv_messages_status.php                                   *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_messages_status($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        // INDIVIDUAZIONE RECORD
        $sets="";
        qv_solverecord($maestro, $data, "QVMESSAGES", "SYSID", "", $SYSID);
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO STATUS
        if(isset($data["STATUS"])){
            $STATUS=intval($data["STATUS"]);
            if($STATUS<0 || $STATUS>3 )
                $STATUS=1;
        }
        else{
            $STATUS=1;
        }
        qv_appendcomma($sets,"STATUS=$STATUS");
        
        if($STATUS==1){
            $RECEIVINGTIME="[:NOW()]";
            qv_appendcomma($sets,"RECEIVINGTIME=$RECEIVINGTIME");
        }
        
        if($sets!=""){
            $sql="UPDATE QVMESSAGES SET $sets WHERE SYSID='$SYSID'";
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