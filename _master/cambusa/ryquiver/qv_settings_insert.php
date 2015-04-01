<?php 
/****************************************************************************
* Name:            qv_settings_insert.php                                   *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_settings_insert($maestro, $data){
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
        
        // DETERMINO UN NUOVO SYSID
        $SYSID=qv_createsysid($maestro);
        
        // DETERMINO NAME
        if(isset($data["NAME"])){
            $NAME=ryqEscapize($data["NAME"], 50);
            qv_checkname($maestro, "QVARROWTYPES", $SYSID, $NAME);
        }
        else{
            $NAME="__$SYSID";
        }
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize(qv_inputUTF8($data["DESCRIPTION"]), 200);
            if($DESCRIPTION=="")
                $DESCRIPTION=$NAME;
        }
        else{
            $DESCRIPTION=$NAME;
        }
            
        // DETERMINO DATATYPE
        if(isset($data["DATATYPE"])){
            $DATATYPE=strtoupper( ryqEscapize($data["DATATYPE"], 10) );
            switch($DATATYPE){
                case "INTEGER":
                case "RATIONAL":
                case "BOOLEAN":
                case "STRING":
                case "DATE":
                case "TIMESTAMP":
                    break;
                default:
                    $DATATYPE="STRING";
            }
        }
        else{
            $DATATYPE="STRING";
        }
        
        // DETERMINO DATAVALUE
        if(isset($data["DATAVALUE"]))
            $DATAVALUE=ryqEscapize($data["DATAVALUE"], 250);
        else
            $DATAVALUE="";
        
        // DETERMINO TAG
        if(isset($data["TAG"]))
            $TAG=ryqEscapize($data["TAG"], 200);
        else
            $TAG="";
        
        // PREDISPONGO COLONNE E VALORI DA REGISTRARE
        $columns="SYSID,NAME,DESCRIPTION,DATATYPE,DATAVALUE,TAG";
        $values="'$SYSID','$NAME','$DESCRIPTION','$DATATYPE','$DATAVALUE','$TAG'";
        $sql="INSERT INTO QVSETTINGS($columns) VALUES($values)";
        
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