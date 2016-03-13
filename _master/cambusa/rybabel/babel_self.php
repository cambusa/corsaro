<?php 
/****************************************************************************
* Name:            babel_self.php                                           *
* Project:         Cambusa/ryBabel                                          *
* Version:         1.70                                                     *
* Description:     Language localization                                    *
* Copyright (C):   2016  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."sysconfig.php";
include_once $tocambusa."ryquiver/quiversex.php";

function babelself($codes){
    global $config_selflearning;
    try{
        if($config_selflearning!="" && $codes!=""){
            $sessionevalida=false;
            if(isset($_POST["env"]) && isset($_POST["sessionid"])){

                // RISOLVO I VALORI IN INGRESSO
                $env_name=strtolower($_POST["env"]);
                $sessionid=$_POST["sessionid"];
            
                // APRO IL DATABASE
                $maestrosex=maestro_opendb($env_name, false);

                // VERIFICO IL BUON ESITO DELL'APERTURA
                if($maestrosex->conn!==false){
                    $sessionevalida=qv_validatesession($maestrosex, $sessionid, "");
                }
                
                // CHIUSURA DATABASE
                maestro_closedb($maestrosex);
            }
            if($sessionevalida){
            
                // APERTURA DATABASE
                $maestro=maestro_opendb($config_selflearning);
                
                if($maestro->conn!==false){
                    foreach($codes as $babel){
                        $code=strtoupper($babel["code"]);
                        $caption=ryqEscapize($babel["caption"]);
                        
                        $sql="SELECT NAME FROM BABELITEMS WHERE [:UPPER(NAME)]='$code'";
                        maestro_query($maestro, $sql, $r);
                        if(count($r)==0){
                            $sql="INSERT INTO BABELITEMS (SYSID,NAME,CAPTION) VALUES([:SYSID],'$code','$caption')";
                            maestro_execute($maestro, $sql, false);
                        }
                    }
                }
                
                // CHIUSURA DATABASE
                maestro_closedb($maestro);
                return "1";
            }
            else{
                return "0Invalid session: $env_name, $sessionid";
            }
        }
        else{
            return "0Insufficient parameters";
        }
    }
    catch(Exception $e){
        return "0".$e->getMessage();
    }
}
?>