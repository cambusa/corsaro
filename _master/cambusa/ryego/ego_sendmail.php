<?php
/****************************************************************************
* Name:            ego_sendmail.php                                         *
* Project:         Cambusa/ryEgo                                            *
* Version:         1.00                                                     *
* Description:     Central Authentication Service (CAS)                     *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_execlib.php";
include_once $tocambusa."ryque/ryq_util.php";
include_once $tocambusa."phpmailer/class.phpmailer.php";

function egomail($user, $object, $text){
    global $postmaster_mail,$path_databases;
    
    $success=1;
    $description="Invio riuscito";
    try{
        // APRO IL DATABASE
        $maestro=maestro_opendb("ryego");
        if($maestro->conn!==false){
            $userupper=strtoupper(ryqEscapize($user));
            $sql="SELECT EMAIL AS EMAIL FROM EGOALIASES WHERE [:UPPER(NAME)]='$userupper'";
            maestro_query($maestro, $sql, $v);
            if(count($v)==1){   // Esistenza utente
                $mailaddress=trim($v[0]["EMAIL"]);
                if($mailaddress!=""){     // Email impostata
                    $mail = new PHPMailer;
                    $mail->SMTPDebug=0;
                    $mail->Mailer="smtp";
                    $mail->From=$postmaster_mail;
                    $mail->FromName="Autenticazione Ego";

                    // CONFIGURAZIONE CUSTOM
                    $fileconfig=$path_databases."_configs/email.php";
                    if(file_exists($fileconfig)){
                        $recipient=$mailaddress;
                        include($fileconfig);
                    }
                    $mail->AddAddress($mailaddress);
                    $mail->WordWrap=80;
                    $mail->IsHTML(true);    // Set email format to HTML
                    $mail->Subject=$object;
                    $mail->Body=$text;
                    $mail->AltBody=strip_tags($text);

                    // INVIO EFFETTIVO
                    if(!@$mail->Send()){
                        $success=0;
                        $description=$mail->ErrorInfo;
                    }
                }
                else{
                    $success=0;
                    $description="Indirizzo email non impostato";
                }
            }
            else{
                $success=0;
                $description="Utente inestistente";
            }
        }
        else{
            // CONNESSIONE FALLITA
            $success=0;
            $description=$maestro->errdescr;
        }
        
        // CHIUDO IL DATABASE
        maestro_closedb($maestro);
    }
    catch(Exception $e){
        $success=0;
        $description=$e->getMessage();
    }
    // USCITA ARRAY
    $j=array();
    $j["success"]=$success;
    $j["description"]=htmlentities($description);
    return $j;
}
?>