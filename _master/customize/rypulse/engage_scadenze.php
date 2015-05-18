<?php
function engage_main(){
    global $PARAMS, $public_sessionid;

    $data=array();
    
    if(isset($PARAMS["env"]))
        $env=$PARAMS["env"];
    else
        $env="demo";
        
    $params=array();
    $params["sessionid"]=$public_sessionid;
    $params["environ"]=$env;
    $params["function"]="pratiche_scadenze";
    $params["data"]=$data;

    $json=quiver_execute($params);
}
?>