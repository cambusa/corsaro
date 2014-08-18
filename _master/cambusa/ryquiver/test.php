<?php

set_time_limit(0);

include("quiverlib.php");

$maestro=maestro_opendb("acme");

maestro_begin($maestro);

qv_bulkinitialize($maestro);

include_once "qv_arrows_insert.php";

for($i=0; $i<1; $i++){
    $data=array();
    $data["BOWNAME"]="INFER";
    $data["BOWTIME"]="20130405";
    $data["TARGETNAME"]="INFER";
    $data["TARGETTIME"]="20130407";
    $data["TYPOLOGYNAME"]="ECTOPLASMA2";
    $data["MOTIVENAME"]="MOTIVO";
    $data["GENRENAME"]="DENOMINAZIONE";
    $data["AMOUNT"]=23.567;
    $data["ATTRIBUTO"]="CICCIO";
    
    $jret=qv_arrows_insert($maestro, $data);
    var_export( json_encode($jret) );
    print "<br>";
}


if($jret["success"])
    maestro_commit($maestro);
else
    maestro_rollback($maestro);

maestro_closedb($maestro);
    
?>