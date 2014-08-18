<?php
function engage_main(){
    global $PARAMS, $public_sessionid;

    $env="demo";

    $data=array();
    $data["MAILBOX"]="mailbox";
    
    if(isset($PARAMS["env"])){
        $env=$PARAMS["env"];
    }

    if(isset($PARAMS["mbox"])){
        $data["MAILBOX"]=$PARAMS["mbox"];
    }
    
    $json=quiver_execute($public_sessionid, $env, false, "pratiche_imap", $data);
}
?>