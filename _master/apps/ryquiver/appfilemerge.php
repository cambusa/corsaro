<?php
/****************************************************************************
* Name:            appfilemerge.php                                         *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function appfilemerge($maestro, $mergeparams, &$merge){
    global $babelcode, $babelparams;
    
    $CONTEXT=$mergeparams["_CONTEXT"];
    switch($CONTEXT){
    case "ARRAYS":
        foreach($mergeparams["DATA"] as $space => $arr){
            $merge[$space]=array("array" => $arr);
        }
        break;

    case "SINGLETON":
        $TABLE=ryqEscapize($mergeparams["TABLE"]);
        $SYSID=ryqEscapize($mergeparams["SYSID"]);

        $sql="SELECT * FROM $TABLE WHERE SYSID='$SYSID'";
        $merge["DATA"]=array("sql" => $sql);
        break;

    case "PRATICHE":
        if(isset($mergeparams["PRATICAID"])){
            $PRATICAID=ryqEscapize($mergeparams["PRATICAID"]);
        }
        else{
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        if(isset($mergeparams["ATTIVITAID"]))
            $ATTIVITAID=ryqEscapize($mergeparams["ATTIVITAID"]);
        else
            $ATTIVITAID="";
            
        // RISOLVO LA PRATICA
        $sql="SELECT * FROM QW_PRATICHEJOIN WHERE SYSID='$PRATICAID'";
        maestro_query($maestro, $sql, $prat);
        if(count($prat)==1){
            $merge["PRAT"]=array("array" => $prat);
            
            // RISOLVO IL PROCESSO
            $PROCESSOID=$prat[0]["PROCESSOID"];
            $sql="SELECT * FROM QW_PROCESSIJOIN WHERE SYSID='$PROCESSOID'";
            maestro_query($maestro, $sql, $proc);
            $merge["PROC"]=array("array" => $proc);
            
            // RISOLVO IL RICHIEDENTE
            $RICHIEDENTEID=$prat[0]["RICHIEDENTEID"];
            $sql="SELECT * FROM QW_ATTORIJOIN WHERE SYSID='$RICHIEDENTEID'";
            maestro_query($maestro, $sql, $rich);
            $merge["RICH"]=array("array" => $rich);
        
            // RISOLVO IL MEDIATORE
            $MEDIATOREID=$prat[0]["MEDIATOREID"];
            $sql="SELECT * FROM QW_ATTORIJOIN WHERE SYSID='$MEDIATOREID'";
            maestro_query($maestro, $sql, $med);
            $merge["MEDI"]=array("array" => $med);

            // RISOLVO LO STATO
            $STATOID=$prat[0]["STATOID"];
            $sql="SELECT * FROM QW_PROCSTATIJOIN WHERE SYSID='$STATOID'";
            maestro_query($maestro, $sql, $stat);
            $merge["STAT"]=array("array" => $stat);

            // RISOLVO L'ATTORE PROPRIETARIO
            $ATTOREID=$stat[0]["ATTOREID"];
            $sql="SELECT * FROM QW_ATTORIJOIN WHERE SYSID='$ATTOREID'";
            maestro_query($maestro, $sql, $prop);
            $merge["PROP"]=array("array" => $prop);
        
            // RISOLVO IL CONTO DELLO STATO
            $STATOCONTOID=$stat[0]["CONTOID"];
            $sql="SELECT * FROM QW_CONTI WHERE SYSID='$STATOCONTOID'";
            maestro_query($maestro, $sql, $r);
            $merge["SCON"]=array("array" => $r);
            unset($r);
        
            // RISOLVO IL CONTO DELLA PRATICA
            $CONTOID=$prat[0]["CONTOID"];
            $sql="SELECT * FROM QW_CONTI WHERE SYSID='$CONTOID'";
            maestro_query($maestro, $sql, $r);
            $merge["PCON"]=array("array" => $r);
            unset($r);
        
            // RISOLVO L'ATTIVITA'
            $sql="SELECT * FROM QW_ATTIVITAJOIN WHERE SYSID='$ATTIVITAID'";
            maestro_query($maestro, $sql, $att);
            $merge["ATTV"]=array("array" => $att);
            
            if(count($att)==1){
                // RISOLVO IL MITTENTE
                $BOWID=$att[0]["BOWID"];
                $sql="SELECT * FROM QW_ATTORIJOIN WHERE SYSID='$BOWID'";
                maestro_query($maestro, $sql, $r);
                $merge["ORIG"]=array("array" => $r);
                unset($r);
                
                // RISOLVO IL DESTINATARIO
                $TARGETID=$att[0]["TARGETID"];
                $sql="SELECT * FROM QW_ATTORIJOIN WHERE SYSID='$TARGETID'";
                maestro_query($maestro, $sql, $r);
                $merge["DEST"]=array("array" => $r);
                unset($r);
            }
            else{
                $merge["ORIG"]=array("array" => array());
                $merge["DEST"]=array("array" => array());
            }

            // RECORDSET
            $qarray="SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$PRATICAID')";
            
            // RISOLVO LE ATTIVITA'
            $sql="SELECT * FROM QW_ATTIVITAJOIN WHERE SCOPE=0 AND CONSISTENCY<=1 AND AVAILABILITY<=1 AND $qarray";
            maestro_query($maestro, $sql, $r);
            $merge["ATTS"]=array("array" => $r);
            unset($r);
            
            // RISOLVO I MOVIMENTI
            $sql="SELECT * FROM QW_MOVIMENTIJOIN WHERE $qarray";
            maestro_query($maestro, $sql, $r);
            qv_file_mergenum($r, "AMOUNT", 2);
            $merge["MOVS"]=array("array" => $r);
            unset($r);

            // RISOLVO I FLUSSI
            $sql="SELECT * FROM QW_FLUSSIJOIN WHERE $qarray";
            maestro_query($maestro, $sql, $r);
            qv_file_mergenum($r, "AMOUNT", 2);
            $merge["FLUS"]=array("array" => $r);
            unset($r);

            // RISOLVO I TRASFERIMENTI
            $sql="SELECT * FROM QW_TRASFERIMENTIJOIN WHERE $qarray";
            maestro_query($maestro, $sql, $r);
            qv_file_mergenum($r, "AMOUNT", 0);
            $merge["TRAS"]=array("array" => $r);
            unset($r);
            
            // RISOLVO GLI ATTORI COINVOLTI
            $sql="SELECT * FROM QW_ATTORIJOIN WHERE SYSID IN (SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='$PROCESSOID')";
            maestro_query($maestro, $sql, $r);
            $merge["INTS"]=array("array" => $r);
            unset($r);
        }
        break;
    }
}
?>