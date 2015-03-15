<?php 
/****************************************************************************
* Name:            qv_selections_flags.php                                  *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_selections_flags($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO PARENTID
        $PARENTID="";
        if(isset($data["PARENTID"])){
            $PARENTID=$data["PARENTID"];
            maestro_query($maestro,"SELECT {AS:TOP 1} PARENTFIELD FROM QVSELECTIONS WHERE PARENTID='$PARENTID' {LM:LIMIT 1}{O: AND ROWNUM=1}{D:FETCH FIRST 1 ROWS ONLY}", $r);
            if(count($r)>0){   // PRETENDO CHE ESISTA
                $PARENTFIELD=$r[0]["PARENTFIELD"];
                if($PARENTFIELD==""){
                    $PARENTFIELD="SYSID";
                }
            }
            else{
                $PARENTID="";
            }
        }
        if($PARENTID==""){
            $babelcode="QVERR_PARENTID";
            $b_params=array();
            $b_pattern="Riferimento non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO LA SET
        $set=array();
        if(isset($data["ENABLED"])){
            if(intval($data["ENABLED"]))
                $set[]="ENABLED=1";
            else
                $set[]="ENABLED=0";
        }
        if(isset($data["DISTANCE"])){
            $set[]="DISTANCE=".floatval($data["DISTANCE"]);
        }
        for($i=1; $i<=4; $i++){
            if(isset($data["FLAG".$i])){
                if(intval($data["FLAG".$i]))
                    $set[]="FLAG".$i."=1";
                else
                    $set[]="FLAG".$i."=0";
            }
        }
        // DETERMINO SELECTIONS
        if(isset($data["SELECTION"])){
            $SELECTION=$data["SELECTION"];
            if(!is_array($SELECTION)){
                if($SELECTION!="")
                    $SELECTION=explode("|", $SELECTION);
                else
                    $SELECTION=array();
            }
            if(count($set)>0 && count($SELECTION)>0){
                $settings=implode(",", $set);
                $in="'" . implode("','", $SELECTION) . "'";
                // AGGIORNO I SELEZIONATI
                $sql="UPDATE QVSELECTIONS SET $settings WHERE PARENTID='$PARENTID' AND SELECTEDID IN ($in)";
                if(!maestro_execute($maestro, $sql, false)){
                    $babelcode="QVERR_EXECUTE";
                    $trace=debug_backtrace();
                    $b_params=array("FUNCTION" => $trace[0]["function"] );
                    $b_pattern=$maestro->errdescr;
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
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