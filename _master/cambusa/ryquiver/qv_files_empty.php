<?php 
/****************************************************************************
* Name:            qv_files_empty.php                                       *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverdel.php";
include_once "quiverfil.php";
include_once $path_cambusa."rymaestro/maestro_querylib.php";
function qv_files_empty($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // INDIVIDUAZIONE RECORD
        qv_solverecord($maestro, $data, "QVFILES", "SYSID", "NAME", $SYSID);
        
        // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
        $infoenv=qv_environs($maestro);
        $dirtemp=$infoenv["dirtemp"];
        $dirattach=$infoenv["dirattach"];
            
        $arrdel=array();
        
        if($SYSID!=""){
            // CANCELLAZIONE SINGOLA PER SYSID O PER NAME
            $arrdel[]=$SYSID;
        }
        else{
            // CANCELLAZIONE MASSIVA PER DATA CANCELLAZIONE
            if(isset($data["DATE"]))
                $date=ryqEscapize($data["DATE"]);
            else
                $date=HIGHEST_DATE;
            $cnt=0;
            $res=maestro_unbuffered($maestro, "SELECT SYSID FROM QVFILES WHERE DELETED=1 AND TIMEDELETE<[:DATE($date)]");
            while( $row=maestro_fetch($maestro, $res) ){
                $arrdel[]=$row["SYSID"];
                $cnt+=1;
                if($cnt>10000){
                    break;
                }
            }
            maestro_free($maestro, $res);
        }
        for($i=0;$i<count($arrdel);$i++){
            $SYSID=$arrdel[$i];
            
            // RINOMINO IL FILE ALLEGATO PER MARCARLO COME ORFANO
            maestro_query($maestro,"SELECT SUBPATH,IMPORTNAME FROM QVFILES WHERE SYSID='$SYSID'",$r);
            if(count($r)==1){
                $SUBPATH=$r[0]["SUBPATH"];
                $IMPORTNAME=$r[0]["IMPORTNAME"];
                $path_parts=pathinfo($dirtemp.$IMPORTNAME);
                if(isset($path_parts["extension"]))
                    $ext="." . $path_parts["extension"];
                else
                    $ext="";
                $from=$dirattach.$SUBPATH.$SYSID.$ext;
                $to=$dirattach.$SUBPATH."_".$SYSID.$ext;
                @rename($from, $to);
            }
            unset($r);
            
            // PROVVEDO CON LA CANCELLAZIONE DEFINITIVA
            $sql="DELETE FROM QVFILES WHERE SYSID='$SYSID'";
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