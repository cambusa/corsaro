<?php
try{
    if(isset($_SERVER["SESSIONNAME"])){
        if(strtoupper($_SERVER["SESSIONNAME"])=="CONSOLE"){
            set_time_limit(0);
            include("../sysconfig.php");
            include("../rygeneral/post_request.php");
            $t=time();
            $p=$t;
            print date("Y-m-d H:i:s") . " - Enabled \r\n";
            while(true){
                $t=time();
                if($t-$p>10){
                    if(file_exists("_pulse.stop")){
                        @unlink("_pulse.stop");
                        break;
                    }
                    $p=$t;
                    $postdata=array(
                        'sessionid' => $public_sessionid
                    );
                    //do_post_request($url_cambusa."rypulse/pulse_heart.php", $postdata);
                    post_async($url_cambusa."rypulse/pulse_heart.php", $postdata);
                }
            }
            print date("Y-m-d H:i:s") . " - Disabled \r\n\r\n";
        }
        else{
            print date("Y-m-d H:i:s") . " - No CONSOLE \r\n\r\n";
        }
    }
    else{
        print date("Y-m-d H:i:s") . " - No SESSIONNAME \r\n\r\n";
    }
}
catch(Exception $e){
    print date("Y-m-d H:i:s") . " - Error ---> " . $e->getMessage() . "\r\n\r\n";
}
?>