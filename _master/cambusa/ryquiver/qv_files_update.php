<?php 
/****************************************************************************
* Name:            qv_files_update.php                                      *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverinf.php";
include_once "quiverfil.php";
function qv_files_update($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // RISOLVO LE INFO DI SESSIONE
        qv_infosession($maestro);
        
        // INDIVIDUAZIONE RECORD
        $sets="";
        qv_solverecord($maestro, $data, "QVFILES", "SYSID", "NAME", $SYSID);
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // SE NAME E' IN MODIFICA LO VALIDO
        if(isset($data["SYSID"]) && isset($data["NAME"])){
            $NAME=$data["NAME"];
            qv_checkname($maestro, "QVFILES", $SYSID, $NAME);
            qv_appendcomma($sets, "NAME='$NAME'");
        }
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize(qv_inputUTF8($data["DESCRIPTION"]), 100);
            if($DESCRIPTION=="")
                $DESCRIPTION=$NAME;
            qv_appendcomma($sets,"DESCRIPTION='$DESCRIPTION'");
        }

        // DETERMINO REGISTRY
        $clobs=false;
        if(isset($data["REGISTRY"])){
            qv_setclob($maestro, "REGISTRY", $data["REGISTRY"], $REGISTRY, $clobs);
            qv_appendcomma($sets,"REGISTRY=$REGISTRY");
        }

        // DETERMINO AUXTIME
        if(isset($data["AUXTIME"])){
            $AUXTIME=qv_escapizetime($data["AUXTIME"], LOWEST_TIME);
            $AUXTIME="[:DATE($AUXTIME)]";
            qv_appendcomma($sets,"AUXTIME=$AUXTIME");
        }

        // DETERMINO IL FILE DA IMPORTARE
        if(isset($data["IMPORTNAME"])){
            $IMPORTNAME=ryqEscapize($data["IMPORTNAME"]);
            
            // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
            qv_environs($maestro, $dirtemp, $dirattach);
            
            // CONTROLLO CHE IL FILE DA IMPORTARE ESISTA
            if(!is_file($dirtemp.$IMPORTNAME)){
                $babelcode="QVERR_TEMPFILE";
                $b_params=array("IMPORTNAME" => $IMPORTNAME);
                $b_pattern="File [{1}] da importare inesistente";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets, "IMPORTNAME='$IMPORTNAME'");
            // DETERMINO L'ESTENSIONE
            $path_parts=pathinfo($dirtemp.$IMPORTNAME);
            if(isset($path_parts["extension"]))
                $EXTENSION=$path_parts["extension"];
            else
                $EXTENSION="";
            qv_appendcomma($sets, "EXTENSION='$EXTENSION'");
        }
        else{
            $IMPORTNAME="";
            $EXTENSION="";
        }

        // DETERMINO SUBPATH
        if(isset($data["SUBPATH"])){
            $SUBPATH=ryqEscapize($data["SUBPATH"]);
            if($SUBPATH!=""){
                // INVERTO LE BARRE
                $SUBPATH=strtr($SUBPATH, array("\\" => "/" ) );
                // NORMALIZZO CON LA BARRA FINALE
                if( substr($SUBPATH, -1)!="/" )
                    $SUBPATH.="/";
            }
            qv_appendcomma($sets,"SUBPATH='$SUBPATH'");
        }
        else{
            // REPERISCO IL VECCHIO SUBPATH
            qv_solvesubpath($maestro, $SYSID, $SUBPATH);
        }
        
        // DETERMINO TAG
        if(isset($data["TAG"])){
            $TAG=ryqEscapize($data["TAG"], 200);
            qv_appendcomma($sets,"TAG='$TAG'");
        }
        
        // DETERMINO SCOPE
        if(isset($data["SCOPE"])){
            $SCOPE=intval($data["SCOPE"]);
            if($SCOPE<0 || $SCOPE>2 )
                $SCOPE=0;
            qv_appendcomma($sets,"SCOPE=$SCOPE");
        }

        $USERUPDATEID=$global_quiveruserid;
        qv_appendcomma($sets,"USERUPDATEID='$USERUPDATEID'");
        
        $TIMEUPDATE="[:NOW()]";
        qv_appendcomma($sets,"TIMEUPDATE=$TIMEUPDATE");
        
        if($sets!=""){
            $sql="UPDATE QVFILES SET $sets WHERE SYSID='$SYSID'";
            if(!maestro_execute($maestro, $sql, false, $clobs)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"] );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            // COPIO IL FILE
            if($IMPORTNAME!=""){
                // CREO EVENTUALMENTE IL SOTTOPERCORSO
                if($SUBPATH!="")
                    qv_makepath($dirattach, $SUBPATH);
                if(!@copy("$dirtemp$IMPORTNAME", "$dirattach$SUBPATH$SYSID.$EXTENSION")){
                    $babelcode="QVERR_IMPORTFAILED";
                    $b_params=array("NAME" => $NAME);
                    $b_pattern="Import del file [{1}] fallito";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
                @unlink($dirtemp.$IMPORTNAME);
            }
        }
        
        // EVENTUALE MODIFICA TABELLA CROSS
        if(isset($data["CROSSID"]) && isset($data["SORTER"])){
            $CROSSID=ryqEscapize($data["CROSSID"]);
            $SORTER=intval($data["SORTER"]);
            $sql="UPDATE QVTABLEFILE SET SORTER=$SORTER WHERE SYSID='$CROSSID'";
            if(!maestro_execute($maestro, $sql, false)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"] );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
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