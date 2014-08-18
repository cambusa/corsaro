<?php 
    set_time_limit(0);
    
    $here=realpath(dirname(__FILE__)."/.");
    $here=str_replace("\\", "/", $here);

    $list=array();
    $l=glob($here."/*");
    foreach($l as $file){
        if(is_dir($file)){
            if(basename($file)!="_master"){
                $list[]=$file;
            }
        }
    }

    $public_sessionid="ZZZZZZZZZZZZZZZZZZZZ";
    
    for($t=1; $t<=5; $t++){
        for($i=0; $i<count($list); $i++){
            @file_get_contents("http://www.rudyz.net/progetti/".basename($list[$i])."/cambusa/rypulse/pulse_heart.php/?sessionid=$public_sessionid");
            sleep(1);
        }
    }
?>