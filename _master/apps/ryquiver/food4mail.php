<?php 
/****************************************************************************
* Name:            food4mail.php                                            *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$tocambusa="../../cambusa/";
include_once $tocambusa."rymaestro/maestro_execlib.php";
include_once $tocambusa."phpmailer/class.phpmailer.php";

$env="";
$site="";
$TOOLID="";
$email="";
$text="";

if(isset($_POST["env"]))
    $env=flb_escapize($_POST["env"]);
elseif(isset($_GET["env"]))
    $env=flb_escapize($_GET["env"]);

if(isset($_POST["site"]))
    $site=flb_escapize($_POST["site"]);
elseif(isset($_GET["site"]))
    $site=flb_escapize($_GET["site"]);

if(isset($_POST["toolid"]))
    $TOOLID=flb_escapize($_POST["toolid"]);
elseif(isset($_GET["toolid"]))
    $TOOLID=flb_escapize($_GET["toolid"]);

if(isset($_POST["email"]))
    $email=flb_escapize($_POST["email"]);
elseif(isset($_GET["email"]))
    $email=flb_escapize($_GET["email"]);

if(isset($_POST["text"]))
    $text=flb_escapize($_POST["text"]);
elseif(isset($_GET["text"]))
    $text=flb_escapize($_GET["text"]);
    
$tr = Array();
$tr["\r"]="";
$tr["\n"]="<br>";
$tr["\'"]="'";
$tr["\\\""]="\"";
$tr["\\\\"]="\\";
$text=strtr($text, $tr);
$text=utf8Decode($text);

if($env!="" && $site!=""){
    // APRO IL DATABASE
    $maestro=maestro_opendb($env, false);
    
    // DETERMINO GLI ATTRIBUTI DEL SITO
    maestro_query($maestro, "SELECT SYSID,DEFAULTID FROM QW_WEBSITES WHERE [:UPPER(NAME)]='".strtoupper($site)."'", $s);
    if(count($s)==1){
        // DETERMINAZIONE PARAMETRI DI INVIO
        maestro_query($maestro, "SELECT EMAIL FROM QW_WEBCONTENTS WHERE SYSID='$TOOLID'", $c);
        if(count($c)==1){
            $targetmail=$c[0]["EMAIL"];
        
            $mail=new PHPMailer;
            
            $mail->SMTPDebug=0;
            $mail->Mailer="smtp";
            $mail->From=$email;
            $mail->FromName=$email;

            // CONFIGURAZIONE CUSTOM
            $fileconfig=$path_databases."/_configs/email.php";
            if(file_exists($fileconfig)){
                $recipient=$targetmail;
                include($fileconfig);
            }
            $mail->AddAddress($targetmail);
            $mail->AddReplyTo($email);
            $mail->WordWrap=80;
            $mail->IsHTML(true);
            $mail->Subject="Messaggio di un lettore su $site";
            $mail->Body=$text;
            $mail->AltBody=strip_tags(preg_replace("/<[bh]r\/?>/i", " ", $text));
            
            $mail->SMTPDebug=0;

            // INVIO EFFETTIVO
            if(!@$mail->Send()){
                $food="[0]Invio fallito!";
            }
            else{
                $food="[1]Messaggio inviato!";
            }
        }
        else{
            $food="[0]Servizio non disponibile!";
        }
    }
    else{
        $food="[0]Sito non disponibile!";
    }
    
    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
}
else{
    $food="[0]Ambiente/sito non specificati!";
}

print $food;

function flb_escapize($var){
    return str_replace("'", "''", strtr(trim($var), array("\'" => "'", "\\\"" => "\"", "\\\\" => "\\")));
}
?>