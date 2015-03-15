<?php 
/****************************************************************************
* Name:            qv_motivetypes_update.php                                 *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quivervws.php";
function qv_motivetypes_update($maestro, $data){
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
        $record=qv_solverecord($maestro, $data, "QVMOTIVETYPES", "SYSID", "NAME", $SYSID, "OBJECTTYPEID");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $REG_OBJECTTYPEID=$record["OBJECTTYPEID"];
        
        // SE NAME E' IN MODIFICA LO VALIDO
        if(isset($data["SYSID"]) && isset($data["NAME"])){
            $NAME=$data["NAME"];
            qv_checkname($maestro, "QVMOTIVETYPES", $SYSID, $NAME);
            qv_appendcomma($sets, "NAME='$NAME'");
        }
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize(qv_inputUTF8($data["DESCRIPTION"]), 200);
            if($DESCRIPTION=="")
                $DESCRIPTION=$NAME;
            qv_appendcomma($sets,"DESCRIPTION='$DESCRIPTION'");
        }

        // DETERMINO OBJECTTYPEID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTTYPES", "OBJECTTYPEID", "OBJECTTYPENAME", $OBJECTTYPEID);
        if($OBJECTTYPEID!=""){
            qv_appendcomma($sets,"OBJECTTYPEID='$OBJECTTYPEID'");
        }
        else{
            if($fields){
                qv_appendcomma($sets,"OBJECTTYPEID='$OBJECTTYPEID'");
            }
        }
        if($fields!==false){
            if($OBJECTTYPEID!=$REG_OBJECTTYPEID && $REG_OBJECTTYPEID!=""){
                // POSSO CAMBIARE TIPOLOGIA SOLTANTO SE NON E' IN USO
                qv_modifiabletype($maestro, "QVMOTIVES", $SYSID, "REFERENCEID");
                qv_modifiabletype($maestro, "QVMOTIVES", $SYSID, "COUNTERPARTID");
            }
        }

        // DETERMINO VIEWNAME
        if(isset($data["VIEWNAME"])){
            $VIEWNAME=ryqEscapize($data["VIEWNAME"], 50);
            qv_appendcomma($sets,"VIEWNAME='$VIEWNAME'");
        }
        
        // DETERMINO TABLENAME
        if(isset($data["TABLENAME"])){
            $TABLENAME=ryqEscapize($data["TABLENAME"], 50);
            qv_appendcomma($sets,"TABLENAME='$TABLENAME'");
        }
        
        // DETERMINO DELETABLE
        if(isset($data["DELETABLE"])){
            if(intval($data["DELETABLE"])!=0)
                $DELETABLE=1;
            else
                $DELETABLE=0;
            qv_appendcomma($sets,"DELETABLE=$DELETABLE");
        }
        
        // DETERMINO SIMPLE
        if(isset($data["SIMPLE"])){
            if(intval($data["SIMPLE"])!=0)
                $SIMPLE=1;
            else
                $SIMPLE=0;
            qv_appendcomma($sets,"SIMPLE=$SIMPLE");
        }
        
        // DETERMINO VIRTUALDELETE
        if(isset($data["VIRTUALDELETE"])){
            if(intval($data["VIRTUALDELETE"])!=0)
                $VIRTUALDELETE=1;
            else
                $VIRTUALDELETE=0;
            qv_appendcomma($sets,"VIRTUALDELETE=$VIRTUALDELETE");
        }

        // DETERMINO HISTORICIZING
        if(isset($data["HISTORICIZING"])){
            if(intval($data["HISTORICIZING"])!=0)
                $HISTORICIZING=1;
            else
                $HISTORICIZING=0;
            qv_appendcomma($sets,"HISTORICIZING=$HISTORICIZING");
        }

        // DETERMINO TAG
        if(isset($data["TAG"])){
            $TAG=ryqEscapize($data["TAG"], 200);
            qv_appendcomma($sets,"TAG='$TAG'");
        }
            
        // DROPPO LA VECCHIA VIEW
        qv_deleteview($maestro, "QVMOTIVE", $SYSID);
        
        if($sets!=""){
            $sql="UPDATE QVMOTIVETYPES SET $sets WHERE SYSID='$SYSID'";
            if(!maestro_execute($maestro, $sql, false)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"] );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        // RICREO LA VIEW
        qv_refreshview($maestro, "QVMOTIVE", "", $SYSID);
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