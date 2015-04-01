<?php
/****************************************************************************
* Name:            ddt.php                                                  *
* Project:         Customize                                                *
* Version:         1.69                                                     *
* Description:     Documento di Trasporto                                   *
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
        $TBS->LoadTemplate(dirname(__FILE__)."/ddt.odt");
        
        $env_strconn="";
        $envtemporary=qv_setting($maestro, "_TEMPENVIRON", "temporary");
        include($path_databases."_environs/".$envtemporary.".php");
        $temporary=$env_strconn;
        
        // DATI PRATICA
        $sql="SELECT * FROM QW_PRATICHEJOIN WHERE SYSID='$praticaid'";
        maestro_query($maestro, $sql, $prat);
        if(count($prat)==1){
            $TBS->MergeField("OGGI", date("d/m/Y"));
            $TBS->MergeField("COLLI", "0");
            $TBS->MergeField("KG", "0");
            $TBS->MergeField("PORTO", "Assegnato");
            $TBS->MergeField("ASPETTO", "SCATOLONI");
            $TBS->MergeField("CAUSALE", "VENDITA");
        
            // DATI RICHIEDENTEID
            $RICHIEDENTEID=$prat[0]["RICHIEDENTEID"];
            $sql="SELECT * FROM QW_ATTORIJOIN WHERE SYSID='$RICHIEDENTEID'";
            maestro_query($maestro, $sql, $att);
            array_walk_recursive($att, "qv_escapizeUTF8");
            $TBS->NoErr=true;
            $TBS->MergeBlock("att", $att);
            
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
            
            // TRASFERIMENTI
            $sql="SELECT * FROM QW_TRASFERIMENTI WHERE SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$praticaid')";
            maestro_query($maestro, $sql, $trasf);
            for($i=0; $i<count($trasf); $i++){
                $GENREID=$trasf[$i]["GENREID"];
                $sql="SELECT * FROM QW_ARTICOLI WHERE SYSID='$GENREID'";
                maestro_query($maestro, $sql, $art);
                if(count($art)>0){
                    $trasf[$i]["CODICE"]=$art[0]["CODICE"];
                    $trasf[$i]["BREVITY"]=$art[0]["BREVITY"];
                    $ROUNDING=floatval($art[0]["ROUNDING"]);
                }
                else{
                    $trasf[$i]["CODICE"]="";
                    $trasf[$i]["BREVITY"]="N";
                    $ROUNDING=0;
                }
                $trasf[$i]["AMOUNT"]=formatta_numero($trasf[$i]["AMOUNT"], $ROUNDING);
            }
            array_walk_recursive($trasf, "qv_escapizeUTF8");
            $TBS->NoErr=true;
            $TBS->MergeBlock("trasf", $trasf);
            
            // GENERAZIONE DOCUMENTO
            $filetmp=$temporary."T$praticaid.odt";
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