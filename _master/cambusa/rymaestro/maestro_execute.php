<?php 
/****************************************************************************
* Name:            maestro_execute.php                                      *
* Project:         Cambusa/ryMaestro                                        *
* Version:         1.69                                                     *
* Description:     Databases modeling and maintenance                       *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
set_time_limit(0);

include_once "../sysconfig.php";
include_once "maestro_execlib.php";
include_once "../ryego/ego_validate.php";
include_once "../rygeneral/writelog.php";
try{
    if(isset($_POST["sessionid"]))
        $sessionid=$_POST["sessionid"];
    else
        $sessionid="";

    if(isset($_POST["env"]))
        $env=$_POST["env"];
    else
        $env="";

    if(isset($_POST["sql"]))
        $sql=$_POST["sql"];
    else
        $sql="";

    $r=array();
        
    if(ext_validatesession($sessionid, false, "maestro")){
        if($env!=""){
            $maestro=maestro_opendb($env);
            if($maestro->conn!==false){
                // NORMALIZZO IL TESTO
                $sql=strtr($sql, array("\'" => "'", "\\\"" => "\"", "\\\\" => "\\"));
                $sql=preg_replace("/^[\n\r\t]+/", "", $sql);
                $sql=preg_replace("/--[^\n\r]*([\n\r])/", "$1", $sql);
                $sql=preg_replace("/--[^\n\r]*$/", "", $sql);
                $sql=trim($sql);

                // STABLISCO SE E' UNA QUERY DI INTERROGAZIONE O DI AGGIORNAMENTO
                if(maestro_querytype($maestro, $sql)){
                    if(!maestro_query($maestro, $sql, $r, false))
                        $r[]=array("Failure" => $maestro->errdescr);
                }
                else{
                    if(maestro_execute($maestro, $sql, false))
                        $r[]=array("Rows" => $maestro->rows);
                    else
                        $r[]=array("Failure" => $maestro->errdescr);
                }
            }
            else{
                $r[]=array("Failure" => "Connessione al database fallita");
            }
            maestro_closedb($maestro);
        }
        else{
            $r[]=array("Failure" => "Ambiente non specificato");
        }
    }
    else{
        $r[]=array("Failure" => "Sessione non valida o autorizzazioni insufficienti");
    }
}
catch(Exception $e){
    $r[]=array("Failure" => $e->getMessage() );
}
array_walk_recursive($r, "maestro_escapize");
print json_encode($r);
?>