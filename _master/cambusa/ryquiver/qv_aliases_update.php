<?php 
/****************************************************************************
* Name:            qv_aliases_update.php                                    *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_aliases_update($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // INDIVIDUAZIONE RECORD
        $record=qv_solverecord($maestro, $data, "QVALIASES", "SYSID", "NAME", $SYSID, "*");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $TABLENAME=$record["TABLENAME"];
        $RECORDID=$record["RECORDID"];
        $PROVIDER=$record["PROVIDER"];
        $INDIVIDUATION=$record["INDIVIDUATION"];

        // DETERMINO TABLENAME
        if(isset($data["TABLENAME"])){
            $TABLENAME=ryqEscapize($data["TABLENAME"]);
            if($TABLENAME==""){
                $babelcode="QVERR_TABLENAME";
                $b_params=array();
                $b_pattern="Nome tabella non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"TABLENAME='$TABLENAME'");
        }
            
        // DETERMINO RECORDID
        $fields=qv_solverecord($maestro, $data, $TABLENAME, "RECORDID", "RECORDNAME", $RECORDID);
        if($RECORDID!=""){
            qv_appendcomma($sets,"RECORDID='$RECORDID'");
        }
        else{
            if($fields){
                $babelcode="QVERR_RECORDID";
                $b_params=array();
                $b_pattern="Identificatore di record non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        
        // DETERMINO PROVIDER
        if(isset($data["PROVIDER"])){
            $PROVIDER=ryqEscapize($data["PROVIDER"], 20);
            if($PROVIDER==""){
                $babelcode="QVERR_PROVIDER";
                $b_params=array();
                $b_pattern="Provider non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"PROVIDER='$PROVIDER'");
        }

        // DETERMINO INDIVIDUATION
        if(isset($data["INDIVIDUATION"])){
            $INDIVIDUATION=ryqEscapize($data["INDIVIDUATION"], 100);
            if($INDIVIDUATION==""){
                $babelcode="QVERR_INDIV";
                $b_params=array();
                $b_pattern="Decodifica non specificata";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"INDIVIDUATION='$INDIVIDUATION'");
        }
        
        // CONTROLLO CHE UNA COMBINAZIONE UGUALE NON SIA GIA' NEL SISTEMA
        maestro_query($maestro,"SELECT SYSID FROM QVALIASES WHERE SYSID<>'$SYSID' AND [:UPPER(TABLENAME)]='".strtoupper($TABLENAME)."' AND RECORDID='$RECORDID' AND [:UPPER(PROVIDER)]='".strtoupper($PROVIDER)."' AND [:UPPER(INDIVIDUATION)]='".strtoupper($INDIVIDUATION)."'",$r);
        if(count($r)>0){
            $babelcode="QVERR_ALREADYALIAS";
            $b_params=array("TABLENAME" => $TABLENAME, "RECORDID" => $RECORDID, "PROVIDER" => $PROVIDER, "INDIVIDUATION" => $INDIVIDUATION);
            $b_pattern="Impossibile decodificare un record in due modi uguali";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        if($sets!=""){
            $sql="UPDATE QVALIASES SET $sets WHERE SYSID='$SYSID'";
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