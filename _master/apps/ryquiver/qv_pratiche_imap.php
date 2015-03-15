<?php 
/****************************************************************************
* Name:            qv_pratiche_imap.php                                     *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/quiverfil.php";
include_once $path_cambusa."ryquiver/qv_files_insert.php";
include_once $path_cambusa."ryquiver/qv_files_attach.php";
include_once $path_cambusa."ryquiver/qv_objects_insert.php";
include_once $path_cambusa."ryquiver/qv_sendmail.php";
include_once $path_cambusa."rygeneral/mbox.php";
include_once $path_applications."ryquiver/qv_pratiche_insert.php";
include_once $path_applications."ryquiver/pratiche_date.php";
include_once $path_applications."ryquiver/qv_attivita_insert.php";
function qv_pratiche_imap($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    global $path_customize, $global_emailrobot;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        // CONTROLLO CHE SIA DEFINITO IL FILTRO EMAIL
        $inc=$path_customize."ryquiver/emailfilter.php";
        if(is_file($inc)){
            include_once $inc;
            $funct="email_filter";
            if(!function_exists($funct)){
                $babelcode="QVERR_NOFUNCTION";
                $b_params=array("ERROR" => $funct);
                $b_pattern="Funzione filtro non definita: [{1}]";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $babelcode="QVERR_NOFILTER";
            $b_params=array("ERROR" => $inc);
            $b_pattern="Filtro email inesistente: [{1}]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
        qv_environs($maestro, $dirtemp, $dirattach);
        
        // INSERIMENTO AUTOMATICO ATTORI
        $autoattori=intval(qv_setting($maestro, "_EMAILAUTOATTORI", "0"));
        
        // DETERMINO IL NOME DELLA CASELLA
        if(isset($data["MAILBOX"]))
            $MAILBOX=$data["MAILBOX"];
        else
            $MAILBOX="mailbox";
        
        // APRO LA POSTA
        $mbox=mbox_login($MAILBOX, $err);
        if(!$mbox){
            $babelcode="QVERR_IMAPCONNECT";
            $b_params=array("ERROR" => $err);
            $b_pattern="Errori in apertura della casella: [{1}]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // CERCO DI RISOLVERE L'ATTORE ROBOT
        $ROBOTID="";
        if($global_emailrobot!=""){
            $sql="SELECT SYSID FROM QW_ATTORI WHERE [:UPPER(NAME)]='".strtoupper($global_emailrobot)."'";
            maestro_query($maestro, $sql, $r);
            if(count($r)>0){
                $ROBOTID=$r[0]["SYSID"];
            }
        }

        // SCARICO LA LISTA DELLE EMAIL
        $lista=mbox_list($mbox);
        
        // INIZIALIZZO LA LISTA DEI MESSAGGI DA ELIMINARE
        $messaggi=array();

        // SCANDISCO LA LISTA
        foreach($lista as $key => $mail){
            // REPERISCO ID E NUMERO DEL MESSAGGIO
            $uniqueid="";
            if(isset($mail["message_id"])){
                $uniqueid=$mail["message_id"];
            }
            $mid=intval($mail["msgno"]);
            
            // LEGGO IL MESSAGGIO
            $messaggio=mbox_mime_to_array($mbox, $mid);
            
            // DETERMINO DESCRIPTION E EMAIL
            $DESCRIPTION="(senza titolo)";
            $EMAIL="";
            $from="";
            if(isset($messaggio[0]["data"])){
                $dt=$messaggio[0]["data"];
                if(preg_match("/Subject: (.*)[\n\r]/i", $dt, $m)){
                    $DESCRIPTION=$m[1];
                }
                if(preg_match("/From: (.*)[\n\r]/i", $dt, $m)){
                    $from=$m[1];
                }
            }
            if($from!=""){
                if(preg_match("/<([^<>]+)>/i", $from, $m)){
                    $EMAIL=$m[1];
                }
                elseif(strpos($from, "@")!==false){
                    $EMAIL=$from;
                }
            }
            
            // DETERMINO RICHIEDENTEID
            $RICHIEDENTEID="";
            $RICHIEDENTE="";
            if($EMAIL!=""){
                $sql="SELECT SYSID,DESCRIPTION FROM QW_ATTORI WHERE EMAIL='$EMAIL'";
                maestro_query($maestro, $sql, $r);
                if(count($r)==1){
                    $RICHIEDENTEID=$r[0]["SYSID"];
                    $RICHIEDENTE=$r[0]["DESCRIPTION"];
                }
                elseif(count($r)==0 && $autoattori!=0){
                    // INSERISCO L'ATTORE
                    $datax=array();
                    $datax["DESCRIPTION"]=$EMAIL;
                    $datax["EMAIL"]=$EMAIL;
                    $datax["TYPOLOGYID"]=qv_actualid($maestro, "0ATTORI00000");
                    $jret=qv_objects_insert($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        writelog($jret["message"]);
                        return $jret;
                    }
                    $RICHIEDENTEID=$jret["SYSID"];
                    $RICHIEDENTE=$EMAIL;
                }
            }

            if($RICHIEDENTEID!=""){
                // INIZIALIZZO LE VARIABILI PER UNA EVENTUALE EMAIL DI RISPOSTA
                $ATTOREID="";
                $PRATICAID="";
            
                // DETERMINO REGISTRY
                $REGISTRY="";
                // Cerco HTML
                foreach($messaggio as $k => $d){
                    if($k!=0 && isset($d["type"])){
                        $REGISTRY=$d["data"];
                        break;
                    }
                }
                if($REGISTRY==""){
                    // Cerco PLAIN
                    foreach($messaggio as $k => $d){
                        if($k!=0 && !isset($d["is_attachment"])){
                            $REGISTRY=$d["data"];
                            break;
                        }
                    }
                }
                // Estraggo il corpo
                if(preg_match("/<body [^>]*>([^\\x00]*)<.body>/i", $REGISTRY, $m)){
                    $REGISTRY=trim($m[1]);
                }
                
                // CHIAMO IL FILTRO PERSONALIZZATO PER STABILIRE SE
                // UNA PRATICA DEVE ESSERE CREATA
                $params=array();
                $params["DESCRIPTION"]=$DESCRIPTION;
                $params["REGISTRY"]=$REGISTRY;
                $params["EMAIL"]=$EMAIL;
                $params["RICHIEDENTEID"]=$RICHIEDENTEID;
                $params["RICHIEDENTE"]=$RICHIEDENTE;
                $options=array();
                if( $funct($maestro, $params, $options) ){
                    // TRAVASO INVERSO
                    $DESCRIPTION=$params["DESCRIPTION"];
                    $REGISTRY=$params["REGISTRY"];
                    
                    // DETERMINO PROCESSOID
                    $processo=qv_solverecord($maestro, $options, "QW_PROCESSI", "PROCESSOID", "PROCESSONAME", $PROCESSOID, "*");
                    if($PROCESSOID==""){
                        $babelcode="QVERR_PROCESSOID";
                        $b_params=array();
                        $b_pattern="Dati insufficienti per individuare il processo";
                        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                    }
                    
                    // DETERMINO PRATICAID
                    $pratica=qv_solverecord($maestro, $options, "QW_PRATICHEJOIN", "PRATICAID", "", $PRATICAID, "ATTOREID");
                    if($PRATICAID!=""){
                        $ATTOREID=$pratica["ATTOREID"];
                    }
                    
                    // DETERMINO GLI ALLEGATI
                    $allegati=array();
                    $descrizioni=array();
                    foreach($messaggio as $i => $d){
                        if( isset($d["is_attachment"]) && isset($d["filename"]) ){
                            if(intval($d["is_attachment"])){
                                // FILE ALLEGATO
                                $filename=$d["filename"];
                                $descrizioni[]=$filename;
                                
                                // EVENTUALE RIDENOMINAZIONE
                                $filename=qv_fileuniquity($dirtemp, $filename);
                                $allegati[]=$filename;
                                
                                // SALVATAGGIO NELLA DIRECTORY TEMPORANEA
                                $pathname=$dirtemp.$filename;
                                $buff=$d["data"];
                                $fp=fopen($pathname, "wb");
                                fwrite($fp, $buff);
                                fclose($fp);
                            }
                        }
                    }
                    
                    if($PRATICAID==""){
                        // APRO UNA PRATICA
                        $datax=array();
                        $datax["PROCESSOID"]=$PROCESSOID;
                        $datax["RICHIEDENTEID"]=$RICHIEDENTEID;
                        $datax["DESCRIPTION"]="$RICHIEDENTE [!SYSID]";
                        $jret=qv_pratiche_insert($maestro, $datax);
                        unset($datax);
                        if(!$jret["success"]){
                            writelog($jret["message"]);
                            return $jret;
                        }
                        $PRATICAID=$jret["params"]["PRATICAID"];
                        $ATTOREID=$jret["params"]["ATTOREID"];
                    }
                    
                    // CREAZIONE DELLA ATTIVITA' DI RICHIESTA
                    $datax=array();
                    $datax["OPERATION"]="INSERT";
                    $datax["PRATICAID"]=$PRATICAID;
                    $datax["DESCRIPTION"]=$DESCRIPTION;
                    $datax["REGISTRY"]=$REGISTRY;
                    $datax["MOTIVEID"]=qv_actualid($maestro, "0MOTATTRICH0");
                    $datax["BOWID"]=$RICHIEDENTEID;
                    $datax["TARGETID"]=$ATTOREID;
                    $datax["CONSISTENCY"]=0;
                    $jret=qv_attivita_insert($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        writelog($jret["message"]);
                        return $jret;
                    }
                    // PIPE
                    $ARROWID=$jret["params"]["ARROWID"];
                    
                    // CICLO SUGLI ALLEGATI
                    foreach($allegati as $i => $filename){
                        // IMPORTO IL FILE
                        $datax=array();
                        $datax["IMPORTNAME"]=$filename;
                        $datax["SUBPATH"]=substr($ARROWID, -2);
                        $datax["DESCRIPTION"]=htmlentities($descrizioni[$i]);
                        $jret=qv_files_insert($maestro, $datax);
                        unset($datax);
                        if(!$jret["success"]){
                            writelog($jret["message"]);
                            return $jret;
                        }
                        $FILEID=$jret["SYSID"];

                        // ALLEGO IL FILE ALLA RICHIESTA
                        $datax=array();
                        $datax["TABLENAME"]="QVARROWS";
                        $datax["FILEID"]=$FILEID;
                        $datax["RECORDID"]=$ARROWID;
                        $jret=qv_files_attach($maestro, $datax);
                        unset($datax);
                        if(!$jret["success"]){
                            writelog($jret["message"]);
                            return $jret;
                        }
                    }
                    
                    // ANNOVERO IL NUMERO MESSAGGIO TRA QUELLI DA ELIMINARE
                    // SUL SERVER DI POSTA
                    $messaggi[]=$mid;
                    
                    // FACCIO RIMBALZARE L'EMAIL IN INGRESSO AL PROPRIETARIO DELLA PRATICA
                    $datax=array();
                    $datax["TABLE"]="QVARROWS";
                    $datax["SYSID"]=$ARROWID;
                    $datax["MAILTABLE"]="QW_ATTORI";
                    $datax["SENDERID"]=$RICHIEDENTEID;
                    $datax["RECIPIENTS"]=$ATTOREID;
                    $jret=qv_sendmail($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        // Non lo considero errore bloccante
                        //return $jret;
                        writelog($jret["message"]);
                    }
                    
                    if($ROBOTID!=""){
                        // DETERMINO RISPOSTAID
                        $RISPOSTAID="";
                        if(isset($options["RISPOSTANAME"])){
                            $RISPOSTANAME=strtoupper($options["RISPOSTANAME"]);
                            maestro_query($maestro, "SELECT SYSID FROM QVARROWS WHERE [:UPPER(NAME)]='$RISPOSTANAME'", $r);
                            if(count($r)==1){
                                $RISPOSTAID=$r[0]["SYSID"];
                            }
                        }
                        // EVENTUALE RISPOSTA VIA EMAIL
                        if($RISPOSTAID!=""){
                            $datax=array();
                            $datax["TABLE"]="QVARROWS";
                            $datax["SYSID"]=$RISPOSTAID;
                            $datax["MAILTABLE"]="QW_ATTORI";
                            $datax["SENDERID"]=$ROBOTID;
                            $datax["RECIPIENTS"]=$RICHIEDENTEID;
                            $datax["ARGS"]=array();
                            $datax["ARGS"][]=array("FIND" => "[!PRATICAID]", "REPLACE" => "[$PRATICAID]" );
                            $jret=qv_sendmail($maestro, $datax);
                            unset($datax);
                            if(!$jret["success"]){
                                // Non lo considero errore bloccante
                                //return $jret;
                                writelog($jret["message"]);
                            }
                        }
                    }
                }
            }
        }
        // ELIMINO I MESSAGGI INSERITI COME PRATICHE
        foreach($messaggi as $i => $mid){
            mbox_delete($mbox, $mid);
        }
        mbox_expunge($mbox);

        // CHIUDO LA POSTA
        mbox_close($mbox);
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