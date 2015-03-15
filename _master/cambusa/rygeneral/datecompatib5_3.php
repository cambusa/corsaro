<?php
/****************************************************************************
* Name:            datecompatib5_3.php                                      *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
class DateInterval{
    var $days;
    function DateInterval($formula){
        $this->days=intval(substr($formula,1));
    }
}

function date_diff($d1, $d2){
    if($d1<=$d2){
        $signum=1;
    }
    else{
        $signum=-1;
        $d0=$d1;
        $d1=$d2;
        $d2=$d0;
    }
    $days=0;
    
    $y1=intval($d1->format("Y"));
    $m1=intval($d1->format("m"));
    $d1=intval($d1->format("d"));
    
    $y2=intval($d2->format("Y"));
    $m2=intval($d2->format("m"));
    $d2=intval($d2->format("d"));
    $t2=mktime(0,0,0,$m2,$d2,$y2);
    
    while(true){
        $curr=mktime(0,0,0,$m1,$d1+$days,$y1);
        if($curr<$t2){
            $days+=1;
        }
        else{
            break;
        }
    }
    return new DateInterval("P".($signum*$days));
}
function date_add($date, $interval){
    $y1=intval($date->format("Y"));
    $m1=intval($date->format("m"));
    $d1=intval($date->format("d"));
    $days=$interval->days;
    $new=mktime(0,0,0,$m1,$d1+$days,$y1);
    $y=intval(date("Y", $new));
    $m=intval(date("m", $new));
    $d=intval(date("d", $new));
    return date_create($y."-".substr("00".$m,-2)."-".substr("00".$d,-2));
}
?>