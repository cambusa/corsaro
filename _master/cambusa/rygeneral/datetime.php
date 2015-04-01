<?php
/****************************************************************************
* Name:            datetime.php                                             *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(floatval(phpversion())<5.3){
    include_once $path_cambusa."rygeneral/datecompatib5_3.php";
}
function ry_businessday($y,$m,$d){
    $w=intval(date("w", mktime(0, 0, 0, $m, $d, $y)));
    if($w==0 || $w==6){return false;}
    if($m==1 && $d==1){return false;}
    if($m==1 && $d==6){return false;}
    if($m==4 && $d==25){return false;}
    if($m==5 && $d==1){return false;}
    if($m==6 && $d==2){return false;}
    if($m==8 && $d==15){return false;}
    if($m==11 && $d==1){return false;}
    if($m==12 && $d==8){return false;}
    if($m==12 && $d==25){return false;}
    if($m==12 && $d==26){return false;}
    if( easter_date($y)==mktime(0, 0, 0, $m, $d-1, $y) ){return false;}
    return true;
}
function ry_datediff($d1, $d2){
    $interval=date_diff($d1, $d2);
    return $interval->days;
}
function ry_datediff365($d1, $d2){
    $interval=date_diff($d1, $d2);
    $days=$interval->days;
    
    $year1=intval($d1->format("Y"));
    $month1=intval($d1->format("m"));
    $day1=intval($d1->format("d"));
    $year2=intval($d2->format("Y"));
    $month2=intval($d2->format("m"));
    $day2=intval($d2->format("d"));
    
    if(($year1 % 4)==0){
        if($month1<=2)
            $days-=1;
    }
    if(($year2 % 4)==0){
        if($month2>2)
            $days-=1;
        elseif($month2==2 && $day2==29)
            $days-=1;
    }
    for($i=$year1+1;$i<$year2;$i++){
        if(($i % 4)==0)
            $days-=1;
    }
    return $days;
}
function ry_datediff360($d1, $d2){
    $year1=intval($d1->format("Y"));
    $month1=intval($d1->format("m"));
    $day1=intval($d1->format("d"));

    $year2=intval($d2->format("Y"));
    $month2=intval($d2->format("m"));
    $day2=intval($d2->format("d"));
          
    // metodo 30E/360
    if($day1==31)
        $day1=30;
    if($day2==31)
        $day2=30;
    return (($year2-$year1)*12+$month2-$month1)*30+$day2-$day1;
}
function ry_businessadd($date, $days){
    $str=is_string($date);
    if($str){
        $y=intval(substr($date, 0, 4));
        $m=intval(substr($date, 4, 2));
        $d=intval(substr($date, 6, 2));
    }
    else{
        $y=intval($date->format("Y"));
        $m=intval($date->format("m"));
        $d=intval($date->format("d"));
    }
    if($days>=0){
        $delta=1;
    }
    else{
        $delta=-1;
        $days=abs($days);
    }
    // Avanzo (arretro) fino al primo giorno lavorativo
    while( !ry_businessday($y,$m,$d) ){
        $curr=mktime(0,0,0,$m,$d+$delta,$y);
        $y=intval(date("Y", $curr));
        $m=intval(date("m", $curr));
        $d=intval(date("d", $curr));
    }
    $count=0;
    while(true){
        if( ry_businessday($y,$m,$d) ){
            if($count>=$days)
                break;
            $count+=1;
        }
        $curr=mktime(0,0,0,$m,$d+$delta,$y);
        $y=intval(date("Y", $curr));
        $m=intval(date("m", $curr));
        $d=intval(date("d", $curr));
    }
    if($str)
        return $y.substr("00".$m,-2).substr("00".$d,-2);
    else
        return date_create($y."-".substr("00".$m,-2)."-".substr("00".$d,-2));
}
function ry_dateadd($date, $days){
    $str=is_string($date);
    if($str){
        $y=substr($date, 0, 4);
        $m=substr($date, 4, 2);
        $d=substr($date, 6, 2);
        $date=date_create("$y-$m-$d");
    }
    $interval=new DateInterval("P".abs($days)."D");
    if($days<0){
        $interval->invert=1;
    }
    $new=date_add($date, $interval);
    if($str)
        return $new->format("Ymd");
    else
        return $new;
}
?>