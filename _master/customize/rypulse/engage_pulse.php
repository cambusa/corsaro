<?php
function engage_main(){
    global $url_base;
    $sched=realpath("../../progetti/scheduler.php");
    include($sched);
}
?>