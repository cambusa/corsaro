<?php 
/****************************************************************************
* Name:            qv_inclusions_update.php                                 *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverobj.php";
include_once "quiverinc.php";
function qv_inclusions_update($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // INDIVIDUAZIONE RECORD
        $sets="";
        $record=qv_solverecord($maestro, $data, "QVINCLUSIONS", "SYSID", "", $SYSID, "OBJECTID,PARENTID");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $REG_OBJECTID=$record["OBJECTID"];
        $REG_PARENTID=$record["PARENTID"];
        
        // INDIVIDUAZIONE ARCO DI VITA
        // Mi serve per avere le date registrate in precedenza
        qv_solvelife($maestro, "QVINCLUSIONS", $SYSID, $bi, $ei);

        // INDIVIDUAZIONE DEGLI OGGETTI RELAZIONATI
        qv_solveinclusion($maestro, $SYSID, $OBJECTID, $PARENTID);
        
        // DETERMINO BEGINDATE E ENDTIME
        qv_updatedatalife($maestro, $data, $sets, $bi, $ei);
        $BEGINTIME="[:TIME($bi)]";
        $ENDTIME="[:TIME($ei)]";
        qv_appendcomma($sets,"BEGINTIME=$BEGINTIME");
        qv_appendcomma($sets,"ENDTIME=$ENDTIME");

        // DETERMINO OBJECTID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTS", "OBJECTID", "OBJECTNAME", $OBJECTID);
        if($OBJECTID!=""){
            qv_appendcomma($sets,"OBJECTID='$OBJECTID'");
        }
        else{
            if($fields){
                $babelcode="QVERR_CHILDOBJ";
                $b_params=array();
                $b_pattern="Oggetto incluso non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            else{
                $OBJECTID=$REG_OBJECTID;
            }
        }
        
        // DETERMINO PARENTID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTS", "PARENTID", "PARENTNAME", $PARENTID);
        if($PARENTID!=""){
            qv_appendcomma($sets,"PARENTID='$PARENTID'");
        }
        else{
            if($fields){
                $babelcode="QVERR_PARENTOBJ";
                $b_params=array();
                $b_pattern="Oggetto contenitore non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            else{
                $PARENTID=$REG_PARENTID;
            }
        }
        
        /*
        if($OBJECTID==$PARENTID){
            $babelcode="QVERR_SAMEOBJ";
            $b_params=array();
            $b_pattern="Un oggetto non può essere incluso in se stesso";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        */
        
        // RISOLVO I CICLI DI VITA DEL CONTENUTO E DEL CONTENITORE
        qv_solvelife($maestro, "QVOBJECTS", $PARENTID, $bp, $ep);
        qv_solvelife($maestro, "QVOBJECTS", $OBJECTID, $bc, $ec);
        
        // CONSISTENZA DATE
        if( ( $bi>LOWEST_TIME && ($bi<$bp || $bi<$bc) ) || ( $ei<HIGHEST_TIME && ($ei>$ep || $ei>$ec) ) ){
            $babelcode="QVERR_OUTOFLIFE";
            $b_params=array();
            $b_pattern="Ciclo di vita non compatibile con quello degli oggetti relazionati";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // CONTROLLO CHE SE ESISTONO ALTRE INCLUSIONI TRA GLI STESSI OGGETTI LE DATE NON COLLIDANO
        qv_checkinclusions($maestro, $SYSID, $OBJECTID, $PARENTID, $bi, $ei);
        
        // DETERMINO TAG
        if(isset($data["TAG"])){
            $TAG=ryqEscapize($data["TAG"], 200);
            qv_appendcomma($sets,"TAG='$TAG'");
        }
        
        // DETERMINO SORTER
        if(isset($data["SCOPE"])){
            $SORTER=intval($data["SORTER"]);
            qv_appendcomma($sets,"SORTER=$SORTER");
        }
        
        if($sets!=""){
            // PREDISPONGO COLONNE E VALORI DA REGISTRARE
            $sql="UPDATE QVINCLUSIONS SET $sets WHERE SYSID='$SYSID'";
        
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