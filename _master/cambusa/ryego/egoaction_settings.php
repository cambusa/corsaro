<?php 
/****************************************************************************
* Name:            egoaction_settings.php                                   *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.69                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
// CARICO LE LIBRERIE
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."ryquiver/quiversex.php";
include_once $tocambusa."ryque/ryq_util.php";
include_once $tocambusa."ryego/ego_util.php";

try{
    // DETERMINO LA SESSIONID
    if(isset($_POST["sessionid"]))
        $sessionid=ryqEscapize($_POST["sessionid"]);
    else
        $sessionid="";

    // DETERMINO DURATION
    if(isset($_POST["duration"]))
        $duration=ryqEscapize($_POST["duration"]);

    // DETERMINO WARNING
    if(isset($_POST["warning"]))
        $warning=ryqEscapize($_POST["warning"]);
        
    // DETERMINO SAVEUSER
    if(isset($_POST["saveuser"]))
        $saveuser=ryqEscapize($_POST["saveuser"]);
        
    // DETERMINO MINLEN
    if(isset($_POST["minlen"]))
        $minlen=ryqEscapize($_POST["minlen"]);

    // DETERMINO DEFAULT
    if(isset($_POST["default"]))
        $default=ryqEscapize($_POST["default"]);

    // DETERMINO UPPERLOWER
    if(isset($_POST["upperlower"]))
        $upperlower=ryqEscapize($_POST["upperlower"]);

    // DETERMINO LETTERDIGIT
    if(isset($_POST["letterdigit"]))
        $letterdigit=ryqEscapize($_POST["letterdigit"]);
        
    // DETERMINO EMAILRESET
    if(isset($_POST["emailreset"]))
        $emailreset=ryqEscapize($_POST["emailreset"]);
        
    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $field=0;
    $description="Le nuove impostazioni sono state registrate";
    $babelcode="EGO_MSG_SETSUCCESSFUL";

    // APRO IL DATABASE
    $maestro=maestro_opendb("ryego");
    if($maestro->conn!==false){

        // CONTROLLO VALIDITA' SESSIONE
        if(ego_validatesession($maestro, $sessionid, true)==false){
            $success=0;
            $description="Sessione non valida";
            $babelcode="EGO_MSG_INVALIDSESSION";
        }

        if($success){
            if(isset($duration))
                maestro_execute($maestro, "UPDATE EGOSETTINGS SET VALUE='$duration' WHERE NAME='duration'");
            if(isset($warning))
                maestro_execute($maestro, "UPDATE EGOSETTINGS SET VALUE='$warning' WHERE NAME='warning'");
            if(isset($saveuser)){
                if($saveuser)
                    $saveuser="1";
                else
                    $saveuser="0";
                maestro_execute($maestro, "UPDATE EGOSETTINGS SET VALUE='$saveuser' WHERE NAME='saveuser'");
            }
            if(isset($minlen))
                maestro_execute($maestro, "UPDATE EGOSETTINGS SET VALUE='$minlen' WHERE NAME='minlen'");
            if(isset($default))
                maestro_execute($maestro, "UPDATE EGOSETTINGS SET VALUE='$default' WHERE NAME='default'");
            if(isset($upperlower)){
                if($upperlower)
                    $upperlower="1";
                else
                    $upperlower="0";
                maestro_execute($maestro, "UPDATE EGOSETTINGS SET VALUE='$upperlower' WHERE NAME='upperlower'");
            }
            if(isset($letterdigit)){
                if($letterdigit)
                    $letterdigit="1";
                else
                    $letterdigit="0";
                maestro_execute($maestro, "UPDATE EGOSETTINGS SET VALUE='$letterdigit' WHERE NAME='letterdigit'");
            }
            if(isset($emailreset)){
                if($emailreset)
                    $emailreset="1";
                else
                    $emailreset="0";
                maestro_execute($maestro, "UPDATE EGOSETTINGS SET VALUE='$emailreset' WHERE NAME='emailreset'");
            }
        }
    }
    else{
        // CONNESSIONE FALLITA
        $success=0;
        $description=$maestro->errdescr;
        $babelcode="EGO_MSG_UNDEFINED";
    }

    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
}
catch(Exception $e){
    $success=0;
    $field=0;
    $description=$e->getMessage();
    $babelcode="EGO_MSG_UNDEFINED";
}

$description=qv_babeltranslate($description);

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["field"]=$field;
$j["description"]=$description;
array_walk_recursive($j, "ego_escapize");
print json_encode($j);
?>