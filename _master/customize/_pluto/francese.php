<?php
/*
OPZIONI
-----------------------------------------------------------------------------------
[
{"id":"CAPITALE", "caption":"Capitale", "type":"2", "default":"10000"},
{"id":"SCADENZE", "caption":"Scadenze", "type":"0", "default":"12"},
{"id":"INIZIO", "caption":"Inizio", "type":"/"}
]
-----------------------------------------------------------------------------------

PARAMETRI
-----------------------------------------------------------------------------------
{
"TASSO":2.3,
"COMMISSIONI":1.2
}
-----------------------------------------------------------------------------------
*/
function plutoMain($DEVELOPER){
    
    // CAPITALE EROGATO
    if(isset($DEVELOPER->parametri["CAPITALE"]))
        $erog=$DEVELOPER->parametri["CAPITALE"];
    else
        $erog=10000;
    
    // NUMERO SCADENZE
    if(isset($DEVELOPER->parametri["SCADENZE"]))
        $scadenze=intval($DEVELOPER->parametri["SCADENZE"]);
    else
        $scadenze=12;
        
    // TASSO APPLICATO
    if(isset($DEVELOPER->parametri["TASSO"]))
        $tasso=floatval($DEVELOPER->parametri["TASSO"]);
    else
        $tasso=3;
        
    // COMMISSIONI IN TERMINI PERCENTUALI
    if(isset($DEVELOPER->parametri["COMMISSIONI"]))
        $commissioni=floatval($DEVELOPER->parametri["COMMISSIONI"]);
    else
        $commissioni=0.5;
    $commissioni=round($erog*$commissioni/100);
        
    // RATEAZIONE
    $svil="1M";

    // DATA ACCENSIONE
    if(isset($DEVELOPER->parametri["INIZIO"]))
        $inizio=$DEVELOPER->parametri["INIZIO"];
    else
        $inizio=date("Ymd");

    // PRIMA QUOTA CAPITALI
    $i=$tasso/1200;
    $rata=$erog*$i/(1-(1/(pow(1+$i, $scadenze))));
    $quoto=1+$i;
    $rimb=round($rata/pow($quoto, $scadenze), 2);
    
    // FLUSSO INIZIALE
    $DEVELOPER->sviluppo[]=array("DATA" => $inizio, "CAPITALE" => -$erog, "COMMISSIONI" => $commissioni);
    $DEVELOPER->sviluppo[]=array("DATA" => $inizio, "TASSO" => $tasso);

    // PIANO DATE
    $date=$DEVELOPER->sviluppodate($inizio, $svil, $scadenze+1, false);
    for($i=1; $i<count($date); $i++){
        $DEVELOPER->sviluppo[]=array("DATA" => $date[$i], "CAPITALE" => $rimb, "INTERESSI" => 0);
        $rimb=round($rimb*$quoto, 2);
    }
    
    // ULTIMA QUOTA A ZERO: INDICA A $DEVELOPER DI SOSTITUIRLO COL CAPITALE RESIDUO
    $DEVELOPER->sviluppo[count($DEVELOPER->sviluppo)-1]["CAPITALE"]=0;
    
    return true;
}
?>