<?php 
/****************************************************************************
* Name:            qv_files_delete.php                                      *
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
include_once "quiverdel.php";
function qv_files_delete($maestro, $data){
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
        qv_solverecord($maestro, $data, "QVFILES", "SYSID", "NAME", $SYSID);
        if($SYSID!=""){
            // LO CANCELLO SOLTANTO SE NON E' ALLEGATO
            maestro_query($maestro,"SELECT {AS:TOP 1} SYSID FROM QVTABLEFILE WHERE FILEID='$SYSID' {LM:LIMIT 1}{O: AND ROWNUM=1}{D:FETCH FIRST 1 ROWS ONLY}", $e);
            $ok=(count($e)==0);
            unset($e);

            if($ok){
                // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
                qv_environs($maestro, $dirtemp, $dirattach);
                
                if(qv_setting($maestro, "_VIRTUALDELETE", true)){
                    $NAME="__$SYSID";
                    $DELETED=1;
                    $USERDELETEID=$global_quiveruserid;
                    $TIMEDELETE="[:NOW()]";
                    $sql="UPDATE QVFILES SET NAME='$NAME',DELETED=$DELETED,USERDELETEID='$USERDELETEID',TIMEDELETE=$TIMEDELETE WHERE SYSID='$SYSID'";
                    $IMPORTNAME="";
                }
                else{
                    $sql="DELETE FROM QVFILES WHERE SYSID='$SYSID'";
                    maestro_query($maestro,"SELECT SUBPATH,IMPORTNAME FROM QVFILES WHERE SYSID='$SYSID'",$r);
                    if(count($r)==1){
                        $SUBPATH=$r[0]["SUBPATH"];
                        $IMPORTNAME=$r[0]["IMPORTNAME"];
                    }
                    else{
                        $SUBPATH="";
                        $IMPORTNAME="";
                    }
                }
                
                if(!maestro_execute($maestro, $sql, false)){
                    $babelcode="QVERR_EXECUTE";
                    $trace=debug_backtrace();
                    $b_params=array("FUNCTION" => $trace[0]["function"] );
                    $b_pattern=$maestro->errdescr;
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
                if($IMPORTNAME!=""){
                    // RINOMINO IL FILE ALLEGATO PER MARCARLO COME ORFANO
                    $path_parts=pathinfo($dirtemp.$IMPORTNAME);
                    if(isset($path_parts["extension"]))
                        $ext="." . $path_parts["extension"];
                    else
                        $ext="";
                    $from=$dirattach.$SUBPATH.$SYSID.$ext;
                    $to=$dirattach.$SUBPATH."_".$SYSID.$ext;
                    @rename($from, $to);
                }
            }
        }
        else{
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
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