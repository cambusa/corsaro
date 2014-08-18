<?php
function engage_main(){
    global $VLAD, $PARAMS, $PAPER, $maestro;
    global $CONTOTESORERIA;

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
    
    return "Tutto OK";
}

function dettaglio_complete(){
    global $BLOOD, $PARAMS, $tocambusa, $url_cambusa, $public_sessionid;
    global $CONTOTESORERIA;
    
    $data=array();
    $data["TYPOLOGYID"]="0MOVIMENTI0000";
    $data["DESCRIPTION"]=$BLOOD->data("DESCRIZIONE");
    $data["GENREID"]="0MONEYEURO0000";
    $data["MOTIVEID"]="0CAUSPAG000000";

    if(rand(1,10)<=5)
        $data["BOWID"]=$CONTOTESORERIA;
    else
        $data["TARGETID"]=$CONTOTESORERIA;
    
    if(rand(1,10)<=5)
        $data["CONSISTENCY"]="0";
    else
        $data["CONSISTENCY"]="1";
        
    $data["AMOUNT"]=$BLOOD->data("IMPORTO");
    $data["BOWTIME"]=$BLOOD->data("DATAVAL");
    $data["TARGETTIME"]=$BLOOD->data("DATAVAL");
    $data["AUXTIME"]=$BLOOD->data("DATABAN");
    $data["REGISTRY"]=$BLOOD->data("REGISTRO");
    
    if(isset($PARAMS["env"]))
        $env=$PARAMS["env"];
    else
        $env="demo";
        
    $json=quiver_execute($public_sessionid, $env, false, "arrows_insert", $data);
    //writelog(serialize($json));
}

?>