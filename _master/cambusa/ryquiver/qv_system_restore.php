<?php 
/****************************************************************************
* Name:            qv_system_restore.php                                    *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverfil.php";
include_once "../rymonad/monad_lib.php";
function qv_system_restore($maestro, $data){
    global $babelcode, $babelparams;
    global $path_databases, $url_rymonad, $path_root, $url_base;
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
        
        // IMPOSTO IL DEFAULT PER DIRECTORY ALLEGATI
        $dirattach="";
        
        // CARICO LA STRUTTURA DEL DATABASE
        $maestro->loadinfo();
        
        if(isset($data["BACKUP"])){
            $BACKUP=$data["BACKUP"];
            $pathname=$path_databases."_backup/$BACKUP";
        }
        else{
            $babelcode="QVERR_BACKUP";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il backup";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // LA RESTORE PUO' ESSERE FATTO SOLTANTO CON MONAD LOCALE
        if($url_rymonad!=""){
            $babelcode="QVERR_MONAD";
            $b_params=array();
            $b_pattern="La restore può essere fatta soltanto con Monad locale";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // APERTURA FILE
        $fp=fopen($pathname, "rb");
        
        $version=_readblock($fp, 15);
        fgetc($fp);
        
        $counter=intval(_readblock($fp, 18));
        fgetc($fp);
        
        // UTILE PER AVANZAMENTO DA CLIENT
        print substr( ($counter+1) . str_repeat(" ", 100), 0, 100);
        flush();
        
        // CANCELLO LE TABELLE
        foreach($maestro->infobase as $TABLENAME => $TABLE){
            if($TABLE->type=="database"){
                $sql="DELETE FROM $TABLENAME";
                maestro_execute($maestro, $sql, false);
            }
        }
        
        // INIZALIZZO UN VETTORE PER MEMORIZZARE I MASSIMI SYSID DELLE VARIE SERIE
        $series=array();
        
        for($i=1; $i<=$counter; $i++){
            $blocklen=intval(_readblock($fp, 18));
            fgetc($fp);
            
            $block=_readblock($fp, $blocklen);
            fgetc($fp);
            
            $row=unserialize($block);
            
            $TABLENAME=$row["_TABLENAME"];
            
            if($TABLENAME=="QVSETTINGS"){
                if(isset($row["NAME"]) && isset($row["DATAVALUE"])){
                    if($row["NAME"]=="_FILEENVIRON"){
                        $fileenviron=$row["DATAVALUE"];
                        if($fileenviron!=""){
                            if(is_file($path_databases."_environs/".$fileenviron.".php")){
                                $env_strconn="";
                                include($path_databases."_environs/".$fileenviron.".php");
                                $dirattach=$env_strconn;
                            }
                        }
                    }
                    elseif($row["NAME"]=="_ENVIRONID"){
                        $row["DATAVALUE"]="";
                    }
                }
            }

            if( isset($maestro->infobase->{$TABLENAME}) ){
                $table=$maestro->infobase->{$TABLENAME};
                $fields=$table->fields;
                $columns="";
                $values="";
                $clobs=false;
                foreach($fields as $field => $attr){
                    if(isset($row[$field])){
                        $value=$row[$field];
                        // TIPIZZAZIONE
                        switch($attr->type){
                        case "INTEGER":
                        case "RATIONAL":
                        case "BOOLEAN":
                            if(!is_numeric($value)){
                                $value="0";
                            }
                            break;
                        case "DATE":
                        case "TIMESTAMP":
                            $value=qv_strtime($value);
                            if(strlen($value)<8){
                                $value=LOWEST_TIME;
                            }
                            $value="[:TIME($value)]";
                            break;
                        case "TEXT":
                        case "JSON":
                            qv_setclob($maestro, $field, $value, $value, $clobs);
                            break;
                        default:
                            if(strpos($attr->type, "RATIONAL(")!==false){
                                if(!is_numeric($value)){
                                    $value="0";
                                }
                            }
                            elseif(strpos($attr->type, "SYSID")!==false){
                                if($value!=""){
                                    $value=qv_actualid($maestro, $value);
                                    $serie=substr($value, 0, 1);
                                    if($serie!="0"){
                                        if(isset($series[$serie])){
                                            if($value>$series[$serie]){
                                                $series[$serie]=$value;
                                            }
                                        }
                                        else{
                                            $series[$serie]=$value;
                                        }
                                    }
                                }
                                $value="'$value'";
                            }
                            elseif(strpos($attr->type, "JSON(")!==false){
                                qv_setclob($maestro, $field, $value, $value, $clobs);
                            }
                            else{
                                $value=ryqEscapize($value);
                                $value="'$value'";
                            }
                            break;
                        }
                        if($columns!="")
                            $columns.=",";
                        $columns.=$field;
                        if($values!="")
                            $values.=",";
                        $values.=$value;
                    }
                }
                // ESECUZIONE QUERY
                $sql="INSERT INTO $TABLENAME($columns) VALUES($values)";
                if(maestro_execute($maestro, $sql, false, $clobs)){
                    if($TABLENAME=="QVFILES"){
                        if($dirattach!=""){
                            // ESPANDO L'ALLEGATO
                            $contents=$row["_CONTENTS"];
                            if($contents!=""){
                                $SYSID=$row["SYSID"];
                                $SUBPATH=$row["SUBPATH"];
                                $IMPORTNAME=$row["IMPORTNAME"];
                                $path_parts=pathinfo($IMPORTNAME);
                                if(isset($path_parts["extension"]))
                                    $ext="." . $path_parts["extension"];
                                else
                                    $ext="";
                                if($SUBPATH!=""){
                                    qv_makepath($dirattach, $SUBPATH);
                                }
                                $filedoc=$dirattach.$SUBPATH.$SYSID.$ext;
                                $contents=base64_decode($contents);
                                $contents=file_put_contents($filedoc, $contents);
                            }
                        }
                    }
                }
            }
            // UTILE PER AVANZAMENTO DA CLIENT
            print str_repeat("X", 100);
            flush();
        }
        // CHIUSURA FILE
        fclose($fp);
        
        // ADEGUO MONAD PER POTER GESTIRE CORRETTAMENTE I NUOVI SYSID
        foreach($series as $serie => $id){
            monadset($id);
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

function _readblock($fp, $len){
    $buff="";
    for($i=0; $i<$len; $i++){
        $buff.=fgetc($fp);
    }
    return $buff;
}
?>