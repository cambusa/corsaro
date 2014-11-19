<?php 
/****************************************************************************
* Name:            qv_forum_insert.php                                      *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_update.php";
include_once $path_cambusa."ryquiver/qv_selections_add.php";
include_once $path_cambusa."ryego/ego_sendmail.php";
include_once $path_applications."ryquiver/qv_pages_insert.php";
include_once $path_applications."ryquiver/qv_pages_indicize.php";
include_once $path_applications."ryquiver/food4_library.php";
function qv_forum_insert($maestro, $data){
    global $babelcode, $babelparams;
    global $global_quiveruserid;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $url=food4containerURL();
        $p=strpos($url, "/ryquiver");
        if($p!==false){
            $url=substr($url, 0, $p-8);
        }
        $url.="/apps/corsaro/filibuster.php";
        
        if(isset($data["SITEID"]))
            $SITEID=$data["SITEID"];
        else
            $SITEID="";
        
        if(isset($data["PARENTID"]))
            $PARENTID=$data["PARENTID"];
        else
            $PARENTID="";
        
        if(isset($data["DESCRIPTION"]))
            $DESCRIPTION=$data["DESCRIPTION"];
        else
            $DESCRIPTION="(nuovo contenuto)";

        if(isset($data["REGISTRY"]))
            $REGISTRY=$data["REGISTRY"];
        else
            $REGISTRY="";
            
        // REPERIMENTO SITO
        $sql="SELECT NAME,DESCRIPTION FROM QW_WEBSITES WHERE SYSID='$SITEID'";
        maestro_query($maestro, $sql, $r);
        if(count($r)>0){
            $SITENAME=$r[0]["NAME"];
            $SITEDESCR=$r[0]["DESCRIPTION"];
        }
        else{
            $babelcode="QVERR_FORUMSITE";
            $b_params=array();
            $b_pattern="Sito non trovato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
            
        // ISTRUZIONE DI CREAZIONE DI UNA PAGINA
        $datax=array();
        $datax["SITEID"]=$SITEID;
        $jret=qv_pages_insert($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $PAGEID=$jret["SYSID"];
        
        // TIMESTAMP
        $DESCRDATE=date("d/m/Y H:i");
        
        // REPERIMENTO PERSONA
        $sql="SELECT SYSID,NOME,COGNOME FROM QW_PERSONE WHERE UTENTEID='$global_quiveruserid'";
        maestro_query($maestro, $sql, $r);
        if(count($r)>0){
            $CURRUTENTE=$r[0]["NOME"]." ".$r[0]["COGNOME"];
        }
        else{
            $babelcode="QVERR_FORUMPERSON";
            $b_params=array();
            $b_pattern="Persona non trovata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // ISTRUZIONE DI AGGIORNAMENTO PAGINA
        $datax=array();
        $datax["SYSID"]=$PAGEID;
        $datax["DESCRIPTION"]=$DESCRIPTION;
        $datax["REGISTRY"]=$REGISTRY;
        $datax["SCOPE"]="0";
        $datax["ABSTRACT"]=$DESCRDATE." - ".$CURRUTENTE;
        $datax["REFERENCE"]="mlit";
        $jret=qv_arrows_update($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        
        // REPERISCO L'AGGANCIO DELLA PAGINA PARENT
        $sql="SELECT SETRELATED FROM QW_WEBCONTENTS WHERE SYSID='$PARENTID'";
        maestro_query($maestro, $sql, $r);
        if(count($r)>0){
            $PARENTRELATED=$r[0]["SETRELATED"];
        }
        else{
            $babelcode="QVERR_FORUMPARENT";
            $b_params=array();
            $b_pattern="Genitore non trovato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // ISTRUZIONE DI CORRELAZIONE
        $datax=array();
        $datax["PARENTTABLE"]="QW_WEBCONTENTS";
        $datax["PARENTFIELD"]="SETRELATED";
        $datax["SELECTEDTABLE"]="QVARROWS";
        $datax["PARENTID"]=$PARENTRELATED;
        $datax["SELECTION"]=$PAGEID;
        $jret=qv_selections_add($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        
        // INDIVIDUAZIONE DELL'AUTORE DEL POST PARENT PER INVIO EMAIL
        $sql="SELECT USERINSERTID FROM QW_WEBCONTENTS WHERE SYSID='$PARENTID'";
        maestro_query($maestro, $sql, $r);
        if(count($r)>0){
            $USERINSERTID=$r[0]["USERINSERTID"];
            // LETTURA QW_PERSONE
            $sql="SELECT EMAIL FROM QW_PERSONE WHERE UTENTEID='$USERINSERTID'";
            maestro_query($maestro, $sql, $r);
            if(count($r)>0){
                $email=$r[0]["EMAIL"];
                
                $object="Forum $SITEDESCR";
                $url.="?env=".$maestro->environ."&site=$SITENAME&id=$PAGEID";
                
                $text="";
                $text.="<html><head><meta charset='utf-8' /></head><body style='font-family:verdana,sans-serif;font-size:13px;'>";
                $text.="<b>$CURRUTENTE</b> ha risposto a un tuo post:<br><br>";
                $text.="<a href='$url'>$url</a><br>";
                $text.="</body><html>";
                
                $m=egomail($email, $object, $text, false);
                
                /*
                // ISTRUZIONE DI INVIO EMAIL
                $datax=array();
                $datax["SYSID"]=$PAGEID;
                $datax["TABLE"]="QVARROWS";
                $datax["MAILTABLE"]="QW_PERSONE";
                $datax["RECIPIENTS"]=$RECIPIENTS;
                $jret=qv_sendmail($maestro, $datax);
                unset($datax);
                */
            }
        }
        
        // VARIABILI DI RITORNO
        $babelparams["PAGEID"]=$PAGEID;
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