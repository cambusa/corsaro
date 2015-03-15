<?php
/****************************************************************************
* Name:            rygeoload.php                                            *
* Project:         Cambusa/sysInstall                                       *
* Version:         1.69                                                     *
* Description:     Cambusa installer                                        *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_upgradelib.php";

set_time_limit(0);

// SE IL DATABASE E' SQLITE EVENTUALMENTE LO CREO
maestro_checklite("rygeography");

// APERTURA DATABASE
$maestro=maestro_opendb("rygeography");

if($maestro->conn!==false){
    if(!maestro_istable($maestro, "GEOCONTINENTI")){
        maestro_upgrade($maestro);
        geo_load($maestro);
    }
}

// CHIUSURA DATABASE
maestro_closedb($maestro);

function geo_load($maestro){

    $currdir=realpath(dirname(__FILE__));
    
    $tr=array();
    $tr[" "]="_";
    $tr["'"]="_";
    $tr["-"]="_";
    $tr["ù"]="u";
    $tr["é"]="e";
    $tr["è"]="e";
    $tr["à"]="a";
    $tr["ì"]="i";
    $tr["ò"]="o";
    $tr[" "]="";
    $tr["\r"]="";
    $tr["\n"]="";
    
    log_open(log_unique("geography"));
    
    // CARICAMENTO CONTINENTI
    $continenti=array();
    $buff=file("$currdir/continenti.txt");
    foreach($buff as $row){
        $SYSID=qv_createsysid($maestro);
        $NAME=strtoupper(strtr($row, $tr));
        $DESCRIPTION=ryqEscapize($row);
        $sql="INSERT INTO GEOCONTINENTI(SYSID,NAME,DESCRIPTION,REGISTRY) VALUES('$SYSID', '$NAME', '$DESCRIPTION', '')";
        maestro_execute($maestro, $sql, false);

        $continenti[$NAME]=$SYSID;
    }

    // PREPARAZIONE SIGLE NAZIONI
    $nazioni=array();
    $buff=file("$currdir/nazionisigle.txt");
    foreach($buff as $row){
        $fields=explode("|", $row);
        $NAME=strtoupper(strtr($fields[0], $tr));

        $nazioni[$NAME]=array($fields[1], $fields[2], $fields[3]);
    }
    
    // CARICAMENTO NAZIONI
    $nazioni2=array();
    $buff=file("$currdir/nazioni.txt");
    foreach($buff as $row){
        $fields=explode("|", $row);
        $SYSID=qv_createsysid($maestro);
        $NAME=strtoupper(strtr($fields[0], $tr));
        $DESCRIPTION=ryqEscapize($fields[0]);
        
        // CODICI
        $ALFADUE="";
        $ALFATRE="";
        $NUMERICO="";
        if(isset($nazioni[$NAME])){
            $ALFADUE=$nazioni[$NAME][0];
            $ALFATRE=$nazioni[$NAME][1];
            $NUMERICO=$nazioni[$NAME][2];
        }
        
        // CONTINENTE
        $CONTINENTEID="";
        $CONTINENTENAME=strtoupper(strtr($fields[1], $tr));
        if(isset($continenti[$CONTINENTENAME])){
            $CONTINENTEID=$continenti[$CONTINENTENAME];
        }
        $NAME.="_".$CONTINENTENAME;
        
        $sql="INSERT INTO GEONAZIONI(SYSID,NAME,DESCRIPTION,REGISTRY,ALFADUE,ALFATRE,NUMERICO,CONTINENTEID) VALUES('$SYSID', '$NAME', '$DESCRIPTION', '', '$ALFADUE', '$ALFATRE', '$NUMERICO', '$CONTINENTEID')";
        maestro_execute($maestro, $sql, false);
        
        $nazioni2[$NAME]=$SYSID;
    }
    
    // CARICAMENTO REGIONI
    $regioni=array();
    $buff=file("$currdir/regioni.txt");
    foreach($buff as $row){
        $SYSID=qv_createsysid($maestro);
        $NAME=strtoupper(strtr($row, $tr));
        $DESCRIPTION=ryqEscapize($row);
        
        // NAZIONE
        $NAZIONEID="";
        $NAZIONENAME="ITALY_EUROPE";
        if(isset($nazioni2[$NAZIONENAME])){
            $NAZIONEID=$nazioni2[$NAZIONENAME];
        }
        
        $sql="INSERT INTO GEOREGIONI(SYSID,NAME,DESCRIPTION,REGISTRY,NAZIONEID) VALUES('$SYSID', '$NAME', '$DESCRIPTION', '', '$NAZIONEID')";
        maestro_execute($maestro, $sql, false);

        $regioni[$NAME]=$SYSID;
    }

    // CARICAMENTO PROVINCE E COMUNI
    $province=array();
    $buff=file("$currdir/comuni.txt");
    foreach($buff as $row){
        $fields=explode("|", $row);
        
        // GESTIONE PROVINCIA
        $PROVINCIA=ryqEscapize($fields[2]);
        $PROVINCIANAME=strtoupper(strtr($fields[2], $tr));
        $PROVINCIASIGLA=$fields[3];
        $REGIONENAME=strtoupper(strtr($fields[4], $tr));
        
        // NAZIONE
        $NAZIONEID="";
        $NAZIONENAME="ITALY_EUROPE";
        if(isset($nazioni2[$NAZIONENAME])){
            $NAZIONEID=$nazioni2[$NAZIONENAME];
        }
        
        if(isset($province[$PROVINCIANAME])){
            $PROVINCIAID=$province[$PROVINCIANAME];
        }
        else{
            $PROVINCIAID=qv_createsysid($maestro);
            
            // REGIONE
            $REGIONEID="";
            if(isset($regioni[$REGIONENAME])){
                $REGIONEID=$regioni[$REGIONENAME];
            }
            
            $sql="INSERT INTO GEOPROVINCE(SYSID,NAME,DESCRIPTION,REGISTRY,SIGLA,NAZIONEID,REGIONEID) VALUES('$PROVINCIAID', '$PROVINCIANAME', '$PROVINCIA', '', '$PROVINCIASIGLA', '$NAZIONEID', '$REGIONEID')";
            if(!maestro_execute($maestro, $sql, false)){
                log_write($maestro->errdescr);
            }
            
            $province[$PROVINCIANAME]=$PROVINCIAID;
        }
        
        $SYSID=qv_createsysid($maestro);
        $NAME=strtoupper(strtr($fields[1], $tr))."_".$PROVINCIANAME;
        $DESCRIPTION=ryqEscapize($fields[1]);
        $CAP=substr($fields[0], 0 ,5);
        
        $sql="INSERT INTO GEOCOMUNI(SYSID,NAME,DESCRIPTION,REGISTRY,CAP,NAZIONEID,PROVINCIAID) VALUES('$SYSID', '$NAME', '$DESCRIPTION', '', '$CAP', '$NAZIONEID', '$PROVINCIAID')";
        if(!maestro_execute($maestro, $sql, false)){
            log_write($maestro->errdescr);
        }
        
        print str_repeat(" ", 100);
        flush();
    }
    
    log_close();
}
?>