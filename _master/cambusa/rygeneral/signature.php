<?php
/****************************************************************************
* Name:            signature.php                                            *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "../sysconfig.php";
function signature_p7m($pathfile){
    global $path_databases;
    
    include($path_databases."ryssl/sslconfig.php");
    
    $cacert=realpath($path_databases."ryssl/cacert.pem");
    $cakey=realpath($path_databases."ryssl/cakey.pem");
    
    $pathfile=realpath($pathfile);
    openssl_pkcs7_sign(
        $pathfile,
        $pathfile.".p7m", 
        "file://".$cacert,
        array("file://".$cakey, $ssl_password),
        array("SIGNATURE" => date("Y-m-d H:i:s")),
        PKCS7_BINARY
    );
    return $pathfile.".p7m";
}
function extract_p7m($pathfile){
    if(substr(strtolower($pathfile), -4)==".p7m"){
        $newfile=substr($pathfile, 0, -4);
        $ext=strtolower(pathinfo($newfile, PATHINFO_EXTENSION));
        $buffer=file_get_contents($pathfile);
        
        // TOLGO L'INTESTAZIONE
        $pos=strpos($buffer, "\n");
        $buffer=substr($buffer, $pos+1);
        $pos=strpos($buffer, "\n");
        $buffer=substr($buffer, $pos+1);
        $pos=strpos($buffer, "\n");
        $buffer=substr($buffer, $pos+1);
        $pos=strpos($buffer, "\n");
        $buffer=substr($buffer, $pos+1);
        $pos=strpos($buffer, "\n");
        $buffer=substr($buffer, $pos+1);
        
        switch($ext){
        case "pdf":
        case "wmv":
            $buffer=substr($buffer, (1+8*8)+(1+8*3));
            break;
        default:
            $buffer=substr($buffer, (1+8*8)+(1+8*2));
        }
        
        // TOLGO LA CODA
        switch($ext){
        case "wmv":
            $buffer=substr($buffer, 0, -1005);
            break;
        case "pdf":
        case "odt":
            $buffer=substr($buffer, 0, -1007);
            break;
        default:
            $buffer=substr($buffer, 0, -1005);
        }

        // CONVERTO
        $buffer=base64_decode($buffer);
        $buffer=substr($buffer, 2);

        $fp=fopen($newfile, "wb");
        fwrite($fp, $buffer);
        fclose($fp);
    }
    else{
        $newfile=$pathfile;
    }
    return $newfile;
}
?>