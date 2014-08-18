<?php 
/****************************************************************************
* Name:            qv_files_insert.php                                      *
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
function qv_files_insert($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // RISOLVO LE INFO DI SESSIONE
        qv_infosession($maestro);
        
        // DETERMINO UN NUOVO SYSID
        $SYSID=qv_createsysid($maestro);
        
        // DETERMINO NAME
        if(isset($data["NAME"])){
            $NAME=ryqEscapize($data["NAME"], 50);
            qv_checkname($maestro, "QVFILES", $SYSID, $NAME);
        }
        else{
            $NAME="__$SYSID";
        }
        
        // DETERMINO IL FILE DA IMPORTARE
        if(isset($data["IMPORTNAME"])){
            $IMPORTNAMEORIG=$data["IMPORTNAME"];
            $IMPORTNAME=ryqEscapize($IMPORTNAMEORIG);
            
            // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
            qv_environs($maestro, $dirtemp, $dirattach);
            
            // CONTROLLO CHE IL FILE DA IMPORTARE ESISTA
            if(!is_file($dirtemp.$IMPORTNAMEORIG)){
                $babelcode="QVERR_TEMPFILE";
                $b_params=array("IMPORTNAME" => $IMPORTNAMEORIG);
                $b_pattern="File [{1}] da importare inesistente";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $IMPORTNAMEORIG="";
            $IMPORTNAME="";
        }
        
        // DETERMINO L'ESTENSIONE
        if($IMPORTNAMEORIG!=""){
            $path_parts=pathinfo($dirtemp.$IMPORTNAMEORIG);
            if(isset($path_parts["extension"]))
                $EXTENSION=$path_parts["extension"];
            else
                $EXTENSION="";
        }
        else{
            $EXTENSION="";
        }
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize(qv_inputUTF8($data["DESCRIPTION"]), 100);
            if($DESCRIPTION=="")
                $DESCRIPTION=$NAME;
        }
        else{
            if($IMPORTNAME!="")
                $DESCRIPTION=$IMPORTNAME;
            else
                $DESCRIPTION=$NAME;
        }
            
        // DETERMINO REGISTRY
        $clobs=false;
        if(isset($data["REGISTRY"]))
            qv_setclob($maestro, "REGISTRY", $data["REGISTRY"], $REGISTRY, $clobs);
        else
            $REGISTRY="''";
            
        // DETERMINO AUXTIME
        if(isset($data["AUXTIME"]))
            $AUXTIME=qv_escapizetime($data["AUXTIME"], LOWEST_TIME);
        else
            $AUXTIME=date("YmdHis");
            
        // COMPLETO PER SQL
        $AUXTIME="[:DATE($AUXTIME)]";

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
        }
        else{
            $SUBPATH="";
        }
        
        // DETERMINO TAG
        if(isset($data["TAG"]))
            $TAG=ryqEscapize($data["TAG"], 200);
        else
            $TAG="";
        
        // DETERMINO SCOPE
        if(isset($data["SCOPE"])){
            $SCOPE=intval($data["SCOPE"]);
            if($SCOPE<0 || $SCOPE>2 )
                $SCOPE=0;
        }
        else{
            $SCOPE=0;
        }
        
        $DELETED=0;
        $ROLEID=$global_quiverroleid;
        $USERINSERTID=$global_quiveruserid;
        $USERUPDATEID="";
        $USERDELETEID="";
        $TIMEINSERT="[:NOW()]";
        $TIMEUPDATE="[:DATE(" . LOWEST_DATE . ")]";
        $TIMEDELETE="[:DATE(" . LOWEST_DATE . ")]";
        
        // PREDISPONGO COLONNE E VALORI DA REGISTRARE
        $columns="SYSID,NAME,DESCRIPTION,REGISTRY,AUXTIME,SUBPATH,IMPORTNAME,EXTENSION,TAG,SCOPE,DELETED,ROLEID,USERINSERTID,USERUPDATEID,USERDELETEID,TIMEINSERT,TIMEUPDATE,TIMEDELETE";
        $values="'$SYSID','$NAME','$DESCRIPTION',$REGISTRY,$AUXTIME,'$SUBPATH','$IMPORTNAME','$EXTENSION','$TAG',$SCOPE,$DELETED,'$ROLEID','$USERINSERTID','$USERUPDATEID','$USERDELETEID',$TIMEINSERT,$TIMEUPDATE,$TIMEDELETE";
        $sql="INSERT INTO QVFILES($columns) VALUES($values)";
        
        if(!maestro_execute($maestro, $sql, false, $clobs)){
            $babelcode="QVERR_EXECUTE";
            $trace=debug_backtrace();
            $b_params=array("FUNCTION" => $trace[0]["function"] );
            $b_pattern=$maestro->errdescr;
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        // COPIO IL FILE
        if($IMPORTNAMEORIG!=""){
            // CREO EVENTUALMENTE IL SOTTOPERCORSO
            if($SUBPATH!="")
                qv_makepath($dirattach, $SUBPATH);
            if(!@copy("$dirtemp$IMPORTNAMEORIG", "$dirattach$SUBPATH$SYSID.$EXTENSION")){
                $babelcode="QVERR_IMPORTFAILED";
                $b_params=array("NAME" => $NAME);
                $b_pattern="Import del file [{1}] fallito";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            @unlink($dirtemp.$IMPORTNAMEORIG);
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