<?php 
/****************************************************************************
* Name:            qv_inclusions_insert.php                                 *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverobj.php";
include_once "quiverinc.php";
function qv_inclusions_insert($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO UN NUOVO SYSID
        $SYSID=qv_createsysid($maestro);
        
        // DETERMINO BEGINDATE E ENDTIME
        qv_insertdatalife($maestro, $data, $bi, $ei);
        $BEGINTIME="[:TIME($bi)]";
        $ENDTIME="[:TIME($ei)]";
        
        // DETERMINO OBJECTID
        qv_solverecord($maestro, $data, "QVOBJECTS", "OBJECTID", "OBJECTNAME", $OBJECTID);
        if($OBJECTID==""){
            $babelcode="QVERR_CHILDOBJ";
            $b_params=array();
            $b_pattern="Oggetto incluso non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO PARENTID
        qv_solverecord($maestro, $data, "QVOBJECTS", "PARENTID", "PARENTNAME", $PARENTID);
        if($PARENTID==""){
            $babelcode="QVERR_PARENTOBJ";
            $b_params=array();
            $b_pattern="Oggetto contenitore non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
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
        if(isset($data["TAG"]))
            $TAG=ryqEscapize($data["TAG"], 200);
        else
            $TAG="";
        
        // DETERMINO SORTER
        if(isset($data["SORTER"]))
            $SORTER=intval($data["SORTER"]);
        else
            $SORTER=0;
        
        // PREDISPONGO COLONNE E VALORI DA REGISTRARE
        $columns="SYSID,OBJECTID,PARENTID,BEGINTIME,ENDTIME,TAG,SORTER";
        $values="'$SYSID','$OBJECTID','$PARENTID',$BEGINTIME,$ENDTIME,'$TAG',$SORTER";
        $sql="INSERT INTO QVINCLUSIONS($columns) VALUES($values)";
        
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