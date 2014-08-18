<?php 
/****************************************************************************
* Name:            qv_quivertypes_delete.php                                 *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverdel.php";
include_once "quivervws.php";
function qv_quivertypes_delete($maestro, $data){
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
        qv_solverecord($maestro, $data, "QVQUIVERTYPES", "SYSID", "NAME", $SYSID);
        if($SYSID!=""){
            qv_deletable($maestro,"QVQUIVERS","TYPOLOGYID",$SYSID);
            qv_deletable($maestro,"QVQUIVERVIEWS","TYPOLOGYID",$SYSID);
            qv_deletable($maestro, "QVQUIVERTYPES", "QUIVERTYPEID", $SYSID);
            qv_deletable($maestro, "QVOBJECTTYPES", "QUIVERTYPEID", $SYSID);
            
            // DROPPO LA VECCHIA VIEW
            qv_deleteview($maestro, "QVQUIVER", $SYSID);

            $sql="DELETE FROM QVQUIVERTYPES WHERE SYSID='$SYSID'";
            if(!maestro_execute($maestro, $sql, false)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"] );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
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