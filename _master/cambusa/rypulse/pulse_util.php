<?php
function pulse_execute($maestro_pulse, $sysid, $script, $notify, $now, $once, &$success, &$description){
    global $maestro, $PARAMS;

    // UNA TANTUM
    if($once)
        $unatantum=",ENABLED=0";
    else
        $unatantum="";
    
    // RUNNING
    $sql="UPDATE ENGAGES SET LASTENGAGE=[:TIME($now)],RUNNING=1 $unatantum WHERE SYSID='$sysid'";
    maestro_execute($maestro_pulse, $sql, false);
    
    // SE E' SPECIFICATO UN AMBIENTE APRO IL DATABASE
    $maestro=false;
    if(isset($PARAMS["env"])){
        $env=$PARAMS["env"];
        $maestro=maestro_opendb($env);
    }
    
    try{
        if(function_exists("engage_main")){
            $response=@engage_main();
        }
        else{
            $response="Function [engage_main] doesn't exist";
            writelog($response);
        }
    }
    catch(Exception $e){
        $success=0;
        $description=$e->getMessage();
        $response="pulse_heart.php:\r\n".$description;
        writelog($response);
    }
    
    // CHIUDO IL DATABASE DI AMBIENTE
    if($maestro!==false){
        maestro_closedb($maestro);
    }
    
    // NO RUNNING
    $sql="UPDATE ENGAGES SET RUNNING=0 WHERE SYSID='$sysid'";
    maestro_execute($maestro_pulse, $sql, false);
    // INVIO DELLE EMAIL DI NOTIFICA
    if($notify!=""){
        $u=explode(",",$notify);
        for($j=0;$j<count($u);$j++){
            $user=$u[$j];
            $object="Scheduler:".$descr;
            $text=$response;
            $rm=egomail($user, $object, $text);
            if($rm["success"]==0){
                $response="pulse_heart.php: file ".$script."\r\n".$rm["description"];
                writelog($response);
            }
        }
    }
}
?>