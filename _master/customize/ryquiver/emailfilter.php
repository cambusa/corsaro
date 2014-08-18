<?php
function email_filter($maestro, &$params, &$options){
    $ret=false;
    
    $d=$params["DESCRIPTION"];
    if(preg_match("/\[([A-Z0-9]{14})\]/", $d, $m)){
        $options["PROCESSONAME"]="_PROCRICHIESTE";
        $options["PRATICAID"]=$m[1];
        $ret=true;
    }
    else{
        $options["PROCESSONAME"]="_PROCRICHIESTE";
        $options["RISPOSTANAME"]="_RISPRICHIESTE";
        $ret=true;
    }
    return $ret;
}
?>