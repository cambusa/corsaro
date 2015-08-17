<?php 
/****************************************************************************
* Name:            qv_legend_infoconfig.php                                 *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_legend_infoconfig($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // LEGGO IL LEGEND
        $legend=qv_solverecord($maestro, $data, "QW_LEGEND", "LEGENDID", "", $LEGENDID, "*");
        if($LEGENDID==""){
            $babelcode="QVERR_LEGENDID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la configurazione";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $PROCESSOID=$legend["PROCESSOID"];
        $CONTOID=$legend["CONTOID"];
        $DESCRIPTION=$legend["DESCRIPTION"];
        
        if($PROCESSOID==""){
            $babelcode="QVERR_NOPROCESSO";
            $b_params=array();
            $b_pattern="Processo non specificato nella configurazione";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // LEGGO IL PRIMO STATO DEL PROCESSO
        maestro_query($maestro, "SELECT SYSID,ATTOREID,CONTOID FROM QW_PROCSTATI WHERE PROCESSOID='$PROCESSOID' AND INIZIALE=1 ORDER BY ORDINATORE", $r);
        if(count($r)>0){
            $STATOID=$r[0]["SYSID"];
            $ATTOREID=$r[0]["ATTOREID"];
            if($CONTOID==""){
                // SE LA CONFIGURAZIONE NON HA CONTO USO QUELLO DEL PROCESSO
                $CONTOID=$r[0]["CONTOID"];
            }
            if($CONTOID==""){
                $babelcode="QVERR_NOCONTO";
                $b_params=array();
                $b_pattern="Conto non specificato in configurazione";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $babelcode="QVERR_NOSTATO";
            $b_params=array();
            $b_pattern="Non trovato uno stato valido per il processo";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // LEGGO IL GENERE DEL CONTO
        maestro_query($maestro, "SELECT REFGENREID FROM QW_CONTI WHERE SYSID='$CONTOID'", $r);
        if(count($r)>0){
            $GENREID=$r[0]["REFGENREID"];
        }
        else{
            $babelcode="QVERR_NOREFGENREID";
            $b_params=array();
            $b_pattern="Genere non specificato nel conto del processo";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // SCANSIONE DELLE QUERY
        $QUERIES=array();
        maestro_query($maestro, "SELECT SYSID,DESCRIPTION,LEGENDID,ARROWTYPEID,LEGENDVIEW,LEGENDSELECT,LEGENDROWS,LEGENDCOLUMNS,LEGENDPARAMS,SIGNUM,BAGNAME FROM QW_LEGENDQUERY WHERE LEGENDID='$LEGENDID' ORDER BY SORTER", $r);
        for($i=0; $i<count($r); $i++){
            $QUERYID=$r[$i]["SYSID"];
            $ARROWTYPEID=$r[$i]["ARROWTYPEID"];
            $LEGENDVIEW=$r[$i]["LEGENDVIEW"];

            $QUERIES[$QUERYID]=array();
            $QUERIES[$QUERYID]["SYSID"]=$QUERYID;
            $QUERIES[$QUERYID]["DESCRIPTION"]=$r[$i]["DESCRIPTION"];
            $QUERIES[$QUERYID]["LEGENDID"]=$r[$i]["LEGENDID"];
            $QUERIES[$QUERYID]["ARROWTYPEID"]=$ARROWTYPEID;
            $QUERIES[$QUERYID]["LEGENDVIEW"]=$LEGENDVIEW;
            $QUERIES[$QUERYID]["LEGENDSELECT"]=$r[$i]["LEGENDSELECT"];
            $QUERIES[$QUERYID]["LEGENDROWS"]=$r[$i]["LEGENDROWS"];
            $QUERIES[$QUERYID]["LEGENDCOLUMNS"]=$r[$i]["LEGENDCOLUMNS"];
            $QUERIES[$QUERYID]["LEGENDPARAMS"]=$r[$i]["LEGENDPARAMS"];
            $QUERIES[$QUERYID]["SIGNUM"]=intval($r[$i]["SIGNUM"]);
            $QUERIES[$QUERYID]["BAGNAME"]=$r[$i]["BAGNAME"];
            
            maestro_query($maestro, "SELECT * FROM QVARROWTYPES WHERE SYSID='$ARROWTYPEID'", $v);
            if(count($v)==1){
                $QUERIES[$QUERYID]["VIEWNAME"]=$v[0]["VIEWNAME"];
                $QUERIES[$QUERYID]["TABLENAME"]=$v[0]["TABLENAME"];
            }
            if($LEGENDVIEW!=""){
                $QUERIES[$QUERYID]["VIEWNAME"]=$LEGENDVIEW;
            }
        }
        
        // VARIABILI DI RITORNO
        $babelparams["PROCESSOID"]=$PROCESSOID;
        $babelparams["DESCRIPTION"]=$DESCRIPTION;
        $babelparams["STATOID"]=$STATOID;
        $babelparams["ATTOREID"]=$ATTOREID;
        $babelparams["CONTOID"]=$CONTOID;
        $babelparams["GENREID"]=$GENREID;
        $babelparams["QUERIES"]=$QUERIES;
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