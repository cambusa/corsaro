<?php 
/****************************************************************************
* Name:            qv_smartfinder.php                                       *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(is_file($path_cambusa."ryque/ryq_gauge.php"))
    include_once $path_cambusa."ryque/ryq_gauge.php";
elseif(is_file($path_cambusa."ryque/ryq_gauge.phar"))
    include_once "phar://".$path_cambusa."ryque/ryq_gauge.phar/ryq_gauge.php";
else
    include_once $path_cambusa."ryque/ryq_gaugeminus.php";
function qv_smartfinder($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO ACTION
        if(isset($data["ACTION"]))
            $ACTION=$data["ACTION"];
        else
            $ACTION="BEGIN";

        // DETERMINO REQID
        if(isset($data["REQID"]))
            $REQID=$data["REQID"];
        else
            $REQID="";
            
        switch($ACTION){
        case "BEGIN":
            // DETERMINO GAUGE
            if(isset($data["GAUGE"]))
                $GAUGE=floatval($data["GAUGE"]);
            else
                $GAUGE=0;
            
            // DETERMINO VALUES
            if(isset($data["VALUES"]))
                $VALUES=explode("|", $data["VALUES"]);
            else
                $VALUES=array();

            // DETERMINO REFS
            if(isset($data["REFS"]))
                $REFS=explode("|", $data["REFS"]);
            else
                $REFS=array();
            
            if($REQID==""){
                // CREO UNA REQUESTID UNIVOCA
                $REQID="RI".date("YmdHis");
                for($i=1; $i<=2; $i++){
                    $REQID.=monadrand();
                }
            }
            $s=gaugesearch($REQID, array("gauge" => $GAUGE, "exhaustive" => 2), $VALUES, $REFS);
            break;

        case "CONTINUE":
            $s=gaugesearch($REQID);
            break;

        case "END":
            gaugedispose($REQID);
            $s=false;
            break;
        }
        
        // VARIABILI DI RITORNO
        $babelparams["PROTOCOLID"]=$REQID;
        if($s){
            $babelparams["FOUND"]="1";
            $babelparams["SOLUTION"]=implode("|", $s);
        }
        else{
            $babelparams["FOUND"]="0";
            $babelparams["SOLUTION"]="";
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