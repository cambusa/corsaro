<?php 
/****************************************************************************
* Name:            qv_transcoding_insert.php                                *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_transcoding_insert($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO UN NUOVO SYSID
        $SYSID=qv_createsysid($maestro);
        
        // DETERMINO TYPOLOGY
        if(isset($data["TYPOLOGY"])){
            $TYPOLOGY=ryqEscapize($data["TYPOLOGY"]);
            if($TYPOLOGY==""){
                $babelcode="QVERR_TYPOLOGY";
                $b_params=array();
                $b_pattern="Tipologia non specificata";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $babelcode="QVERR_TYPOLOGY";
            $b_params=array();
            $b_pattern="Tipologia non specificata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
            
        // DETERMINO CONTEXT
        if(isset($data["CONTEXT"])){
            $CONTEXT=ryqEscapize($data["CONTEXT"]);
            if($CONTEXT==""){
                $babelcode="QVERR_CONTEXT";
                $b_params=array();
                $b_pattern="Contesto non specificata";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $babelcode="QVERR_CONTEXT";
            $b_params=array();
            $b_pattern="Contesto non specificata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINO ORIGINALVALUE
        if(isset($data["ORIGINALVALUE"])){
            $ORIGINALVALUE=ryqEscapize($data["ORIGINALVALUE"]);
            if($ORIGINALVALUE==""){
                $babelcode="QVERR_ORIGINALVALUE";
                $b_params=array();
                $b_pattern="Valore originale non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $babelcode="QVERR_ORIGINALVALUE";
            $b_params=array();
            $b_pattern="Valore originale non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINO TRANSCODEDVALUE
        if(isset($data["TRANSCODEDVALUE"])){
            $TRANSCODEDVALUE=ryqEscapize($data["TRANSCODEDVALUE"]);
        }
        else{
            $TRANSCODEDVALUE="";
        }

        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize($data["DESCRIPTION"]);
        }
        else{
            $DESCRIPTION="";
        }

        // PREDISPONGO COLONNE E VALORI DA REGISTRARE
        $columns="SYSID,TYPOLOGY,CONTEXT,ORIGINALVALUE,TRANSCODEDVALUE,DESCRIPTION";
        $values="'$SYSID','$TYPOLOGY','$CONTEXT','$ORIGINALVALUE','$TRANSCODEDVALUE','$DESCRIPTION'";
        $sql="INSERT INTO QVTRANSCODING($columns) VALUES($values)";
        
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