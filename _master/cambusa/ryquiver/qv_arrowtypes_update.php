<?php 
/****************************************************************************
* Name:            qv_arrowtypes_update.php                                 *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quivervws.php";
function qv_arrowtypes_update($maestro, $data){
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
        $record=qv_solverecord($maestro, $data, "QVARROWTYPES", "SYSID", "NAME", $SYSID, "GENRETYPEID,MOTIVETYPEID,BOWTYPEID,TARGETTYPEID");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $REG_GENRETYPEID=$record["GENRETYPEID"];
        $REG_MOTIVETYPEID=$record["MOTIVETYPEID"];
        $REG_BOWTYPEID=$record["BOWTYPEID"];
        $REG_TARGETTYPEID=$record["TARGETTYPEID"];
        
        // SE NAME E' IN MODIFICA LO VALIDO
        if(isset($data["SYSID"]) && isset($data["NAME"])){
            $NAME=$data["NAME"];
            qv_checkname($maestro, "QVARROWTYPES", $SYSID, $NAME);
            qv_appendcomma($sets, "NAME='$NAME'");
        }
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize(qv_inputUTF8($data["DESCRIPTION"]), 200);
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
                $babelcode="QVERR_GENRETYPE";
                $b_params=array();
                $b_pattern="Tipo di genere non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        if($fields!==false){
            if($GENRETYPEID!=$REG_GENRETYPEID && $REG_GENRETYPEID!=""){
                // POSSO CAMBIARE TIPOLOGIA SOLTANTO SE NON E' IN USO
                qv_modifiabletype($maestro, "QVARROWS", $SYSID, "GENREID");
            }
        }
        
        // DETERMINO MOTIVETYPEID
        $fields=qv_solverecord($maestro, $data, "QVMOTIVETYPES", "MOTIVETYPEID", "MOTIVETYPENAME", $MOTIVETYPEID);
        if($MOTIVETYPEID!=""){
            qv_appendcomma($sets,"MOTIVETYPEID='$MOTIVETYPEID'");
        }
        else{
            if($fields){
                $babelcode="QVERR_MOTIVETYPE";
                $b_params=array();
                $b_pattern="Tipo di motivo non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        if($fields!==false){
            if($MOTIVETYPEID!=$REG_MOTIVETYPEID && $REG_MOTIVETYPEID!=""){
                // POSSO CAMBIARE TIPOLOGIA SOLTANTO SE NON E' IN USO
                qv_modifiabletype($maestro, "QVARROWS", $SYSID, "MOTIVEID");
            }
        }
        
        // DETERMINO BOWTYPEID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTTYPES", "BOWTYPEID", "BOWTYPENAME", $BOWTYPEID);
        if($BOWTYPEID!=""){
            qv_appendcomma($sets,"BOWTYPEID='$BOWTYPEID'");
        }
        else{
            if($fields){
                $babelcode="QVERR_BOWTYPE";
                $b_params=array();
                $b_pattern="Tipo di oggetto di partenza non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        if($fields!==false){
            if($BOWTYPEID!=$REG_BOWTYPEID && $REG_BOWTYPEID!=""){
                // POSSO CAMBIARE TIPOLOGIA SOLTANTO SE NON E' IN USO
                qv_modifiabletype($maestro, "QVARROWS", $SYSID, "BOWID");
            }
        }
        
        // DETERMINO TARGETTYPEID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTTYPES", "TARGETTYPEID", "TARGETTYPENAME", $TARGETTYPEID);
        if($TARGETTYPEID!=""){
            qv_appendcomma($sets,"TARGETTYPEID='$TARGETTYPEID'");
        }
        else{
            if($fields){
                $babelcode="QVERR_TARGETTYPE";
                $b_params=array();
                $b_pattern="Tipo di oggetto di arrivo non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        if($fields!==false){
            if($TARGETTYPEID!=$REG_TARGETTYPEID && $REG_TARGETTYPEID!=""){
                // POSSO CAMBIARE TIPOLOGIA SOLTANTO SE NON E' IN USO
                qv_modifiabletype($maestro, "QVARROWS", $SYSID, "TARGETID");
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
        qv_deleteview($maestro, "QVARROW", $SYSID);
            
        if($sets!=""){
            $sql="UPDATE QVARROWTYPES SET $sets WHERE SYSID='$SYSID'";
            if(!maestro_execute($maestro, $sql, false)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"] );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        // RICREO LA VIEW
        qv_refreshview($maestro, "QVARROW", "", $SYSID);
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