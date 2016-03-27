<?php 
/****************************************************************************
* Name:            qv_system_restore.php                                    *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverfil.php";
include_once $path_cambusa."rymonad/monad_lib.php";
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
        
        $total=intval(_readblock($fp, 18));
        fgetc($fp);
        
        _qv_progress("Cancellazione tabelle...");
        
        // CANCELLO LE TABELLE
        foreach($maestro->infobase as $TABLENAME => $TABLE){
            if($TABLE->type=="database"){
                maestro_truncate($maestro, $TABLENAME);
            }
        }
        
        // INIZALIZZO UN VETTORE PER MEMORIZZARE I MASSIMI SYSID DELLE VARIE SERIE
        $series=array();
        
        // INIZIALIZZO IL CONTATORE PER I MESSAGGI D'AVANZAMENTO
        $counter=0;

        for($i=1; $i<=$total; $i++){
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
            // AGGIORNO IL CONTATORE
            $counter+=1;
        
            // STATO AVANZAMENTO PER CLIENT
            $perc=floor(100*$counter/$total);
            if($perc>100){$perc=100;}
            _qv_progress("$perc%");
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