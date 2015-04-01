<?php 
/****************************************************************************
* Name:            qv_entities_empty.php                                    *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverdel.php";
include_once "quiverext.php";
include_once $path_cambusa."rymaestro/maestro_querylib.php";
function qv_entities_empty($maestro, $data){
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
        
        // DETERMINO TABLENAME
        if(isset($data["TABLENAME"])){
            $TABLENAME=ryqEscapize($data["TABLENAME"]);
            if($TABLENAME==""){
                $babelcode="QVERR_TABLENAME";
                $b_params=array();
                $b_pattern="Nome tabella non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        
        $PREFIX=substr($TABLENAME, 0, -1);
        
        // DETERMINO LA TIPOLOGIA
        if(isset($data["TYPOLOGYID"])){
            $TYPOLOGYID=ryqEscapize($data["TYPOLOGYID"]);
            $CLAUSETYPOLOGY=" TYPOLOGYID='$TYPOLOGYID' AND ";
        }
        else{
            $CLAUSETYPOLOGY="";
        }
        
        if(isset($data["TIMEDELETE"]))
            $TIMEDELETE=qv_escapizetime($data["TIMEDELETE"], HIGHEST_TIME);
        else
            $TIMEDELETE=HIGHEST_TIME;

        // INDIVIDUAZIONE RECORD
        qv_solverecord($maestro, $data, "QVOBJECTS", "SYSID", "NAME", $SYSID);
        
        // CANCELLAZIONE A BLOCCHI DI TRANSAZIONI
        $LASTID="";
        $LIMIT=100;
        $cnt=0;
        do{
            $arrdel=array();
            if($SYSID!=""){
                // CANCELLAZIONE SINGOLA PER SYSID O PER NAME
                maestro_query($maestro,"SELECT TYPOLOGYID FROM $TABLENAME WHERE SYSID='$SYSID'", $b);
                if(count($b)==1){
                    $cnt=1;
                    $arrdel[]=array($SYSID, $b[0]["TYPOLOGYID"]);
                }
            }
            else{
                // CANCELLAZIONE MASSIVA
                $sql="SELECT {AS:TOP $LIMIT} SYSID,TYPOLOGYID FROM $TABLENAME WHERE SYSID>'$LASTID' AND DELETED=1 AND $CLAUSETYPOLOGY TIMEDELETE<[:TIME($TIMEDELETE)] {O: AND ROWNUM=$LIMIT} ORDER BY SYSID {LM:LIMIT $LIMIT}{D:FETCH FIRST $LIMIT ROWS ONLY}";
                maestro_query($maestro, $sql, $b);
                $cnt=count($b);
                for($i=0;$i<$cnt;$i++){
                    $LASTID=$b[$i]["SYSID"];

                    // AGGIUNGO IL RECORD ALLA LISTA DEI CANCELLANDI
                    $arrdel[]=array($LASTID, $b[$i]["TYPOLOGYID"]);
                }
            }
            for($i=0;$i<$cnt;$i++){
                list($SYSID, $TYPOLOGYID)=$arrdel[$i];
                $sql="DELETE FROM $TABLENAME WHERE SYSID='$SYSID'";
                if(!maestro_execute($maestro, $sql, false)){
                    $babelcode="QVERR_EXECUTE";
                    $trace=debug_backtrace();
                    $b_params=array("FUNCTION" => $trace[0]["function"] );
                    $b_pattern=$maestro->errdescr;
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }

                // GESTIONE DEI DATI ESTESI
                qv_extension($maestro, $data, $PREFIX, $SYSID, $TYPOLOGYID, 3);
            }
            // COMMIT TRANSACTION
            maestro_commit($maestro);
            
            // BEGIN TRANSACTION
            maestro_begin($maestro);

        }while($cnt==$LIMIT);
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