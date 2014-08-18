<?php 
/****************************************************************************
* Name:            qv_pages_iconize.php                                     *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_update.php";
include_once $path_cambusa."ryquiver/qv_objects_update.php";
include_once $path_cambusa."ryquiver/quiverfil.php";
function qv_pages_iconize($maestro, $data){
    global $babelcode, $babelparams;
    global $url_cambusa;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
        qv_environs($maestro, $dirtemp, $dirattach);
        
        // DETERMINO L'OPERAZIONE DA EFFETTUARE
        if(isset($data["OPER"])){
            $OPER=$data["OPER"];
            // DETERMINO SYSID
            if(substr($OPER, 1, 1)=="i"){
                qv_solverecord($maestro, $data, "QVARROWS", "SYSID", "", $SYSID);
                if($SYSID==""){
                    $babelcode="QVERR_SYSID";
                    $b_params=array();
                    $b_pattern="Dati insufficienti per individuare la pagina";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
            else{
                qv_solverecord($maestro, $data, "QVOBJECTS", "SYSID", "", $SYSID);
                if($SYSID==""){
                    $babelcode="QVERR_SYSID";
                    $b_params=array();
                    $b_pattern="Dati insufficienti per individuare il sito";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
            $OPER=$data["OPER"];
            if(substr($OPER, 0, 1)=="+"){
                // USO COME ICONA DELLA PAGINA
                // DETERMINO FILEID
                $allegato=qv_solverecord($maestro, $data, "QVFILES", "FILEID", "", $FILEID, "SUBPATH,IMPORTNAME");
                if($FILEID==""){
                    $babelcode="QVERR_FILEID";
                    $b_params=array();
                    $b_pattern="Dati insufficienti per individuare l'allegato";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
                $SUBPATH=$allegato["SUBPATH"];
                $IMPORTNAME=$allegato["IMPORTNAME"];
                
                // DETERMINO L'ESTENSIONE
                $path_parts=pathinfo($IMPORTNAME);
                if(isset($path_parts["extension"]))
                    $ext="." . $path_parts["extension"];
                else
                    $ext="";
                
                // DETERMINO IL PERCORSO
                $path=$dirattach.$SUBPATH.$FILEID.$ext;
                
                // RIDIMENSIONAMENTO L'IMMAGINE
                $buff=file_get_contents($url_cambusa."phpthumb/phpThumb.php?h=80&src=".$path);
                $buff=base64_encode($buff);
                
                //<img alt="" src="data:image/gif;base64,[DATI]" />
            }
            else{
                $buff="";
            }
            
            if(substr($OPER, 1, 1)=="i"){
                // ISTRUZIONE DI AGGIORNAMENTO ICONA
                $datax=array();
                $datax["SYSID"]=$SYSID;
                $datax["ICON"]=$buff;
                $jret=qv_arrows_update($maestro, $datax);
                unset($datax);
                if(!$jret["success"]){
                    return $jret;
                }
            }
            else{
                // ISTRUZIONE DI AGGIORNAMENTO FAVICON
                $datax=array();
                $datax["SYSID"]=$SYSID;
                $datax["FAVICON"]=$buff;
                $jret=qv_objects_update($maestro, $datax);
                unset($datax);
                if(!$jret["success"]){
                    return $jret;
                }
            }
        }
        else{
            $babelcode="QVERR_NOOPER";
            $b_params=array();
            $b_pattern="Operazione non specificata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
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