<?php
/****************************************************************************
* Name:            fattura.php                                              *
* Project:         Customize                                                *
* Version:         1.69                                                     *
* Description:     Fattura cartacea                                         *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function custMain($maestro, $data){
    global $babelcode, $babelparams;
    global $path_databases, $path_customize, $safe_extensions, $url_customize;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        if(isset($data["praticaid"]))
            $praticaid=$data["praticaid"];
        else
            $praticaid="";

        $TBS=new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
        $TBS->LoadTemplate(dirname(__FILE__)."/fattura.odt");
        
        $env_strconn="";
        $envtemporary=qv_setting($maestro, "_TEMPENVIRON", "temporary");
        include($path_databases."_environs/".$envtemporary.".php");
        $temporary=$env_strconn;
        
        // DATI PRATICA
        $sql="SELECT * FROM QW_PRATICHEJOIN WHERE SYSID='$praticaid'";
        maestro_query($maestro, $sql, $prat);
        if(count($prat)==1){
            $CONTOID=$prat[0]["CONTOID"];
            
            $TBS->MergeField("OGGI", date("d/m/Y"));
            $TBS->MergeField("PAGAMENTO", "_______________");
            $TBS->MergeField("SCADENZA", formatta_data($prat[0]["DATAFINE"]));
        
            // DATI RICHIEDENTEID
            $RICHIEDENTEID=$prat[0]["RICHIEDENTEID"];
            $sql="SELECT * FROM QW_ATTORIJOIN WHERE SYSID='$RICHIEDENTEID'";
            maestro_query($maestro, $sql, $att);
            array_walk_recursive($att, "qv_escapizeUTF8");
            $TBS->NoErr=true;
            $TBS->MergeBlock("att", $att);
            
            // MOTIVO FATTURA
            $sql="SELECT SYSID FROM QW_MOTIVIATTIVITA WHERE [:UPPER(NAME)]='_VENDITEFATTURA'";
            maestro_query($maestro, $sql, $mot);
            if(count($mot)>0)
                $motiveid=$mot[0]["SYSID"];
            else
                $motiveid="";

            // DATI FATTURA
            $sql="SELECT QW_ATTIVITA.PROTPROGR AS PROTPROGR FROM QVQUIVERARROW INNER JOIN QW_ATTIVITA ON QW_ATTIVITA.SYSID=QVQUIVERARROW.ARROWID WHERE QVQUIVERARROW.QUIVERID='$praticaid' AND QW_ATTIVITA.MOTIVEID='$motiveid'";
            maestro_query($maestro, $sql, $ord);
            if(count($ord)>0)
                $TBS->MergeField("NUMFATT", $ord[0]["PROTPROGR"]);
            else
                $TBS->MergeField("NUMFATT", "---");
            
            // MOTIVO ORDINE
            $sql="SELECT SYSID FROM QW_MOTIVIATTIVITA WHERE [:UPPER(NAME)]='_VENDITEORDINE'";
            maestro_query($maestro, $sql, $mot);
            if(count($mot)>0)
                $motiveid=$mot[0]["SYSID"];
            else
                $motiveid="";
            
            // DATI ORDINE
            $sql="SELECT QW_ATTIVITA.PROTPROGR AS PROTPROGR FROM QVQUIVERARROW INNER JOIN QW_ATTIVITA ON QW_ATTIVITA.SYSID=QVQUIVERARROW.ARROWID WHERE QVQUIVERARROW.QUIVERID='$praticaid' AND QW_ATTIVITA.MOTIVEID='$motiveid'";
            maestro_query($maestro, $sql, $ord);
            if(count($ord)>0)
                $TBS->MergeField("NUMORD", $ord[0]["PROTPROGR"]);
            else
                $TBS->MergeField("NUMORD", "---");
            
            // FLUSSI
            $lordo=0;
            $totimposta=0;
            $totimponibile=0;
            $flussi=array();
            $sql="SELECT * FROM QW_FLUSSI WHERE SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$praticaid')";
            maestro_query($maestro, $sql, $f);
            for($i=0; $i<count($f); $i++){
                $BOWID=$f[$i]["BOWID"];
                if($CONTOID!="" && $BOWID==$CONTOID)
                    $segno=1;
                else
                    $segno=-1;
                $flussi[$i]=array();
                $flussi[$i]["CODICE"]="";
                $flussi[$i]["BREVITY"]="N";
                $flussi[$i]["AMOUNT"]="1";
                $flussi[$i]["DESCRIPTION"]=$f[$i]["DESCRIPTION"];

                $importo=$segno*round(floatval($f[$i]["AMOUNT"]), 2);
                $aliquota=floatval($f[$i]["ALIQUOTA"]);
                $flussi[$i]["PREZZO"]=formatta_numero($importo, 2);
                $flussi[$i]["IMPORTO"]=formatta_numero($importo, 2);
                $flussi[$i]["ALIQUOTA"]=$aliquota."%";
                
                $lordo+=$importo;
                
                if($aliquota>0){
                    $imposta=round($importo*$aliquota/100, 2);
                    $imponibile=$importo;
                    $totimponibile+=$imponibile;
                    $totimposta+=$imposta;
                }
                
                $TRASFID=$f[$i]["REFARROWID"];
                if($TRASFID!=""){
                    $sql="SELECT * FROM QW_TRASFERIMENTI WHERE SYSID='$TRASFID'";
                    maestro_query($maestro, $sql, $t);
                    if(count($t==1)){
                        $quantita=floatval($t[0]["AMOUNT"]);
                        $GENREID=$t[0]["GENREID"];
                        $sql="SELECT * FROM QW_ARTICOLI WHERE SYSID='$GENREID'";
                        maestro_query($maestro, $sql, $art);
                        if(count($art)>0){
                            $flussi[$i]["CODICE"]=$art[0]["CODICE"];
                            $flussi[$i]["BREVITY"]=$art[0]["BREVITY"];
                            $ROUNDING=floatval($art[0]["ROUNDING"]);
                        }
                        else{
                            $flussi[$i]["CODICE"]="";
                            $flussi[$i]["BREVITY"]="N";
                            $ROUNDING=0;
                        }
                        $flussi[$i]["AMOUNT"]=formatta_numero($quantita, $ROUNDING);
                        if($quantita>0){
                            $flussi[$i]["PREZZO"]=formatta_numero($importo/$quantita, 2);
                        }
                    }
                }
            }
            array_walk_recursive($flussi, "qv_escapizeUTF8");
            $TBS->NoErr=true;
            $TBS->MergeBlock("flussi", $flussi);
            
            $lordo=round($lordo, 2);
            $totimposta=round($totimposta, 2);
            
            $TBS->MergeField("LORDO", formatta_numero($lordo, 2));
            $TBS->MergeField("IMPONIBILE", formatta_numero($totimponibile, 2));
            $TBS->MergeField("IMPOSTA", formatta_numero($totimposta, 2));
            $TBS->MergeField("TOTFATT", formatta_numero($lordo+$totimposta, 2));
            
            // GENERAZIONE DOCUMENTO
            $filetmp=$temporary."F$praticaid.odt";
            $TBS->Show(OPENTBS_FILE, $filetmp);

            // VARIABILI DI RITORNO
            $babelparams["PATH"]=$filetmp;
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
    $j["SYSID"]="";
    return $j; //ritorno standard
}    
?>