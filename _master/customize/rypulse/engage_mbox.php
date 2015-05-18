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
    
    $params=array();
    $params["sessionid"]=$public_sessionid;
    $params["environ"]=$env;
    $params["function"]="pratiche_imap";
    $params["data"]=$data;
    
    $json=quiver_execute($params);
}
?>