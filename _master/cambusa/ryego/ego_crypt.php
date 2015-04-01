<?php
/****************************************************************************
* Name:            ego_crypt.php                                            *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.69                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function decryptString($s, $privatekey){
    $r="";
    try{
        $rsa=new Crypt_RSA();
        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
        $rsa->loadKey($privatekey);
        $r=@$rsa->decrypt( base64_decode($s) );
        unset($rsa);
    }
    catch(Exception $e){}
    if($s!="" && $r==""){
        // SONO PROBABILMENTE NELLA SITUAZIONE IN CUI CHIAVE PRIVATA E CHIAVE PUBBLICA NON SONO CONGRUENTI
        $r="######";
    }
    return $r;
}
function prepareEncrypt($maestro, &$publickey){
    // CRITTOGRAFIA PER PROTEZIONE PASSWORD
    $privatekey="";
    $publickey="";
    
    if($maestro->conn!==false){
        // ELIMINAZIONE RECORD SCADUTI
        $sql="DELETE FROM EGOENCRYPTIONS WHERE [:TIME(RENEWALTIME,24HOURS)]<[:NOW()]";
        maestro_execute($maestro, $sql, false);
        
        // RECUPERO USERCOOKIE SE ESISTE
        if(isset($_COOKIE['USERCOOKIE'])){
            $USERCOOKIE=$_COOKIE['USERCOOKIE'];
            $sql="SELECT * FROM EGOENCRYPTIONS WHERE USERCOOKIE='$USERCOOKIE'";
            maestro_query($maestro, $sql, $r);
            if(count($r)>0){
                $SYSID=$r[0]["SYSID"];
                $privatekey=$r[0]["RSAPRIVATEKEY"];
                $publickey=$r[0]["RSAPUBLICKEY"];
                
                $sql="UPDATE EGOENCRYPTIONS SET RENEWALTIME=[:NOW()] WHERE SYSID='$SYSID'";
                maestro_execute($maestro, $sql, false);
            }
        }
        
        // SE LE CHIAVI NON SONO STATE RISOLTE LE CREO
        if($publickey==""){
            // CREO UN ID UNIVOCO
            $USERCOOKIE="K".date("YmdHis");
            for($i=1; $i<=2; $i++){
                $USERCOOKIE.=monadrand();
            }
            do{
                $sql="SELECT SYSID FROM EGOENCRYPTIONS WHERE USERCOOKIE='$USERCOOKIE'";
                maestro_query($maestro, $sql, $r);
                if(count($r)>0){
                    $USERCOOKIE=substr($USERCOOKIE, 0, 20).monadrand();
                }
                else{
                    break;
                }
            }while(true);
            setcookie("USERCOOKIE", $USERCOOKIE, time()+4000000);
            
            $rsa=new Crypt_RSA();
            $keypair=$rsa->createKey();
            $privatekey=ryqEscapize($keypair["privatekey"]);
            $publickey=ryqEscapize($keypair["publickey"]);
            unset($rsa);
            
            $SYSID=qv_createsysid($maestro);
            $CLIENTIP=get_ip_address();
            
            $sql="INSERT INTO EGOENCRYPTIONS(SYSID,USERCOOKIE,CLIENTIP,RSAPRIVATEKEY,RSAPUBLICKEY,RENEWALTIME) VALUES('$SYSID','$USERCOOKIE','$CLIENTIP','$privatekey','$publickey',[:NOW()])";
            maestro_execute($maestro, $sql, false);
        }
    }
}
function solvePrivateKey($maestro, &$privatekey, &$success, &$description, &$babelcode){
    // CRITTOGRAFIA PER PROTEZIONE PASSWORD
    $publickey="";
    $privatekey="";
    
    if($maestro->conn!==false){
          // RECUPERO USERCOOKIE SE ESISTE
        if(isset($_COOKIE['USERCOOKIE'])){
            $USERCOOKIE=$_COOKIE['USERCOOKIE'];
            $sql="SELECT * FROM EGOENCRYPTIONS WHERE USERCOOKIE='$USERCOOKIE'";
            maestro_query($maestro, $sql, $r);
            if(count($r)>0){
                $SYSID=$r[0]["SYSID"];
                $privatekey=$r[0]["RSAPRIVATEKEY"];
                $publickey=$r[0]["RSAPUBLICKEY"];
                
                $sql="UPDATE EGOENCRYPTIONS SET RENEWALTIME=[:NOW()] WHERE SYSID='$SYSID'";
                maestro_execute($maestro, $sql, false);
            }
        }
        if($privatekey==""){
            // SESSIONE NON INIZIALIZZATA
            $success=0;
            $description="Sessione non inizializzata: ricaricare il form di login";
            $babelcode="EGO_MSG_UNINITSESSION";
        }
    }
}
?>