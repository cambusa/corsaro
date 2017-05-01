<?php 
/****************************************************************************
* Name:            qv_transcoding_update.php                                *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_transcoding_update($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // INDIVIDUAZIONE RECORD
        $sets="";
        $record=qv_solverecord($maestro, $data, "QVTRANSCODING", "SYSID", "", $SYSID, "");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $TYPOLOGY=$record["TYPOLOGY"];
        $CONTEXT=$record["CONTEXT"];
        $ORIGINALVALUE=$record["ORIGINALVALUE"];
        $TRANSCODEDVALUE=$record["TRANSCODEDVALUE"];
        $DESCRIPTION=$record["DESCRIPTION"];
        
        // DETERMINO TYPOLOGY
        if(isset($data["TYPOLOGY"])){
            $TYPOLOGY=ryqEscapize($data["TYPOLOGY"], 50);
            if($TYPOLOGY==""){
                $babelcode="QVERR_TYPOLOGY";
                $b_params=array();
                $b_pattern="Tipologia non specificata";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"TYPOLOGY='$TYPOLOGY'");
        }
            
        // DETERMINO CONTEXT
        if(isset($data["CONTEXT"])){
            $CONTEXT=ryqEscapize($data["CONTEXT"], 50);
            if($CONTEXT==""){
                $babelcode="QVERR_CONTEXT";
                $b_params=array();
                $b_pattern="Contesto non specificata";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"CONTEXT='$CONTEXT'");
        }
        
        // DETERMINO ORIGINALVALUE
        if(isset($data["ORIGINALVALUE"])){
            $ORIGINALVALUE=ryqEscapize($data["ORIGINALVALUE"], 255);
            if($ORIGINALVALUE==""){
                $babelcode="QVERR_ORIGINALVALUE";
                $b_params=array();
                $b_pattern="Valore originale non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"ORIGINALVALUE='$ORIGINALVALUE'");
        }
        
        // DETERMINO TRANSCODEDVALUE
        if(isset($data["TRANSCODEDVALUE"])){
            $TRANSCODEDVALUE=ryqEscapize($data["TRANSCODEDVALUE"], 255);
            qv_appendcomma($sets,"TRANSCODEDVALUE='$TRANSCODEDVALUE'");
        }
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize($data["DESCRIPTION"], 255);
            qv_appendcomma($sets,"DESCRIPTION='$DESCRIPTION'");
        }
        
        if($sets!=""){
            $sql="UPDATE QVTRANSCODING SET $sets WHERE SYSID='$SYSID'";
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