<?php
/****************************************************************************
* Name:            pulse_util.php                                           *
* Project:         Cambusa/ryPulse                                          *
* Version:         1.69                                                     *
* Description:     Scheduler                                                *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
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
        preg_match_all("/([^,;|]+)[,;|]?/", $notify, $m);
        $u=$m[1];
        for($j=0;$j<count($u);$j++){
            $user=$u[$j];
            $object="Scheduler";
            $text=$response;
            $rm=egomail($user, $object, $text);
            if($rm["success"]==0){
                $response="pulse_heart.php: file ".$script."\r\n".$rm["description"];
                writelog($response);
            }
        }
    }
}
function pulse_sendmail($notify, $object, $text){
    preg_match_all("/([^,;|]+)[,;|]?/", $notify, $m);
    $u=$m[1];
    for($j=0;$j<count($u);$j++){
        $user=$u[$j];
        $rm=egomail($user, $object, $text);
        if($rm["success"]==0){
            writelog($rm["description"]);
        }
    }
}
function pulse_notification($env, $notify, $object, $text, $priority=1){
    global $public_sessionid;
    preg_match_all("/([^,;|]+)[,;|]?/", $notify, $m);
    $u=$m[1];
    $xdata=array();
    $instr=0;
    for($j=0;$j<count($u);$j++){
        $user=$u[$j];
        $xdata[$instr]=array();
        $xdata[$instr]["function"]="messages_send";
        $xdata[$instr]["fallible"]=1;
        $xdata[$instr]["data"]=array();
        $xdata[$instr]["data"]["SENDERNAME"]="SERVER";
        $xdata[$instr]["data"]["RECEIVERNAME"]=$user;
        $xdata[$instr]["data"]["DESCRIPTION"]=$object;
        $xdata[$instr]["data"]["REGISTRY"]=$text;
        $xdata[$instr]["data"]["PRIORITY"]=$priority;
        $instr+=1;
    }
    $json=quiver_execute($public_sessionid, $env, false, $xdata);
    $r=json_decode($json);
    if($r->success!=1){
        writelog($json);
    }
}
?>