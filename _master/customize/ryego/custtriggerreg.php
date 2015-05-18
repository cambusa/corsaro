<?php
include_once $path_cambusa."ryquiver/_quiver.php";
include_once $path_cambusa."ryego/ego_sendmail.php";
function ego_triggerreg($egoid, $email, $appname, $envname, $rolename, $custom, &$babel, &$failure){
    global $public_sessionid, $postmaster_mail;

    $ret=true;
    
    if(isset($custom["nome"]) && isset($custom["cognome"])){
    
        $quiverid="";
        $maestro=maestro_opendb($envname);
        if($maestro->conn!==false){
            $quiverid=qv_createsysid($maestro);
            maestro_execute($maestro, "INSERT INTO QVUSERS(SYSID,EGOID,USERNAME,ADMINISTRATOR,EMAIL,ARCHIVED) VALUES('$quiverid','$egoid','".ryqEscapize($email)."',0,'".ryqEscapize($email)."',0)");
        }
        maestro_closedb($maestro);

        $data=array();
        $data["TYPOLOGYID"]="0PERSONE000000";
        $data["NOME"]=$custom["nome"];
        $data["COGNOME"]=$custom["cognome"];
        $data["DESCRIPTION"]=$email;
        $data["EMAIL"]=$email;
        $data["UTENTEID"]=$quiverid;

        $params=array();
        $params["sessionid"]=$public_sessionid;
        $params["environ"]=$envname;
        $params["function"]="objects_insert";
        $params["data"]=$data;
        
        $json=json_decode(quiver_execute($params), true);

        if($json["success"]==1){
            $object="Ego - Registrazione nuovo account";
            
            $text="";
            $text.="<html><head><meta charset='utf-8' /></head><body style='font-family:verdana,sans-serif;font-size:13px;'>";
            $text.="<b>Ego - Registrazione nuovo account</b><br><br>";
            $text.="Una nuova registrazione &egrave; stata eseguita su $envname:<br>";
            $text.=$custom["nome"]." ".$custom["cognome"]." - $email<br>";
            $text.="</body><html>";
            
            $m=egomail($postmaster_mail, $object, $text, false);
        }
        else{
            $ret=false;
            $babel=$json["code"];
            $failure=$json["message"];
            
            $maestro=maestro_opendb($envname);
            if($maestro->conn!==false){
                maestro_execute($maestro, "DELETE FROM QVUSERS WHERE SYSID='$quiverid'");
            }
            maestro_closedb($maestro);
        }
    }

    return $ret;
}
?>