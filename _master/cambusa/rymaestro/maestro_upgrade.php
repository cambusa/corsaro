<?php 
/****************************************************************************
* Name:            maestro_upgrade.php                                      *
* Project:         Cambusa/ryMaestro                                        *
* Version:         1.69                                                     *
* Description:     Databases modeling and maintenance                       *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
// CARICO LE LIBRERIE
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_upgradelib.php";

try{
    if(isset($_POST["sessionid"]))
        $sessionid=$_POST["sessionid"];
    else
        $sessionid="";

    if(isset($_POST["env"]))
        $env=$_POST["env"];
    else
        $env=="";

    if(isset($_POST["logonly"]))
        $logonly=intval($_POST["logonly"]);
    else
        $logonly=0;

    if($env!=""){
        // SE IL DATABASE E' SQLITE, CONTROLLO CHE ESISTA E SE NON ESISTE LO CREO
        maestro_checklite($env);
    
        // APERTURA DATABASE
        $maestro=maestro_opendb($env);

        if($maestro->conn!==false){
            if(ext_validatesession($sessionid, false, "maestro")){
                $j=maestro_upgrade($maestro, $logonly);
                $success=$j["success"];
                $description=$j["description"];
            }
            else{
                $success=0;
                $description="Sessione non valida o autorizzazioni insufficienti";
            }
        }
        else{
            $success=0;
            $description="Connessione non valida";
        }

        // CHIUSURA DATABASE
        maestro_closedb($maestro);
    }
    else{
        $success=0;
        $description="Ambiente non specificato";
    }
}
catch(Exception $e){
    $success=0;
    $description=$e->getMessage();
}
$j=array();
$j["success"]=$success;
$j["description"]=$description;
array_walk_recursive($j, "maestro_escapize");
print json_encode($j);

function upgrade_progress(){
    print str_repeat(" ", 1000);
    flush();
}
?>