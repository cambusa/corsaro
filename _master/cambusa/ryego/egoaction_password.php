<?php 
/****************************************************************************
* Name:            egoaction_password.php                                   *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.00                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
// CARICO LE LIBRERIE
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."ryego/ego_crypt.php";
include_once $tocambusa."ryquiver/quiversex.php";
include_once $tocambusa."ryque/ryq_util.php";
include_once $tocambusa."phpseclib/Math/BigInteger.php";
include_once $tocambusa."phpseclib/Crypt/RSA.php";
try{
    // INIZIALIZZO LE VARIABILI IN USCITA
    $success=1;
    $field=0;
    $description="Nuova password registrata";

    // PERMUTAZIONE PER PROTEZIONE PASSWORD
    session_start();
    if (isset($_SESSION["ego_privatekey"])){
        $privatekey=$_SESSION["ego_privatekey"];
    }
    else{
        // SESSIONE NON INIZIALIZZATA
        $success=0;
        $field=0;
        $description="Sessione non inizializzata: ricaricare il form di login";
    }
    if($success){
        // DETERMINO LA SESSIONID
        if(isset($_POST["sessionid"]))
            $sessionid=ryqEscapize($_POST["sessionid"]);
        else
            $sessionid="";

        // DETERMINO LA PASSWORD CORRENTE
        if(isset($_POST["currpwd"])){
            $currpwd=ryqEscapize($_POST["currpwd"]);
            $currpwd=decryptString($currpwd, $privatekey);
        }
        else{
            $currpwd=sha1("");
        }

        // DETERMINO LA PASSWORD NUOVA
        if(isset($_POST["newpwd"])){
            $newpwd=ryqEscapize($_POST["newpwd"]);
            $newpwd=decryptString($newpwd, $privatekey);
        }
        else{
            $newpwd=sha1("");
        }

        // DETERMINO LA PASSWORD RIPETUTA
        if(isset($_POST["repeatpwd"])){
            $repeatpwd=ryqEscapize($_POST["repeatpwd"]);
            $repeatpwd=decryptString($repeatpwd, $privatekey);
        }
        else{
            $repeatpwd=sha1("");
        }

        // DETERMINO LA LUNGHEZZA
        if(isset($_POST["lenpwd"]))
            $lenpwd=intval($_POST["lenpwd"]);
        else
            $lenpwd=0;

        // DETERMINO LETTER-DIGIT
        if(isset($_POST["ldpwd"]))
            $ldpwd=intval($_POST["ldpwd"]);
        else
            $ldpwd=0;

        // DETERMINO UPPER-LOWER
        if(isset($_POST["ulpwd"]))
            $ulpwd=intval($_POST["ulpwd"]);
        else
            $ulpwd=0;
            
        if($newpwd==sha1("")){
            $success=0;
            $field=2;
            $description="La password è obbligatoria";
        }
        elseif($newpwd==$repeatpwd){

            if($newpwd!=$currpwd){

                // APRO IL DATABASE
                $maestro=maestro_opendb("ryego");
                if($maestro->conn!==false){
                
                    // CONTROLLO VALIDITA' SESSIONE
                    if(ego_validatesession($maestro, $sessionid, false, "")==false){
                        $success=0;
                        $field=0;
                        $description="Sessione non valida";
                    }
                    
                    if($success){
                        // LEGGO LE OPZIONI
                        $minlen=6;
                        $upperlower=0;
                        $letterdigit=0;
                        $sql="SELECT VALUE FROM EGOSETTINGS WHERE NAME IN('letterdigit','minlen','upperlower') ORDER BY NAME";
                        maestro_query($maestro, $sql, $r);
                        $letterdigit=$r[0]["VALUE"];
                        $minlen=$r[1]["VALUE"];
                        $upperlower=$r[2]["VALUE"];
                        
                        $valid=true;
                        if($lenpwd<$minlen){
                            $success=0;
                            $field=2;
                            $description="Password troppo corta";
                            $valid=false;
                        }
                        if($letterdigit){
                            if($ldpwd==0){
                                $success=0;
                                $field=2;
                                $description="Utilizzare sia lettere che numeri";
                                $valid=false;
                            }
                        }
                        if($upperlower){
                            if($ulpwd==0){
                                $success=0;
                                $field=2;
                                $description="Utilizzare lettere sia maiuscole che minuscole";
                                $valid=false;
                            }
                        }
                        
                        if($valid){
                            // LEGGO LA SESSIONE
                            $sql="SELECT ALIASID FROM EGOSESSIONS WHERE SESSIONID='".$sessionid."' AND ENDTIME IS NULL AND [:DATE(RENEWALTIME, 1DAYS)]>[:TODAY()]";
                            maestro_query($maestro, $sql, $r);
                            if(count($r)==1){
                                $aliasid=$r[0]["ALIASID"];
                                
                                // RECUPERO UserID
                                $sql="SELECT USERID FROM EGOALIASES WHERE SYSID='".$aliasid."'";
                                maestro_query($maestro, $sql, $r);
                                $userid=$r[0]["USERID"];
                                
                                // RECUPERO LA PASSWORD 
                                $sql="SELECT PASSWORD FROM EGOUSERS WHERE SYSID='".$userid."'";
                                maestro_query($maestro, $sql, $r);
                                $pwd=$r[0]["PASSWORD"];
                                
                                if($pwd==$currpwd){
                                    // AGGIORNO LA PASSWORD
                                    $sql="UPDATE EGOUSERS SET PASSWORD='$newpwd',LASTCHANGE=[:TODAY()] WHERE SYSID='$userid'";
                                    maestro_execute($maestro, $sql);
                                }
                                else{
                                    $success=0;
                                    $field=1;
                                    $description="Password errata";
                                }
                            }
                            else{
                                $success=0;
                                $field=0;
                                $description="Sessione non valida";
                            }
                        }
                    }
                }
                else{
                    // CONNESSIONE FALLITA
                    $success=0;
                    $field=0;
                    $description=$maestro->errdescr;
                }

                // CHIUDO IL DATABASE
                maestro_closedb($maestro);
            }
            else{
                $success=0;
                $field=2;
                $description="La nuova password è indentica alla vecchia";
            }
        }
        else{
            $success=0;
            $field=3;
            $description="La password è stata digitata in due modi diversi";
        }
    }
}
catch(Exception $e){
    $success=0;
    $field=0;
    $description=$e->getMessage();
}

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["field"]=$field;
$j["description"]=htmlentities($description);
print json_encode($j);
?>