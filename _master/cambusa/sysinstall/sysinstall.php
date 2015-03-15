<?php 
/****************************************************************************
* Name:            sysinstall.php                                           *
* Project:         Cambusa/sysInstall                                       *
* Version:         1.69                                                     *
* Description:     Cambusa installer                                        *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_upgradelib.php";

if(isset($_GET["project"]))
    $project=strtolower(trim($_GET["project"]));
else
    $project="";

if($project==""){
    $project="acme";
}

$project=preg_replace("/[^A-Z0-9_]/i", "}", $project);

set_time_limit(0);

init_rymonad();
init_ryego($project);
init_rypulse($project);
init_rybabel("italiano");
init_rybabel("english");

print "Installazione completata!";

function init_rymonad(){
    global $path_databases;
    
    // SE IL DATABASE E' SQLITE EVENTUALMENTE LO CREO
    maestro_checklite("rymonad");

    // APERTURA DATABASE
    $maestro=maestro_opendb("rymonad");

    if($maestro->conn!==false){
        if(!maestro_istable($maestro, "LASTMONAD")){
        
            maestro_upgrade($maestro);
            
            // PRECARICAMENTO
            $fileinit=$path_databases."/rymonad/rymonad.init";
            if(is_file($fileinit)){
                $rows=file($fileinit);
                foreach($rows as $row){
                    if(trim($row)!=""){
                        maestro_execute($maestro, $row, false);
                    }
                }
                @unlink($fileinit);
            }
        }
    }

    // CHIUSURA DATABASE
    maestro_closedb($maestro);
}

function init_ryego($project){
    global $path_databases;
    
    // SE IL DATABASE E' SQLITE EVENTUALMENTE LO CREO
    maestro_checklite("ryego");

    // APERTURA DATABASE
    $maestro=maestro_opendb("ryego");

    if($maestro->conn!==false){
        if(!maestro_istable($maestro, "EGOALIASES")){
            maestro_upgrade($maestro);
        }
    }
    
    // CAMBIO IL NOME DEL DATABASE
    $sql="UPDATE EGOENVIRONS SET NAME='$project', DESCRIPTION='$project' WHERE SYSID=[:SYSID(0ENVCORSARO0)]";
    maestro_execute($maestro, $sql, false);
    
    // CHIUSURA DATABASE
    maestro_closedb($maestro);
}

function init_rypulse($project){
    global $path_databases;
    
    // SE IL DATABASE E' SQLITE EVENTUALMENTE LO CREO
    maestro_checklite("rypulse");
    
    // APERTURA DATABASE
    $maestro=maestro_opendb("rypulse");

    if($maestro->conn!==false){
        
        if(!maestro_istable($maestro, "ENGAGES")){

            maestro_upgrade($maestro);
    
            // CREAZIONE AZIONE STANDARD MBOX
            $sysid=qv_createsysid($maestro);
            $q="";
            $q.="INSERT INTO ENGAGES(SYSID,NAME,DESCRIPTION,ENABLED,ENGAGE,TOLERANCE,LATENCY,NOTIFY,PARAMS) VALUES(";
            $q.="'".$sysid."',";
            $q.="'MBOX',";
            $q.="'Acquisizione email',";
            $q.="'1',";
            $q.="'@customize/rypulse/engage_mbox.php',";
            $q.="'',";
            $q.="'5MINUTES',";
            $q.="'',";
            $q.="'{\"env\":\"$project\"}'";
            $q.=");";
            maestro_execute($maestro, $q, false);
            
            // CREAZIONE AZIONE STANDARD SCADENZE
            $sysid=qv_createsysid($maestro);
            $q="";
            $q.="INSERT INTO ENGAGES(SYSID,NAME,DESCRIPTION,ENABLED,ENGAGE,TOLERANCE,LATENCY,NOTIFY,PARAMS) VALUES(";
            $q.="'".$sysid."',";
            $q.="'SCADENZE',";
            $q.="'Scadenze',";
            $q.="'1',";
            $q.="'@customize/rypulse/engage_scadenze.php',";
            $q.="'',";
            $q.="'1DAYS',";
            $q.="'',";
            $q.="'{\"env\":\"$project\"}'";
            $q.=");";
            maestro_execute($maestro, $q, false);
        }
    }

    // CHIUSURA DATABASE
    maestro_closedb($maestro);
}
function init_rybabel($lang){

    // SE IL DATABASE E' SQLITE EVENTUALMENTE LO CREO
    maestro_checklite($lang);

    // APERTURA DATABASE
    $maestro=maestro_opendb($lang);

    if($maestro->conn!==false){
        if(!maestro_istable($maestro, "BABELITEMS")){
        
            // GENERAZIONE STRUTTURA
            maestro_upgrade($maestro);
            
            // CARICAMENTO VALORI DA FILE
            $fileload="$lang.txt";
            if(is_file($fileload)){
                $SYSID="";
                $NAME="";
                $CAPTION="";
                $DESCRIPTION="";
                $rows=file($fileload);
                foreach($rows as $row){
                    if(trim($row)!=""){
                        // LA RIGA NON E' VUOTA
                        if(substr($row, 0, 1)=="@"){
                            // INIZIA UN NUOVO BLOCCO
                            if($NAME!=""){
                                // REGISTRO IL VECCHIO
                                $SYSID=qv_createsysid($maestro);
                                maestro_execute($maestro, "INSERT INTO BABELITEMS (SYSID,NAME,CAPTION,DESCRIPTION) VALUES('$SYSID', '$NAME', '$CAPTION', '$DESCRIPTION')", false);
                            }
                            $SYSID="";
                            $NAME="";
                            $CAPTION="";
                            $DESCRIPTION="";
                        }
                        else{
                            if($NAME=="")
                                $NAME=ryqEscapize($row);
                            elseif($CAPTION=="")
                                $CAPTION=ryqEscapize($row);
                            elseif($DESCRIPTION=="")
                                $DESCRIPTION=ryqEscapize($row);
                        }
                    }
                }
                if($NAME!=""){
                    // REGISTRO L'ULTIMO BLOCCO
                    $SYSID=qv_createsysid($maestro);
                    maestro_execute($maestro, "INSERT INTO BABELITEMS (SYSID,NAME,CAPTION,DESCRIPTION) VALUES('$SYSID', '$NAME', '$CAPTION', '$DESCRIPTION')", false);
                }
            }
        }
    }

    // CHIUSURA DATABASE
    maestro_closedb($maestro);
}
?>