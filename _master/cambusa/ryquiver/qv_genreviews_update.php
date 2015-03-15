<?php 
/****************************************************************************
* Name:            qv_genreviews_update.php                                 *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quivervws.php";
function qv_genreviews_update($maestro, $data){
    global $babelcode, $babelparams;
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
        
        // INDIVIDUAZIONE RECORD
        $sets="";
        $record=qv_solverecord($maestro, $data, "QVGENREVIEWS", "SYSID", "", $SYSID, "TYPOLOGYID");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $TYPOLOGYID=$record["TYPOLOGYID"];
        
        // DETERMINO FIELDNAME
        if(isset($data["FIELDNAME"])){
            $FIELDNAME=ryqEscapize($data["FIELDNAME"], 50);
            qv_checkfieldname($maestro, "QVGENREVIEWS", $SYSID, $TYPOLOGYID, $FIELDNAME);
            qv_appendcomma($sets, "FIELDNAME='$FIELDNAME'");
        }
            
        // DETERMINO FIELDTYPE
        if(isset($data["FIELDTYPE"])){
            $FIELDTYPE=ryqEscapize($data["FIELDTYPE"], 50);
            qv_appendcomma($sets,"FIELDTYPE='$FIELDTYPE'");
        }
        
        // DETERMINO FORMULA
        if(isset($data["FORMULA"])){
            $FORMULA=ryqEscapize($data["FORMULA"], 200);
            qv_appendcomma($sets,"FORMULA='$FORMULA'");
        }
        
        // DETERMINO CAPTION
        if(isset($data["CAPTION"])){
            $CAPTION=ryqEscapize($data["CAPTION"], 50);
            qv_appendcomma($sets,"CAPTION='$CAPTION'");
        }
        
        // DETERMINO WRITABLE
        if(isset($data["WRITABLE"])){
            if(intval($data["WRITABLE"])!=0)
                $WRITABLE=1;
            else
                $WRITABLE=0;
            qv_appendcomma($sets,"WRITABLE=$WRITABLE");
        }
        
        // DROPPO LA VECCHIA VIEW
        qv_deleteview($maestro, "QVGENRE", $TYPOLOGYID);
    
        if($sets!=""){
            $sql="UPDATE QVGENREVIEWS SET $sets WHERE SYSID='$SYSID'";
            if(!maestro_execute($maestro, $sql, false)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"] );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        // RICREO LA VIEW
        qv_refreshview($maestro, "QVGENRE", $SYSID, $TYPOLOGYID);
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