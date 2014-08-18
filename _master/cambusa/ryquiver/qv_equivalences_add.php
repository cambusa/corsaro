<?php 
/****************************************************************************
* Name:            qv_equivalences_add.php                                  *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_equivalences_add($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO UN NUOVO SYSID
        $SYSID=qv_createsysid($maestro);

        // DETERMINO REFERENCEID
        qv_solverecord($maestro, $data, "QVARROWS", "REFERENCEID", "REFERENCENAME", $REFERENCEID);
        if($REFERENCEID==""){
            $babelcode="QVERR_REFERENCEID";
            $b_params=array();
            $b_pattern="Freccia di riferimento non specificata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO EQUIVALENTID
        qv_solverecord($maestro, $data, "QVARROWS", "EQUIVALENTID", "EQUIVALENTNAME", $EQUIVALENTID);
        if($EQUIVALENTID==""){
            $babelcode="QVERR_EQUIVALENTID";
            $b_params=array();
            $b_pattern="Freccia equivalente non specificata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        if($REFERENCEID==$EQUIVALENTID){
            $babelcode="QVERR_SAMEARROW";
            $b_params=array();
            $b_pattern="Le frecce devono essere distinte";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // CONTROLLO CHE LA COPPIA REFERENCEID, EQUIVALENTID NON SIA GIA' PRESENTE
        maestro_query($maestro,"SELECT SYSID FROM QVEQUIVALENCES WHERE REFERENCEID='$REFERENCEID' AND EQUIVALENTID='$EQUIVALENTID'",$r);
        if(count($r)>0){
            $babelcode="QVERR_ALREADYEQUIV";
            $b_params=array("REFERENCEID" => $REFERENCEID, "EQUIVALENTID" => $EQUIVALENTID);
            $b_pattern="Equivalenza già definita";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // PREDISPONGO COLONNE E VALORI DA REGISTRARE
        $columns="SYSID,REFERENCEID,EQUIVALENTID";
        $values="'$SYSID','$REFERENCEID','$EQUIVALENTID'";
        $sql="INSERT INTO QVEQUIVALENCES($columns) VALUES($values)";
        
        if(!maestro_execute($maestro, $sql, false)){
            $babelcode="QVERR_EXECUTE";
            $trace=debug_backtrace();
            $b_params=array("FUNCTION" => $trace[0]["function"] );
            $b_pattern=$maestro->errdescr;
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