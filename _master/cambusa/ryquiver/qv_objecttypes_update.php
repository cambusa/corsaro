<?php 
/****************************************************************************
* Name:            qv_objecttypes_update.php                                 *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quivervws.php";
function qv_objecttypes_update($maestro, $data){
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
        $record=qv_solverecord($maestro, $data, "QVOBJECTTYPES", "SYSID", "NAME", $SYSID, "GENRETYPEID,QUIVERTYPEID");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $REG_GENRETYPEID=$record["GENRETYPEID"];
        $REG_QUIVERTYPEID=$record["QUIVERTYPEID"];
        
        // SE NAME E' IN MODIFICA LO VALIDO
        if(isset($data["SYSID"]) && isset($data["NAME"])){
            $NAME=$data["NAME"];
            qv_checkname($maestro, "QVOBJECTTYPES", $SYSID, $NAME);
            qv_appendcomma($sets, "NAME='$NAME'");
        }
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize(qv_inputUTF8($data["DESCRIPTION"]), 100);
            if($DESCRIPTION=="")
                $DESCRIPTION=$NAME;
            qv_appendcomma($sets,"DESCRIPTION='$DESCRIPTION'");
        }

        // DETERMINO GENRETYPEID
        $fields=qv_solverecord($maestro, $data, "QVGENRETYPES", "GENRETYPEID", "GENRETYPENAME", $GENRETYPEID);
        if($GENRETYPEID!=""){
            qv_appendcomma($sets,"GENRETYPEID='$GENRETYPEID'");
        }
        else{
            if($fields){
                qv_appendcomma($sets,"GENRETYPEID='$GENRETYPEID'");
            }
        }
        if($fields!==false){
            if($GENRETYPEID!=$REG_GENRETYPEID && $REG_GENRETYPEID!=""){
                // POSSO CAMBIARE TIPOLOGIA SOLTANTO SE NON E' IN USO
                qv_modifiabletype($maestro, "QVOBJECTS", $SYSID, "REFGENREID");
            }
        }
        
        // DETERMINO QUIVERTYPEID
        $fields=qv_solverecord($maestro, $data, "QVQUIVERTYPES", "QUIVERTYPEID", "QUIVERTYPENAME", $QUIVERTYPEID);
        if($QUIVERTYPEID!=""){
            qv_appendcomma($sets,"QUIVERTYPEID='$QUIVERTYPEID'");
        }
        else{
            if($fields){
                qv_appendcomma($sets,"QUIVERTYPEID='$QUIVERTYPEID'");
            }
        }
        if($fields!==false){
            if($QUIVERTYPEID!=$REG_QUIVERTYPEID && $REG_QUIVERTYPEID!=""){
                // POSSO CAMBIARE TIPOLOGIA SOLTANTO SE NON E' IN USO
                qv_modifiabletype($maestro, "QVOBJECTS", $SYSID, "REFQUIVERID");
            }
        }
        
        // DETERMINO TIMEUNIT
        if(isset($data["TIMEUNIT"])){
            $TIMEUNIT=strtoupper(ryqEscapize($data["TIMEUNIT"], 1));
            if($TIMEUNIT=="D" || $TIMEUNIT=="S")
                qv_appendcomma($sets,"TIMEUNIT='$TIMEUNIT'");
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
        qv_deleteview($maestro, "QVOBJECT", $SYSID);

        if($sets!=""){
            $sql="UPDATE QVOBJECTTYPES SET $sets WHERE SYSID='$SYSID'";
            if(!maestro_execute($maestro, $sql, false)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"] );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        // RICREO LA VIEW
        qv_refreshview($maestro, "QVOBJECT", "", $SYSID);
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