<?php 
/****************************************************************************
* Name:            qv_processi_export.php                                   *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_processi_export($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    global $path_customize;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $export=array();
        
        // LEGGO IL PROCESSO
        $processo=qv_solverecord($maestro, $data, "QW_PROCESSI", "PROCESSOID", "", $PROCESSOID, "*");
        if($PROCESSOID==""){
            $babelcode="QVERR_PROCESSOID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il processo";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $EXPORTNAME=$processo["SYSID"];
        $EXPORTDESCR=$processo["DESCRIPTION"];
        
        $program=array();
        
        // ESPORTAZIONE QUIVER
        $statement=array();
        // Tabella
        $statement["table"]="QVQUIVERS";
        $statement["extension"]="QUIVERS_PROCESSI";
        // Dati
        $datax=array();
        $datax["SYSID"]="[:SYSID(".$processo["SYSID"].")]";
        if(substr($processo["NAME"], 0, 2)!="__"){
            $datax["NAME"]=$processo["NAME"];
        }
        $datax["DESCRIPTION"]=$processo["DESCRIPTION"];
        $datax["REGISTRY"]=$processo["REGISTRY"];
        $datax["TYPOLOGYID"]=$processo["TYPOLOGYID"];
        $datax["GANTT"]=$processo["GANTT"];
        $datax["INVIOEMAIL"]=$processo["INVIOEMAIL"];
        $datax["PROTSERIE"]=$processo["PROTSERIE"];
        $datax["SETINTERPROCESSO"]="[:SYSID(".$processo["SETINTERPROCESSO"].")]";
        $datax["DATIAGGIUNTIVI"]=$processo["DATIAGGIUNTIVI"];
        unset($processo);
        // Aggancio all'istruzione
        $statement["data"]=$datax;
        $program[]=$statement;
        unset($datax);
        unset($statement);
        
        // ESPORTAZIONE MOTIVI
        maestro_query($maestro, "SELECT * FROM QW_MOTIVIATTIVITA WHERE PROCESSOID='$PROCESSOID'", $r);
        for($i=0; $i<count($r); $i++){
            $motivo=$r[$i];

            $statement=array();
            // Tabella
            $statement["table"]="QVMOTIVES";
            $statement["extension"]="MOTIVES_ATTIVITA";
            // Dati
            $datax=array();
            $datax["SYSID"]="[:SYSID(".$motivo["SYSID"].")]";
            if(substr($motivo["NAME"], 0, 2)!="__"){
                $datax["NAME"]=$motivo["NAME"];
            }
            $datax["DESCRIPTION"]=$motivo["DESCRIPTION"];
            $datax["REGISTRY"]=$motivo["REGISTRY"];
            $datax["TYPOLOGYID"]=$motivo["TYPOLOGYID"];
            $datax["DIRECTION"]=$motivo["DIRECTION"];
            $datax["CONSISTENCY"]=$motivo["CONSISTENCY"];
            $datax["SCOPE"]=$motivo["SCOPE"];
            $datax["UPDATING"]=$motivo["UPDATING"];
            $datax["DELETING"]=$motivo["DELETING"];
            $datax["STATUS"]=$motivo["STATUS"];
            $datax["GANTT"]=$motivo["GANTT"];
            $datax["INVIOEMAIL"]=$motivo["INVIOEMAIL"];
            $datax["SETCONOSCENZA"]="[:SYSID(".$motivo["SETCONOSCENZA"].")]";
            $datax["PROCESSOID"]="[:SYSID(".$motivo["PROCESSOID"].")]";
            $datax["ORDINATORE"]=$motivo["ORDINATORE"];
            $datax["PROTSERIE"]=$motivo["PROTSERIE"];
            $datax["RIFERIMENTOINIZIO"]=$motivo["RIFERIMENTOINIZIO"];
            $datax["RIFERIMENTOFINE"]=$motivo["RIFERIMENTOFINE"];
            $datax["MESEINIZIO"]=$motivo["MESEINIZIO"];
            $datax["MESEFINE"]=$motivo["MESEFINE"];
            $datax["GIORNOINIZIO"]=$motivo["GIORNOINIZIO"];
            $datax["GIORNOFINE"]=$motivo["GIORNOFINE"];
            $datax["ORAINIZIO"]=$motivo["ORAINIZIO"];
            $datax["ORAFINE"]=$motivo["ORAFINE"];
            $datax["CALCOLO"]=$motivo["CALCOLO"];
            $datax["PREAVVISO"]=$motivo["PREAVVISO"];
            $datax["INTESTAZIONE"]=$motivo["INTESTAZIONE"];
            $datax["ISTANZE"]=$motivo["ISTANZE"];
            unset($motivo);
            // Aggancio all'istruzione
            $statement["data"]=$datax;
            $program[]=$statement;
            unset($datax);
            unset($statement);
        }                        

        // ESPORTAZIONE STATI
        maestro_query($maestro, "SELECT * FROM QW_PROCSTATI WHERE PROCESSOID='$PROCESSOID'", $r);
        for($i=0; $i<count($r); $i++){
            $stato=$r[$i];
            $STATOID=$stato["SYSID"];

            $statement=array();
            // Tabella
            $statement["table"]="QVOBJECTS";
            $statement["extension"]="OBJECTS_PROCSTATI";
            // Dati
            $datax=array();
            $datax["SYSID"]="[:SYSID(".$stato["SYSID"].")]";
            $datax["DESCRIPTION"]=$stato["DESCRIPTION"];
            $datax["REGISTRY"]=$stato["REGISTRY"];
            $datax["TYPOLOGYID"]=$stato["TYPOLOGYID"];
            $datax["PROCESSOID"]="[:SYSID(".$stato["PROCESSOID"].")]";
            $datax["GESTOREID"]=$stato["GESTOREID"];
            $datax["INIZIALE"]=$stato["INIZIALE"];
            $datax["FINALE"]=$stato["FINALE"];
            $datax["ORDINATORE"]=$stato["ORDINATORE"];
            unset($stato);
            // Aggancio all'istruzione
            $statement["data"]=$datax;
            $program[]=$statement;
            unset($datax);
            unset($statement);
            
            // ESPORTAZIONE VINCOLI
            maestro_query($maestro, "SELECT * FROM QVSELECTIONS WHERE PARENTID='$STATOID'", $s);
            for($j=0; $j<count($s); $j++){
                $vincolo=$s[$j];

                $statement=array();
                // Tabella
                $statement["table"]="QVSELECTIONS";
                $statement["extension"]="";
                // Dati
                $datax=array();
                $datax["SYSID"]="[:SYSID(".$vincolo["SYSID"].")]";
                $datax["PARENTTABLE"]=$vincolo["PARENTTABLE"];
                $datax["PARENTFIELD"]=$vincolo["PARENTFIELD"];
                $datax["PARENTID"]="[:SYSID(".$vincolo["PARENTID"].")]";
                $datax["SELECTEDTABLE"]=$vincolo["SELECTEDTABLE"];
                $datax["SELECTEDID"]="[:SYSID(".$vincolo["SELECTEDID"].")]";
                $datax["ENABLED"]=$vincolo["ENABLED"];
                $datax["FLAG1"]=$vincolo["FLAG1"];
                $datax["FLAG2"]=$vincolo["FLAG2"];
                $datax["FLAG3"]=$vincolo["FLAG3"];
                $datax["FLAG4"]=$vincolo["FLAG4"];
                $datax["SORTER"]=$vincolo["SORTER"];
                unset($vincolo);
                // Aggancio all'istruzione
                $statement["data"]=$datax;
                $program[]=$statement;
                unset($datax);
                unset($statement);
            }
        }                        
        
        // ESPORTAZIONE TRANSIZIONI
        maestro_query($maestro, "SELECT * FROM QW_TRANSIZIONI WHERE SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$PROCESSOID') AND TARGETID<>''", $r);
        for($i=0; $i<count($r); $i++){
            $trans=$r[$i];
            $TRANSID=$trans["SYSID"];

            $statement=array();
            // Tabella
            $statement["table"]="QVARROWS";
            $statement["extension"]="ARROWS_TRANSIZIONI";
            // Dati
            $datax=array();
            $datax["SYSID"]="[:SYSID(".$trans["SYSID"].")]";
            $datax["DESCRIPTION"]=$trans["DESCRIPTION"];
            $datax["TYPOLOGYID"]=$trans["TYPOLOGYID"];
            $datax["GENREID"]=$trans["GENREID"];
            $datax["AMOUNT"]=$trans["AMOUNT"];
            $datax["MOTIVEID"]=$trans["MOTIVEID"];
            $datax["BOWID"]="[:SYSID(".$trans["BOWID"].")]";
            $datax["TARGETID"]="[:SYSID(".$trans["TARGETID"].")]";
            $datax["SVINCOLANTE"]=$trans["SVINCOLANTE"];
            unset($trans);
            // Aggancio all'istruzione
            $statement["data"]=$datax;
            $program[]=$statement;
            unset($datax);
            unset($statement);
            
            $statement=array();
            // Tabella
            $statement["table"]="QVQUIVERARROW";
            $statement["extension"]="";
            // Dati
            $datax=array();
            $datax["QUIVERID"]="[:SYSID(".$PROCESSOID.")]";
            $datax["ARROWID"]="[:SYSID(".$TRANSID.")]";
            unset($trans);
            // Aggancio all'istruzione
            $statement["data"]=$datax;
            $program[]=$statement;
            unset($datax);
            unset($statement);
        }                        
        
        // Aggancio alla radice
        $export["type"]="PROCESSO";
        $export["description"]=$EXPORTDESCR;
        $export["program"]=$program;
        unset($program);
        
        // SERIALIZZAZIONE
        $buff=serialize($export);
        
        // SCRITTURA SU FILE
        $pathname=$path_customize."_export/$EXPORTNAME.QVR";
        $fp=fopen($pathname, "wb");
        fwrite($fp, $buff);
        fclose($fp);
        
        // VARIABILI DI RITORNO
        $babelparams["EXPORTED"]="_export/$EXPORTNAME.QVR";
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