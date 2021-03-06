<?php 
/****************************************************************************
* Name:            qv_objecttypes_insert.php                                *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quivervws.php";
function qv_objecttypes_insert($maestro, $data){
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
        
        // DETERMINO UN NUOVO SYSID
        $SYSID=qv_createsysid($maestro);
        
        // DETERMINO NAME
        if(isset($data["NAME"])){
            $NAME=ryqEscapize($data["NAME"], 50);
            qv_checkname($maestro, "QVOBJECTTYPES", $SYSID, $NAME);
        }
        else{
            $NAME="__$SYSID";
        }
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize(qv_inputUTF8($data["DESCRIPTION"]), 200);
            if($DESCRIPTION=="")
                $DESCRIPTION=$NAME;
        }
        else{
            $DESCRIPTION=$NAME;
        }

        // DETERMINO GENRETYPEID
        qv_solverecord($maestro, $data, "QVGENRETYPES", "GENRETYPEID", "GENRETYPENAME", $GENRETYPEID);
        
        // DETERMINO QUIVERTYPEID
        qv_solverecord($maestro, $data, "QVQUIVERTYPES", "QUIVERTYPEID", "QUIVERTYPENAME", $QUIVERTYPEID);
        
        // DETERMINO TIMEUNIT
        if(isset($data["TIMEUNIT"])){
            $TIMEUNIT=strtoupper(ryqEscapize($data["TIMEUNIT"], 1));
            if($TIMEUNIT!="D" && $TIMEUNIT!="S")
                $TIMEUNIT="D";
        }
        else{
            $TIMEUNIT="D";
        }

        // DETERMINO VIEWNAME
        if(isset($data["VIEWNAME"])){
            $VIEWNAME=ryqEscapize($data["VIEWNAME"], 50);
        }
        else{
            $VIEWNAME="";
        }
        
        // DETERMINO TABLENAME
        if(isset($data["TABLENAME"])){
            $TABLENAME=ryqEscapize($data["TABLENAME"], 50);
        }
        else{
            $TABLENAME="";
        }
        
        // DETERMINO DELETABLE
        if(isset($data["DELETABLE"])){
            if(intval($data["DELETABLE"])!=0)
                $DELETABLE=1;
            else
                $DELETABLE=0;
        }
        else{
            $DELETABLE=0;
        }
        
        // DETERMINO SIMPLE
        if(isset($data["SIMPLE"])){
            if(intval($data["SIMPLE"])!=0)
                $SIMPLE=1;
            else
                $SIMPLE=0;
        }
        else{
            $SIMPLE=0;
        }
        
        // DETERMINO VIRTUALDELETE
        if(isset($data["VIRTUALDELETE"])){
            if(intval($data["VIRTUALDELETE"])!=0)
                $VIRTUALDELETE=1;
            else
                $VIRTUALDELETE=0;
        }
        else{
            $VIRTUALDELETE=0;
        }

        // DETERMINO HISTORICIZING
        if(isset($data["HISTORICIZING"])){
            if(intval($data["HISTORICIZING"])!=0)
                $HISTORICIZING=1;
            else
                $HISTORICIZING=0;
        }
        else{
            $HISTORICIZING=0;
        }

        // DETERMINO TAG
        if(isset($data["TAG"]))
            $TAG=ryqEscapize($data["TAG"], 200);
        else
            $TAG="";

        // PREDISPONGO COLONNE E VALORI DA REGISTRARE
        $columns="SYSID,NAME,DESCRIPTION,GENRETYPEID,QUIVERTYPEID,TIMEUNIT,VIEWNAME,TABLENAME,DELETABLE,SIMPLE,VIRTUALDELETE,HISTORICIZING,TAG";
        $values="'$SYSID','$NAME','$DESCRIPTION','$GENRETYPEID','$QUIVERTYPEID','$TIMEUNIT','$VIEWNAME','$TABLENAME',$DELETABLE,$SIMPLE,$VIRTUALDELETE,$HISTORICIZING,'$TAG'";
        $sql="INSERT INTO QVOBJECTTYPES($columns) VALUES($values)";
        
        if(!maestro_execute($maestro, $sql, false)){
            $babelcode="QVERR_EXECUTE";
            $trace=debug_backtrace();
            $b_params=array("FUNCTION" => $trace[0]["function"] );
            $b_pattern=$maestro->errdescr;
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
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