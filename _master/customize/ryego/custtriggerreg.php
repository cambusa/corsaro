<?php
include_once $path_cambusa."ryquiver/_quiver.php";
function ego_triggerreg($egoid, $email, $appname, $envname, $rolename, $custom, &$babel, &$failure){
    global $public_sessionid;

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
        $data["UTENTEID"]=$quiverid;
        
        $json=json_decode(quiver_execute($public_sessionid, $envname, false, "objects_insert", $data), true);

        if($json["success"]==0){
        
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