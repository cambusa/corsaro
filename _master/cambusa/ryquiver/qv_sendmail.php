<?php 
/****************************************************************************
* Name:            qv_sendmail.php                                          *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverfil.php";
include_once $path_cambusa."phpmailer/class.phpmailer.php";
function qv_sendmail($maestro, $data){
    global $babelcode, $babelparams, $postmaster_mail, $path_databases;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
        qv_environs($maestro, $dirtemp, $dirattach);
        
        // DETERMINO IL SYSID DEL RECORD CON I DATI DA INVIARE
        if(isset($data["SYSID"])){
            $SYSID=ryqEscapize($data["SYSID"]);
        }
        else{
            $babelcode="QVERR_NODATA";
            $b_params=array("NAME" => "SYSID");
            $b_pattern="Dati insufficienti [{1}]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINO LA TABELLA RELATIVA A SYSID
        if(isset($data["TABLE"])){
            $TABLE=ryqEscapize($data["TABLE"]);
        }
        else{
            $babelcode="QVERR_NODATA";
            $b_params=array("NAME" => "TABLE");
            $b_pattern="Dati insufficienti [{1}]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // LEGGO TITOLO E CONTENUTO
        $sql="SELECT DESCRIPTION,REGISTRY FROM $TABLE WHERE SYSID='$SYSID'";
        maestro_query($maestro, $sql, $r);
        if(count($r)==1){
            $subject=$r[0]["DESCRIPTION"];
            $body=$r[0]["REGISTRY"];
            if($body==""){
                $body="***";
            }
        }
        else{
            $babelcode="QVERR_NODATA";
            $b_params=array();
            $b_pattern="Record dati inesistente";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // SE SONO DEFINITE DELLE SOSTITUIZIONI, PERFEZIONO I DATI DA INVIARE
        if(isset($data["ARGS"])){
            $ARGS=$data["ARGS"];
            foreach($ARGS as $ind => $subst){
                if(isset($subst["FIND"]) && isset($subst["REPLACE"])){
                    $FIND=$subst["FIND"];
                    $REPLACE=$subst["REPLACE"];
                    $subject=str_replace($FIND, $REPLACE, $subject);
                    $body=str_replace($FIND, $REPLACE, $body);
                }
                elseif(isset($subst["SUBJECT"])){
                    $subject=$subst["SUBJECT"].$subject;
                }
            }
        }
        
        // DETERMINO LA TABELLA RELATIVA ALLE EMAIL
        if(isset($data["MAILTABLE"])){
            $MAILTABLE=ryqEscapize($data["MAILTABLE"]);
        }
        else{
            $babelcode="QVERR_NODATA";
            $b_params=array("NAME" => "MAILTABLE");
            $b_pattern="Dati insufficienti [{1}]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINO IL SYSID DEL RECORD CON L'INDIRIZZO DEL MITTENTE
        if(isset($data["SENDERID"])){
            $SENDERID=ryqEscapize($data["SENDERID"]);
            // REPERISCO L'INDIRIZZO DEL MITTENTE
            $sql="SELECT DESCRIPTION,EMAIL FROM $MAILTABLE WHERE SYSID='$SENDERID'";
            maestro_query($maestro, $sql, $r);
            if(count($r)==1){
                $fromname=$r[0]["DESCRIPTION"];
                $from=$r[0]["EMAIL"];
            }
            else{
                $babelcode="QVERR_NODATA";
                $b_params=array();
                $b_pattern="Mittente inesistente";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $fromname="Quiver Mailer";
            //$from=$postmaster_mail;
            $from="";
        }

        // DETERMINO I SYSID DEI RECORD DEI DESTINATARI
        if(isset($data["RECIPIENTS"])){
            $RECIPIENTS=ryqEscapize($data["RECIPIENTS"]);
        }
        else{
            $babelcode="QVERR_NODATA";
            $b_params=array("NAME" => "RECIPIENTS");
            $b_pattern="Dati insufficienti [{1}]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // REPERISCO GLI INDIRIZZI DEI DESTINATARI
        $toarr=array();
        $recs="'" . str_replace("|", "','", $RECIPIENTS) . "'";
        $sql="SELECT EMAIL FROM $MAILTABLE WHERE SYSID IN ($recs) AND EMAIL<>''";
        maestro_query($maestro, $sql, $r);
        if(count($r)>0){
            for($i=0; $i<count($r); $i++){
                if(!in_array($r[$i]["EMAIL"], $toarr)){
                    $toarr[]=$r[$i]["EMAIL"];
                }
            }
        }
        $babelparams["RECIPIENTS"]=count($toarr);
        
        if(count($toarr)>0){
            // REPERISCO I FILE ALLEGATI A SYSID
            $attachments=array();
            $where="TABLENAME='$TABLE' AND RECORDID='$SYSID'";
            maestro_query($maestro, "SELECT FILEID,SUBPATH,IMPORTNAME FROM QWFILES WHERE $where", $r);
            if(count($r)>0){
                for($i=0; $i<count($r); $i++){
                    // nome file
                    $FILEID=$r[$i]["FILEID"];
                    $namefile=$r[$i]["IMPORTNAME"];
                    // estensione
                    $path_parts=pathinfo($dirtemp.$namefile);
                    if(isset($path_parts["extension"]))
                        $ext="." . $path_parts["extension"];
                    else
                        $ext="";
                    // sottocartella
                    $subpath=$r[$i]["SUBPATH"];
                    // percorso completo
                    $pathfile=$dirattach.$subpath.$FILEID.$ext;
                    // aggiungo allegato
                    $attachments[]=$pathfile;
                }
            }
            
            // ISTANZIO PHPMAILER
            $mail = new PHPMailer;
            //$mail->IsSMTP();                                      // Set mailer to use SMTP
            
            /* POSTA NORMALE
            $mail->Host = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup server
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'jswan';                            // SMTP username
            $mail->Password = 'secret';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
            */

            /* PEC
            $mail->Mailer = "smtp";
            $mail->Host = "smtps.pec.aruba.it"; // SMTP server
            $mail->Port = 465;
            // $mail->SMTPSecure = 'tls';

            $mail->Timeout= '120';

            $mail->SMTPAuth = true; // turn on SMTP authentication
            $mail->Username = "xxx@pec.luclapec.com"; // SMTP username
            $mail->Password = "xxx"; // SMTP password
            */  
            
            $mail->SMTPDebug=0;
            $mail->Mailer="smtp";
            $mail->From=$from;
            $mail->FromName=$fromname;

            // CONFIGURAZIONE CUSTOM
            $fileconfig=$path_databases."_configs/email.php";
            if(file_exists($fileconfig)){
                $recipient=$toarr[0];
                include($fileconfig);
            }

            if(isset($data["AUTO"])){
                if(valint($data["AUTO"])!=0){
                    $mail->AddAddress($from);
                }
            }
            
            $mail->AddReplyTo($from);
            //$mail->AddCC('cc@example.com');
            for($i=0; $i<count($toarr); $i++){
                $mail->AddBCC($toarr[$i]);
            }
            $mail->WordWrap=80;
            for($i=0; $i<count($attachments); $i++){
                $mail->AddAttachment($attachments[$i]);
            }
            $mail->IsHTML(true);    // Set email format to HTML
            $mail->Subject=$subject;
            $mail->Body=$body;
            $mail->AltBody=strip_tags($body);

            // INVIO EFFETTIVO
            if(!@$mail->Send()){
                $babelcode="QVERR_MAILFAILED";
                $success=2;
                $message=$mail->ErrorInfo;
                if($mail->SMTPDebug>0){
                    writelog($message);
                }
                $b_params=array("MAILERR" => $message);
                $b_pattern="Invio email fallito: {1}";
                //throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                qv_babeltranslate($b_pattern, $b_params);
            }
        }
    }
    catch(Exception $e){
        $success=0;
        $message=$e->getMessage();
    }
    // USCITA JSON
    $j=array();
    $j["success"]=$success;
    $j["code"]=$babelcode;
    $j["params"]=$babelparams;
    $j["message"]=$message;
    $j["SYSID"]=$SYSID;
    return $j; //ritorno standard
}
?>