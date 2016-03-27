<?php 
/****************************************************************************
* Name:            qv_system_backup.php                                     *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverfil.php";
include_once $path_cambusa."rymaestro/maestro_querylib.php";
include_once $path_cambusa."tbs_us/plugins/tbs_plugin_opentbs.php";
function qv_system_backup($maestro, $data){
    global $babelcode, $babelparams;
    global $path_databases, $path_cambusa;
    global $global_lastadmin;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // GESTIONE AMMINISTRATORE
        if($global_lastadmin==0){
            $babelcode="QVERR_FORBIDDEN";
            $b_params=array();
            $b_pattern="Autorizzazioni insufficienti";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
        $infoenv=qv_environs($maestro);
        $dirtemp=$infoenv["dirtemp"];
        $dirattach=$infoenv["dirattach"];

        // CARICO LA STRUTTURA DEL DATABASE
        $maestro->loadinfo();

        // APERTURA FILE
        $BACKUPNAME=$maestro->environ . date("YmdHis");
        $pathname=$path_databases."_backup/$BACKUPNAME.QBK";
        $pathnametemp=$path_databases."_backup/$BACKUPNAME.QBK_TMP";
        $fp=fopen($pathnametemp, "wb");
        $version="QBK VERSION 001";
        $rec=str_repeat("0", 18);
        fwrite($fp, $version);
        fwrite($fp, "\n");
        fwrite($fp, $rec);  // Posizione 15+1
        fwrite($fp, "\n");
        
        _qv_progress("Conteggio record...");
        
        // CALCOLO IL TOTALE DEI RECORD PER L'AVANZAMENTO
        $total=0;
        foreach($maestro->infobase as $TABLENAME => $TABLE){
            if(isset($TABLE->type)){
                if($TABLE->type=="database"){
                    $sql="SELECT COUNT(*) AS TOT FROM $TABLENAME";
                    maestro_query($maestro, $sql, $r);
                    if(count($r)==1){
                        $total+=intval($r[0]["TOT"]);
                    }
                }
            }
        }

        // INIZIALIZZO IL CONTATORE PER I MESSAGGI D'AVANZAMENTO
        $counter=0;

        foreach($maestro->infobase as $TABLENAME => $TABLE){
            if(isset($TABLE->type)){
                if($TABLE->type=="database"){
                    $res=maestro_unbuffered($maestro, "SELECT * FROM $TABLENAME ORDER BY SYSID");
                    while( $row=maestro_fetch($maestro, $res) ){
                        $row["_TABLENAME"]=$TABLENAME;
                        if($TABLENAME=="QVFILES"){
                            // INCORPORAZIONE DEL DOCUMENTO
                            $SYSID=$row["SYSID"];
                            $SUBPATH=$row["SUBPATH"];
                            $IMPORTNAME=$row["IMPORTNAME"];
                            $path_parts=pathinfo($IMPORTNAME);
                            if(isset($path_parts["extension"]))
                                $ext="." . $path_parts["extension"];
                            else
                                $ext="";
                            $filedoc=$dirattach.$SUBPATH.$SYSID.$ext;
                            $contents="";
                            if(file_exists($filedoc)){
                                $contents=file_get_contents($filedoc);
                                $contents=base64_encode($contents);
                            }
                            $row["_CONTENTS"]=$contents;
                        }
                        
                        // SERIALIZZAZIONE
                        $buff=serialize($row);
                        $head=substr( str_repeat("0", 18) . strlen($buff), -18 );
            
                        // SCRITTURA
                        fwrite($fp, $head);
                        fwrite($fp, "\n");
                        fwrite($fp, $buff);
                        fwrite($fp, "\n");

                        // AGGIORNO IL CONTATORE
                        $counter+=1;
                    
                        // STATO AVANZAMENTO PER CLIENT
                        $perc=floor(100*$counter/$total);
                        if($perc>100){$perc=100;}
                        _qv_progress("$perc%");
                    }
                    maestro_free($maestro, $res);
                }
            }
        }
        // AGGIORNAMENTO DEL NUMERO DI RECORD
        fseek($fp, 16);
        $buff=substr( str_repeat("0", 18) . $counter, -18 );
        fwrite($fp, $buff);

        // CHIUSURA FILE
        fclose($fp);
        
        // RINOMINO IL FILE
        @rename($pathnametemp, $pathname);
        
        // VARIABILI DI RITORNO
        $babelparams["BACKUP"]="_backup/$BACKUPNAME.QBK";
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