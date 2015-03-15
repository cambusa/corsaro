<?php 
/****************************************************************************
* Name:            qv_quivers_add.php                                       *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_quivers_add($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO UN NUOVO SYSID
        $SYSID=qv_createsysid($maestro);

        // DETERMINO QUIVERID
        qv_solverecord($maestro, $data, "QVQUIVERS", "QUIVERID", "QUIVERNAME", $QUIVERID);
        if($QUIVERID==""){
            $babelcode="QVERR_QUIVERID";
            $b_params=array();
            $b_pattern="Quiver non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO ARROWID
        qv_solverecord($maestro, $data, "QVARROWS", "ARROWID", "ARROWNAME", $ARROWID);
        if($ARROWID==""){
            $babelcode="QVERR_ARROWID";
            $b_params=array();
            $b_pattern="Freccia non specificata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // CONTROLLO CHE LA COPPIA QUIVERID, ARROWID NON SIA GIA' PRESENTE
        maestro_query($maestro,"SELECT SYSID FROM QVQUIVERARROW WHERE QUIVERID='$QUIVERID' AND ARROWID='$ARROWID'", $r);
        if(count($r)>0){
            $babelcode="QVERR_ALREADYQUIVER";
            $b_params=array("QUIVERID" => $QUIVERID, "ARROWID" => $ARROWID);
            $b_pattern="Freccia già inclusa nel quiver";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINO SORTER
        if(isset($data["SORTER"]))
            $SORTER=intval($data["SORTER"]);
        else
            $SORTER=0;
        
        // PREDISPONGO COLONNE E VALORI DA REGISTRARE
        $columns="SYSID,QUIVERID,ARROWID,SORTER";
        $values="'$SYSID','$QUIVERID','$ARROWID',$SORTER";
        $sql="INSERT INTO QVQUIVERARROW($columns) VALUES($values)";
        
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