<?php
function engage_main(){
    global $PARAMS, $public_sessionid;

    $data=array();
    
    if(isset($PARAMS["env"]))
        $env=$PARAMS["env"];
    else
        $env="demo";

    $json=quiver_execute($public_sessionid, $env, false, "pratiche_scadenze", $data);
}
?>