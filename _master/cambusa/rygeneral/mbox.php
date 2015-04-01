<?php
/****************************************************************************
* Name:            mbox.php                                                 *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "../sysconfig.php";

$global_emailrobot="";

function mbox_login($env, &$err=""){
    global $path_databases, $global_emailrobot;
    include($path_databases."_environs/$env.php");
    $global_emailrobot=$env_robotname;
    $ret=false;
    imap_timeout(IMAP_OPENTIMEOUT, 10);
    imap_timeout(IMAP_READTIMEOUT, 10);
    imap_timeout(IMAP_WRITETIMEOUT, 10);
    imap_timeout(IMAP_CLOSETIMEOUT, 10);
    $ret=@imap_open($env_strconn, $env_user, $env_password);
    // CHIAMO QUESTE FUNZIONI ALTRIMENTI SE LA CASELLA E' VUOTA SI HA UN WARNING
    $err=imap_errors();
    if(is_array($err)){
        $err=implode("|", $err);
    }
    imap_alerts();
    return $ret;
}
function mbox_stat($mbox){
    $check=imap_mailboxmsginfo($mbox);
    return ((array)$check);
}
function mbox_list($mbox){
    $result=array();
    $MC=imap_check($mbox);
    if($MC->Nmsgs>0){
        $range="1:".$MC->Nmsgs;
        $response=imap_fetch_overview($mbox, $range);
        foreach($response as $msg){
            $result[$msg->msgno]=(array)$msg;
        }
    }
    return $result;
}
function mbox_retr($mbox, $message){
    return(imap_fetchheader($mbox, $message, FT_PREFETCHTEXT));
}
function mbox_delete($mbox, $message){
    return(imap_delete($mbox, $message));
}
function mbox_expunge($mbox){
    return(imap_expunge($mbox));
}
function mbox_parse_headers($headers){
    $headers=preg_replace('/\r\n\s+/m', '', $headers);
    preg_match_all('/([^: ]+): (.+?(?:\r\n\s(?:.+?))*)?\r\n/m', $headers, $matches);
    foreach($matches[1] as $key =>$value){
        $result[$value]=$matches[2][$key];
    }
    return($result);
}
function mbox_mime_to_array($mbox, $mid, $parse_headers=false){
    $mail=imap_fetchstructure($mbox, $mid);
    $mail=mbox_get_parts($mbox, $mid, $mail, 0);
    if($parse_headers){
        $mail[0]["parsed"]=mbox_parse_headers($mail[0]["data"]);
    }
    return($mail);
}
function mbox_get_parts($mbox, $mid, $part, $prefix){   
    $attachments=array();
    $attachments[$prefix]=mbox_decode_part($mbox, $mid, $part, $prefix);
    if(isset($part->parts)){ // multipart
        $prefix=($prefix==0)?"":"$prefix.";
        foreach ($part->parts as $number=>$subpart){
            $attachments=array_merge( $attachments, mbox_get_parts($mbox, $mid, $subpart, $prefix.($number+1)) );
        }
    }
    elseif($prefix==0){
        $text=imap_body($mbox, $mid);
        if($part->encoding==3){ // 3 = BASE64
            $text=base64_decode($text);
        }
        elseif($part->encoding==4) { // 4 = QUOTED-PRINTABLE
            $text=quoted_printable_decode($text);
        }
        if(strtoupper($part->subtype)=='PLAIN'){
            // Nessuna azione
        }
        elseif(strtoupper($part->subtype)=='HTML'){
            $attachments[1]["type"]="HTML";
            $text=preg_replace("/[\r\n]/", "", $text);
        }
        $attachments[1]["data"]=$text;
    }
    if($prefix==0){
        // DECODIFICO L'INTESTAZIONE
        $text=$attachments[0]["data"];
        $deco="";
        $elements=imap_mime_header_decode($text);
        for($i=0; $i<count($elements); $i++){
            $deco.=$elements[$i]->text;
        }                
        $attachments[0]["data"]=utf8_encode($deco);
    }
    return $attachments;
}
function mbox_decode_part($mbox, $message_number, $part, $prefix){
    $attachment=array();
    if($part->ifdparameters){
        foreach($part->dparameters as $object) {
            $attr=strtolower($object->attribute);
            $attachment[$attr]=$object->value;
            if($attr=="filename"){
                $attachment['is_attachment']=true;
            }
            elseif($attr=="filename*"){
                $attachment['is_attachment']=true;
                $attachment['filename']=$object->value;
            }
        }
    }
    if($part->ifparameters){
        foreach($part->parameters as $object){
            $attr=strtolower($object->attribute);
            $attachment[$attr]=$object->value;
            if($attr=='name'){
                $attachment['is_attachment']=true;
                if(isset($attachment["filename*"])){
                    $attachment['filename']=str_replace("?", "", utf8_decode(imap_utf8($object->value)));
                }
            }
        }
    }
    $text=imap_fetchbody($mbox, $message_number, $prefix);
    if($part->encoding==3){ // 3 = BASE64
        $text=base64_decode($text);
    }
    elseif($part->encoding==4) { // 4 = QUOTED-PRINTABLE
        $text=quoted_printable_decode($text);
    }
    if(strtoupper($part->subtype)=="PLAIN"){
        // Nessuna azione
    }
    elseif(strtoupper($part->subtype)=='HTML'){
        $attachment["type"]="HTML";
        $text=preg_replace("/[\r\n]/", "", $text);
    }
    $attachment['data']=$text;
    return($attachment);
}
function mbox_close($mbox){
    return(imap_close($mbox));
}
?>