<?php
try{
    if(isset($_SERVER["SESSIONNAME"])){
        if(strtoupper($_SERVER["SESSIONNAME"])=="CONSOLE"){
            $fp=fopen("_pulse.stop", "w");
            fwrite($fp, "1");
            fclose($fp);
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