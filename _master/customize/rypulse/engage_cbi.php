<?php
$xdata=array();
$instr=0;
function engage_main(){
    global $VLAD, $PARAMS, $PAPER, $maestro;
    global $CONTOTESORERIA;
    global $public_sessionid;
    global $xdata,$instr;

    maestro_query($maestro, "SELECT SYSID FROM QW_CONTI WHERE NAME='CONTOTESORERIA'", $r);
    if(count($r)==1)
        $CONTOTESORERIA=$r[0]["SYSID"];
    else
        $CONTOTESORERIA="";

    $VLAD->config('../../customize/ryvlad/cbi.json');
    $VLAD->config('
    {
        "source":"../../customize/_import/fonte.cbi",
        "trace":""
    }
    ');
    $VLAD->bleed();
    
    if(isset($PARAMS["env"]))
        $env=$PARAMS["env"];
    else
        $env="demo";
        
    $json=quiver_execute($public_sessionid, $env, true, $xdata);
    $r=json_decode($json);
    if($r->success==0){
        writelog($json);
    }
    
    pulse_notification($env, "demiurge", "Acquisizione CBI", "Acquisiti $instr movimenti da fonte CBI.", 2);
    
    //pulse_sendmail("demiurge", "Acquisizione CBI", "Acquisiti $instr movimenti da fonte CBI.");
    
    return "Tutto OK";
}

function dettaglio_complete(){
    global $BLOOD, $PARAMS, $tocambusa, $url_cambusa, $public_sessionid;
    global $CONTOTESORERIA;
    global $xdata,$instr;
    
    $xdata[$instr]=array();
    $xdata[$instr]["function"]="arrows_insert";
    $xdata[$instr]["data"]=array();
    $xdata[$instr]["data"]["TYPOLOGYID"]="0MOVIMENTI0000";
    $xdata[$instr]["data"]["DESCRIPTION"]=$BLOOD->data("DESCRIZIONE");
    $xdata[$instr]["data"]["GENREID"]="0MONEYEURO0000";
    $xdata[$instr]["data"]["MOTIVEID"]="0CAUSPAG000000";

    if(rand(1,10)<=5)
        $xdata[$instr]["data"]["BOWID"]=$CONTOTESORERIA;
    else
        $xdata[$instr]["data"]["TARGETID"]=$CONTOTESORERIA;
    
    if(rand(1,10)<=5)
        $xdata[$instr]["data"]["CONSISTENCY"]="0";
    else
        $xdata[$instr]["data"]["CONSISTENCY"]="1";
        
    $xdata[$instr]["data"]["AMOUNT"]=$BLOOD->data("IMPORTO");
    $xdata[$instr]["data"]["BOWTIME"]=$BLOOD->data("DATAVAL");
    $xdata[$instr]["data"]["TARGETTIME"]=$BLOOD->data("DATAVAL");
    $xdata[$instr]["data"]["AUXTIME"]=$BLOOD->data("DATABAN");
    $xdata[$instr]["data"]["REGISTRY"]=$BLOOD->data("REGISTRO");
    
    $instr+=1;
}

?>