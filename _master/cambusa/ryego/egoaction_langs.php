<?php 
/****************************************************************************
* Name:            egoaction_langs.php                                      *
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

    // DETERMINO L'AZIONE
    if(isset($_POST["action"]))
        $action=ryqEscapize($_POST["action"]);
    else
        $action="";

    // DETERMINO LANG
    if(isset($_POST["lang"]))
        $lang=ryqEscapize($_POST["lang"]);
    else
        $lang="";
        
    // DETERMINO NUOVO LANG
    if(isset($_POST["langnew"]))
        $langnew=ryqEscapize($_POST["langnew"]);
    else
        $langnew="";
    
    $lang=strtolower($lang);
    $langnew=strtolower($langnew);

    // DETERMINO DESCRIPTION
    if(isset($_POST["descr"]))
        $descr=ryqEscapize($_POST["descr"]);
    else
        $descr="";

    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $description="Operazione effettuata";
    $babelcode="EGO_MSG_SUCCESSFUL";

    // APRO IL DATABASE
    $maestro=maestro_opendb("ryego");
    if($maestro->conn!==false){

        // CONTROLLO VALIDITA' SESSIONE
        if(ego_validatesession($maestro, $sessionid, true)==false){
            $success=0;
            $description="Sessione non valida";
            $babelcode="EGO_MSG_INVALIDSESSION";
        }
        
        // CONTROLLI DI CORRETTEZZA REPERIMENTO SYSID
        if($success){
            if($lang!=""){
                // Determino langid
                $sql="SELECT SYSID FROM EGOLANGUAGES WHERE NAME='$lang'";
                maestro_query($maestro, $sql, $r);
                if(count($r)==1)
                    $langid=$r[0]["SYSID"];
                else
                    $langid="";
            }
            else{
                $success=0;
                $description="Seleziona una lingua";
                $babelcode="EGO_MSG_SELECTLANG";
            }
        }
        if($success){
            if($action=="insert" || $action=="update"){
                if($descr==""){
                    $success=0;
                    $description="Descrizione obbligatoria";
                    $babelcode="EGO_MSG_MANDATORYDESCR";
                }
            }
        }
        if($success){
            if($action=="update"){
                if($langnew!=""){
                    if($lang!=$langnew){
                        // Determino langid del nuovo valore di lang per vedere se esiste già
                        $sql="SELECT SYSID FROM EGOLANGUAGES WHERE NAME='$langnew'";
                        maestro_query($maestro, $sql, $r);
                        if(count($r)==1)
                            $langnewid=$r[0]["SYSID"];
                        else
                            $langnewid="";
                    }
                    else{
                        $langnewid="";
                    }
                }
            }
        }
        if($success){
            // BEGIN TRANSACTION
            maestro_begin($maestro);

            switch($action){
                case "insert":
                    if($langid==""){
                        $langid=qv_createsysid($maestro);
                        $sql="INSERT INTO EGOLANGUAGES(SYSID,NAME,DESCRIPTION) VALUES('$langid','$lang','$descr')";
                        maestro_execute($maestro, $sql);
                    }
                    else{
                        $success=0;
                        $description="Lingua già in uso";
                        $babelcode="EGO_MSG_LANGALREADYUSED";
                    }
                    break;
                case "update":
                    if($langnewid==""){
                        if($langid!=""){
                            $sql="UPDATE EGOLANGUAGES SET NAME='$langnew',DESCRIPTION='$descr' WHERE SYSID='$langid'";
                            maestro_execute($maestro, $sql);
                        }
                        else{
                            $success=0;
                            $description="Lingua non valida";
                            $babelcode="EGO_MSG_INVALIDLANG";
                        }
                    }
                    else{
                        $success=0;
                        $description="Lingua già in uso";
                        $babelcode="EGO_MSG_LANGALREADYUSED";
                    }
                    break;
                case "delete":
                    $sql="DELETE FROM EGOLANGUAGES WHERE SYSID='$langid'";
                    maestro_execute($maestro, $sql);
                    break;
            }
            if($success){
                // COMMIT TRANSACTION
                maestro_commit($maestro);
            }
            else{
                // ROLLBACK TRANSACTION
                maestro_rollback($maestro);
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
    $description=$e->getMessage();
    $babelcode="EGO_MSG_UNDEFINED";
}

$description=qv_babeltranslate($description);

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["description"]=$description;
array_walk_recursive($j, "ego_escapize");
print json_encode($j);
?>