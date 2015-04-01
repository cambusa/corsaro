<?php 
/****************************************************************************
* Name:            maestro_upgradelib.php                                   *
* Project:         Cambusa/ryMaestro                                        *
* Version:         1.69                                                     *
* Description:     Databases modeling and maintenance                       *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
// CARICO LE LIBRERIE
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_execlib.php";
include_once $tocambusa."rymaestro/maestro_analyze.php";
include_once $tocambusa."ryquiver/quiversex.php";
include_once $tocambusa."rygeneral/json_loader.php";
include_once $tocambusa."rygeneral/writelog.php";

function maestro_upgrade($maestro, $logonly=false){
    global $path_databases, $sqlite3_enabled;
    try{
        // IMPOSTO UN TEMPO DI RISPOSTA ILLIMITATO
        set_time_limit(0);
        
        // IMPOSTO I VALORI DI RITORNO
        $success=1;
        $description="Aggiornamento terminato";

        // APERTURA FILE DI LOG
        log_open(log_unique("maestro"));
        
        // ANALISI DEL DATABASE ATTUALE
        $jbase=MaestroAnalyze($maestro, $s, $d);
            
        // IMPOSTO IL FLAG CHE STABILISCE SE SCRIVERE SOLO IL FILELOG
        $maestro->logonly=$logonly;
    
        if($maestro->master!=""){
            if(substr($maestro->master,0,1)=="/" || substr($maestro->master,1,1)==":"){
                $base=dirname($maestro->master)."/";
                $json=basename($maestro->master);
            }
            else{
                $base=$path_databases."_maestro/";
                $json=$maestro->master;
            }
            
            // LETTURA DOCUMENTO JSON
            $infobase=json_load($base, $json);
            
            if($jbase!=""){
                $prevbase=json_decode($jbase);

                // ANALISI MODELLO MAESTRO: GESTIONE DELLE VERSIONI
                $version_old=false;
                $version_current=0;
                $version_table="";
                $version_keyname="";
                $version_keyvalue="";
                $version_keyothers="";
                $version_dataname="";
                $version_dataothers="";
                for(reset($infobase); $table=current($infobase); next($infobase)){
                    if(isset($table->enabled))
                        $enabled=intval($table->enabled);
                    else
                        $enabled=1;
                    if($enabled){
                        if(isset($table->type)){
                            if($table->type=="version"){
                                if(isset($table->current) && 
                                   isset($table->table) && 
                                   isset($table->keyname) &&
                                   isset($table->keyvalue) &&
                                   isset($table->dataname)){
                                    $version_current=$table->current;
                                    $version_table=$table->table;
                                    $version_keyname=$table->keyname;
                                    $version_keyvalue=$table->keyvalue;
                                    $version_dataname=$table->dataname;
                                    if(isset($table->keyothers))
                                        $version_keyothers=",".$table->keyothers;
                                    if(isset($table->dataothers))
                                        $version_dataothers=",".$table->dataothers;
                                    // REPERISCO LA VECCHIA VERSIONE DEL DATABASE
                                    switch($maestro->provider){
                                    case "oracle":
                                    case "db2odbc":
                                        $version_table=strtoupper($version_table);
                                        break;
                                    case "mysql":
                                        $version_table=strtolower($version_table);
                                        break;
                                    }
                                    if(isset($prevbase->{$version_table})){
                                        $sql="SELECT ".$version_dataname." FROM ".$version_table." WHERE ".$version_keyname."='".$version_keyvalue."'";
                                        maestro_query($maestro, $sql, $r, false);
                                        if(count($r)==1){
                                            if(isset($r[0][$version_dataname])){
                                                $version_old=intval($r[0][$version_dataname]);
                                            }
                                        }
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
                
                // ANALISI MODELLO MAESTRO: DEFINIZIONE TABELLE
                for(reset($infobase); $table=current($infobase); next($infobase)){
                    if(isset($table->enabled))
                        $enabled=intval($table->enabled);
                    else
                        $enabled=1;
                    if($enabled){
                        if(isset($table->type) && isset($table->fields)){
                            if($table->type=="database"){
                                $tabname=key($infobase);
                                switch($maestro->provider){
                                case "oracle":
                                case "db2odbc":
                                    $tabname=strtoupper($tabname);
                                    break;
                                case "mysql":
                                    $tabname=strtolower($tabname);
                                    break;
                                }
                                $fields=$table->fields;
                                if(isset($prevbase->{$tabname})){
                                    // LA TABELLA ESISTE: CERCO CAMPI AGGIUNTIVI
                                    
                                    $hard=false;    // Flag che indica una gestione "dura" per SQLite
                                    $oldcols="";    // Elenco colonne per la gestione "dura"
                                    
                                    for(reset($fields); $field=current($fields); next($fields)){
                                        $fieldname=key($fields);
                                        if(!isset($prevbase->{$tabname}->fields->{$fieldname})){
                                            $sql="ALTER TABLE ".$tabname." ADD ";
                                            
                                            switch($maestro->provider){
                                            case "sqlite":
                                                $sql.="COLUMN ";    // Utile per SQLite3
                                                if(!$sqlite3_enabled){
                                                    $hard=true;
                                                }
                                                break;
                                            }
                                            
                                            if(isset($field->type)){$tp=$field->type;}else{$tp="VARCHAR";}
                                            if(isset($field->size)){$sz=$field->size;}else{$sz=0;}
                                            if(isset($field->key)){$ky=$field->key;}else{$ky=0;}
                                            if(isset($field->unique)){$uq=$field->unique;}else{$uq=0;}
                                            if(isset($field->notnull)){$nn=$field->notnull;}else{$nn=0;}
                                            if(isset($field->default)){$df=$field->default;}else{$df=0;}    // non gestito
                                            
                                            $dbtype=maestro_solvetype($maestro, $tp, $sz, $ky, $nn, $uq);
                                            
                                            switch($maestro->provider){
                                            case "oracle":
                                            case "db2odbc":
                                                $sql.=strtoupper($fieldname)." ".$dbtype;
                                                break;
                                            default:
                                                $sql.=$fieldname." ".$dbtype;
                                            }
                                            if(!$hard)
                                                maestro_execupgrade($maestro, $sql);
                                        }
                                        else{
                                            switch($maestro->provider){
                                            case "sqlite":
                                                if($oldcols!="")
                                                    $oldcols.=",";
                                                $oldcols.=$fieldname;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    if($hard){
                                        // IL DATABASE (SQLite) NON SUPPORTA ALTER TABLE
                                        // BEGIN
                                        maestro_begin($maestro, false);

                                        // NOME DI TABELLA TEMPORANEA PER LA CONVERSIONE
                                        $tabnametemp=$tabname."__TEMP";
                                        
                                        // SE LA TABELLA ESISTE PER FALLITE ESECUZIONI, LA DROPPO
                                        if(maestro_istable($maestro, $tabnametemp)){
                                            $sql="DROP TABLE $tabnametemp";
                                            maestro_execupgrade($maestro, $sql);
                                        }
                                        
                                        // CREATE TABLE {TEMPTABLE} AS SELECT * FROM {OLDTABLE}
                                        $sql="CREATE TABLE $tabnametemp AS SELECT * FROM $tabname";
                                        maestro_execupgrade($maestro, $sql);

                                        // DROP TABLE {OLDTABLE}
                                        $sql="DROP TABLE $tabname";
                                        maestro_execupgrade($maestro, $sql);

                                        // CREATE TABLE {OLDTABLE}
                                        maestro_createtable($maestro, $tabname, $fields);

                                        // INSERT INTO {OLDTABLE} (SYSID,...) SELECT SYSID,... FROM {TEMPTABLE}
                                        $sql="INSERT INTO $tabname ($oldcols) SELECT $oldcols FROM $tabnametemp";
                                        maestro_execupgrade($maestro, $sql);

                                        // DROP TABLE {TEMPTABLE}
                                        $sql="DROP TABLE $tabnametemp";
                                        maestro_execupgrade($maestro, $sql);

                                        // COMMIT
                                        maestro_commit($maestro, false);
                                    }
                                }
                                else{
                                    // LA TABELLA NON ESISTE: LA CREO
                                    maestro_createtable($maestro, $tabname, $fields);
                                }
                                // GESTIONE VERSIONI
                                if(isset($table->versions)){
                                    if($version_current>0){
                                        // IL DATABASE GESTISCE LE VERSIONI
                                        if(isset($prevbase->{$tabname})){
                                            if($version_old!==false)
                                                $verold=$version_old;
                                            else
                                                $verold=0;
                                        }
                                        else{
                                            // LA TABELLA E' STATA APPENA CREATA: ESEGUO TUTTE LE VERSIONI
                                            $verold=0;
                                        }
                                        $versions=$table->versions;
                                        for(reset($versions); $version=current($versions); next($versions)){
                                            if(isset($version->version))
                                                $ver=$version->version;
                                            else
                                                $ver=999999;
                                            if($ver>$verold){
                                                maestro_versioning($maestro, $version);
                                            }
                                        }
                                    }
                                    else{
                                        // IL DATABASE NON GESTISCE LE VERSIONI
                                        // MI ASPETTO VERSIONI A ZERO DA ESEGUIRE ALLA CREAZIONE DELLA TABELLA
                                        if(!isset($prevbase->{$tabname})){
                                            $versions=$table->versions;
                                            for(reset($versions); $version=current($versions); next($versions)){
                                                if(isset($version->version))
                                                    $ver=$version->version;
                                                else
                                                    $ver=999999;
                                                if($ver==0){
                                                    maestro_versioning($maestro, $version);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if(function_exists("upgrade_progress")){
                        upgrade_progress();
                    }
                }
                
                // ANALISI MODELLO MAESTRO: GENERAZIONE DELLE VISTE
                for(reset($infobase); $table=current($infobase); next($infobase)){
                    if(isset($table->enabled))
                        $enabled=intval($table->enabled);
                    else
                        $enabled=1;
                    if($enabled){
                        if(isset($table->type) && isset($table->script)){
                            if($table->type=="view"){
                                $tabname=key($infobase);
                                $namefile=$table->script;
                                $namefile=str_replace("@maestro/",$path_databases."_maestro/",$namefile);
                                if(is_file($namefile))
                                    $script=file_get_contents($namefile);
                                else
                                    $script="";
                                if($script!=""){
                                    // CONTROLLO SE LA VISTA ESISTE GIA' PER DROPPARLA
                                    if(maestro_istable($maestro, $tabname)){
                                        $sql="DROP VIEW ".$tabname;
                                        maestro_execupgrade($maestro, $sql);
                                    }
                                    maestro_execupgrade($maestro, $script);
                                }    
                            }
                        }
                    }
                    if(function_exists("upgrade_progress")){
                        upgrade_progress();
                    }
                }
                if($version_current>0){
                    // AGGIORNO IL DATABASE CON LA NUOVA VERSIONE
                    if($version_old!==false)
                        $sql="UPDATE $version_table SET $version_dataname='$version_current' WHERE $version_keyname='$version_keyvalue'";
                    else
                        $sql="INSERT INTO $version_table (SYSID,TAG,$version_keyname,$version_dataname $version_keyothers) VALUES([:SYSID],'SYSTEM','$version_keyvalue','$version_current' $version_dataothers)";
                    maestro_execupgrade($maestro, $sql);
                }
            }
            else{
                $success=0;
                $description=$d;
            }
        }
        else{
            $success=0;
            $description="Modello non specificato";
        }

        // CHIUSURA FILE DI LOG
        log_close();
    }
    catch(Exception $e){
        $success=0;
        $description=$e->getMessage();
    }
    $j=array();
    $j["success"]=$success;
    $j["description"]=$description;
    return $j;
}
function maestro_execupgrade($maestro, $sql){
    if(substr($sql,0,2)!="--"){
        if($maestro->logonly){
            $sql=maestro_macro($maestro, $sql);
            log_write($sql.";");
        }
        else{
            // ESECUZIONE STRINGA SQL
            if(maestro_execute($maestro, $sql, false)){
                log_write($sql.";");
            }
        }
    }
}

function maestro_createtable($maestro, $tabname, $fields){
    // LA TABELLA NON ESISTE: LA CREO
    $init=false;
    
    switch($maestro->provider){
    case "oracle":
    case "db2odbc":
        $sql="CREATE TABLE ".strtoupper($tabname)."(";
        break;
    case "mysql":
        $sql="CREATE TABLE ".strtolower($tabname)."(";
        break;
    default:
        $sql="CREATE TABLE ".$tabname."(";
    }
            
    for(reset($fields); $field=current($fields); next($fields)){
        $fieldname=key($fields);
        if($init)
            $sql.=",";
        else
            $init=true;
        
        if(isset($field->type)){$tp=$field->type;}else{$tp="VARCHAR";}
        if(isset($field->size)){$sz=$field->size;}else{$sz=0;}
        if(isset($field->key)){$ky=$field->key;}else{$ky=0;}
        if(isset($field->unique)){$uq=$field->unique;}else{$uq=0;}
        if(isset($field->notnull)){$nn=$field->notnull;}else{$nn=0;}
        if(isset($field->default)){$df=$field->default;}else{$df="";}  // non gestito
        
        $dbtype=maestro_solvetype($maestro, $tp, $sz, $ky, $nn, $uq);
        
        switch($maestro->provider){
        case "oracle":
        case "db2odbc":
            $sql.=strtoupper($fieldname)." ".$dbtype;
            break;
        default:
            $sql.=$fieldname." ".$dbtype;
        }
    }
    $sql.=")";
    maestro_execupgrade($maestro, $sql);
}
function maestro_checklite($env){
    global $path_databases;
    if(is_file($path_databases."_environs/".$env.".php")){
        include($path_databases."_environs/".$env.".php");
        if($env_provider=="sqlite"){
            if(!is_file($env_strconn)){
                $conn=@x_sqlite_open($env_strconn, $errdescr);
                @x_sqlite_close($conn);
            }
        }
    }
}
function maestro_versioning($maestro, $version){
    if(isset($version->sql)){
        $sql=$version->sql;
        if(is_array($sql)){
            $jump=0;
            foreach($sql as $q){
                $ok=true;
                // VALUTO SE E' UN'ISTRUZIONE DA SALTARE
                switch($maestro->provider){
                case "oracle":
                case "db2odbc":
                    // INDICE SU NAME IN ORACLE E' IMPLICITO
                    if(preg_match("/^CREATE INDEX .+\(NAME\);?$/i", $q)){
                        $ok=false;
                    }
                    break;
                }
                if($ok){
                    // TRASFORMO LE ENTITÀ HTML E POI LE CODIFICO UTF8
                    $h=html_entity_decode($q);
                    if($h!=$q){
                        $q=utf8_encode($h);
                    }
                    if(maestro_querytype($maestro, $q)){
                        maestro_query($maestro, $q, $r, false);
                        if(isset($r[0]["JUMPVALUE"]))
                            $jump=intval($r[0]["JUMPVALUE"]);
                        else
                            $jump=0;
                        unset($r);
                    }
                    else{
                        if($jump==0)
                            maestro_execupgrade($maestro, $q);
                        else
                            $jump-=1;
                    }
                }
            }
        }
        else{
            maestro_execupgrade($maestro, $sql);
        }
    }
}
?>