<?php 
/****************************************************************************
* Name:            qv_messages_status.php                                   *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_messages_status($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        // DETERMINO ACTION
        if(isset($data["ACTION"]))
            $ACTION=$data["ACTION"];
        else
            $ACTION="";
        
        // DETERMINO RECEIVERID
        if(isset($data["RECEIVERID"]))
            $RECEIVERID=$data["RECEIVERID"];
        else
            $RECEIVERID="";
        
        // DETERMINO EGOID
        if(isset($data["EGOID"]))
            $EGOID=$data["EGOID"];
        else
            $EGOID="";
        
        // DETERMINO LIST
        if(isset($data["LIST"])){
            $LIST=$data["LIST"];
            $LIST="'" . str_replace("|", "','", $LIST) . "'";
        }
        else{
            $LIST="''";
        }
        
        // DETERMINAZIONE DELLA UPDATE
        $sql="";
        switch($ACTION){
        case "RECEIVED":
            if($RECEIVERID!="")
                $sql="UPDATE QVMESSAGES SET STATUS=1,RECEIVINGTIME=[:NOW()] WHERE RECEIVERID='$RECEIVERID' AND STATUS=0";
            else
                $sql="UPDATE QVMESSAGES SET STATUS=1,RECEIVINGTIME=[:NOW()] WHERE RECEIVERID IN (SELECT SYSID FROM QVUSERS WHERE EGOID='$EGOID' AND ARCHIVED=0) AND STATUS=0";
            break;
        case "UNREAD":
            $sql="UPDATE QVMESSAGES SET STATUS=1 WHERE SYSID IN ($LIST)";
            break;
        case "READ":
            $sql="UPDATE QVMESSAGES SET STATUS=2 WHERE SYSID IN ($LIST)";
            break;
        case "DELETE":
            $sql="UPDATE QVMESSAGES SET STATUS=3 WHERE SYSID IN ($LIST)";
            break;
        }
        if($sql!=""){
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