<?php 
if(preg_match("/[^a-z]pec[.]/i", $recipient)){
    $mail->Mailer="smtp";
    $mail->Host="smtps.pec.aruba.it";
    $mail->Port=465;

    $mail->Timeout='10';

    $mail->SMTPAuth=true;
    $mail->SMTPsecure="SSL";
    $mail->Username="rodolfo.calzetti@pec.rudyz.net";
    $mail->Password="";
    $mail->SetFrom("rodolfo.calzetti@pec.rudyz.net", 'Rodolfo Calzetti');
}
else{
    /*
    $mail->SMTPAuth=true;
    $mail->Username="postmaster@rudyz.net";
    $mail->Password="";
    $mail->Host="smtp.rudyz.net";
    $mail->Port=25;
    */
}
?>