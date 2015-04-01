<?php
/****************************************************************************
* Name:            pulse_heart.php                                          *
* Project:         Cambusa/ryPulse                                          *
* Version:         1.69                                                     *
* Description:     Scheduler                                                *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
try{
    // CARICO LE LIBRERIE
    if(!isset($tocambusa))
        $tocambusa="../";
    include_once $tocambusa."rymaestro/maestro_execlib.php";
    include_once $tocambusa."ryquiver/quiversex.php";
    include_once $tocambusa."rygeneral/writelog.php";
    include_once $tocambusa."rygeneral/datetime.php";
    include_once $tocambusa."ryego/ego_sendmail.php";
    include_once $tocambusa."ryvlad/ryvlad.php";
    include_once $tocambusa."ryquiver/_quiver.php";
    include_once $tocambusa."rypaper/rypaper.php";
    include_once $tocambusa."rypulse/pulse_util.php";
    
    // DETERMINO LA SESSIONID
    if(isset($_POST["sessionid"]))
        $sessionid=$_POST["sessionid"];
    elseif(isset($_GET["sessionid"]))
        $sessionid=$_GET["sessionid"];
    else
        $sessionid="";

    // DETERMINO SE DEVO LANCIARE O SOLO RICALCOLARE LE SCADENZE
    if(isset($_POST["exec"]))
        $exec=intval($_POST["exec"]);
    elseif(isset($_GET["exec"]))
        $exec=intval($_GET["exec"]);
    else
        $exec=1;
        
    $success=1;
    $description="Attivazione riuscita";
    
    // SOSTITUZIONI PER LE DATE
    $trdate=array("-", ":", "T", " ", "'", ".");

    // APRO IL DATABASE
    $maestro_pulse=maestro_opendb("rypulse", false);

    if($maestro_pulse->conn!==false){
    
        // SCANDISCO LE AZIONI ABILITATE
        $sql="SELECT * FROM ENGAGES WHERE ENABLED=1 AND ( RUNNING=0 OR RUNNING IS NULL OR [:TIME(LASTENGAGE,15MINUTES)]<=[:NOW()] ) ORDER BY NEXTENGAGE";
        maestro_query($maestro_pulse, $sql, $r);
        for($i=0;$i<count($r);$i++){
            // SYSID
            $sysid=$r[$i]["SYSID"];

            // SCRIPT DA LANCIARE
            $script=$r[$i]["ENGAGE"];
            $script=str_replace("@customize/", $path_customize, $script);
            $script=str_replace("@cambusa/", "../", $script);
            $script=str_replace("@databases/", $path_databases, $script);
            
            // PARAMETRI DA PASSARE ALLO SCRIPT
            $PARAMS=array();
            $params=$r[$i]["PARAMS"];
            if($params!=""){
                if($json=json_decode($params)){
                    $PARAMS=jsonObjectToArray($json);
                }
                else{
                    writelog("Parametri non corretti");
                    break;
                }
            }
                
            // ELENCO INDIRIZZI DI NOTIFICA
            $notify=trim($r[$i]["NOTIFY"]);
            $descr=$r[$i]["DESCRIPTION"];
            
            // ULTIMA ATTIVAZIONE
            $last=str_replace($trdate, "", $r[$i]["LASTENGAGE"]);
                
            // PROSSIMA ATTIVAZIONE
            $next=str_replace($trdate, "", $r[$i]["NEXTENGAGE"]);
            
            // TOLLERANZA
            $tolerance=$r[$i]["TOLERANCE"];
            if($tolerance=="")
                $tolerance="1HOURS";
            else
                $tolerance=strtoupper(trim($tolerance));
            $valtolerance=intval($tolerance);
            
            // LATENZA
            $latency=strtoupper(trim($r[$i]["LATENCY"]));
            if($latency=="")
                $latency="1MINUTES";
            else
                $latency=strtoupper(trim($latency));
            $vallatency=intval($latency);
            
            // FILTRO MESI
            $months=trim($r[$i]["MONTHS"]);
            $arraym=explode(",", $months);
            $arraym=array_map("numerize", $arraym);
            
            // FILTRO DATA
            $days=trim($r[$i]["DAYS"]);
            $arrayd=explode(",", $days);
            $arrayd=array_map("numerize", $arrayd);
            
            // FILTRO SETTIMANA
            $week=strtoupper(trim($r[$i]["WEEK"]));
            $arrayw=explode(",", $week);
            $arrayw=array_map("weekize", $arrayw);
            
            // FILTRO BUSINESSDAY
            $businessday=intval($r[$i]["BUSINESSDAY"]);

            // FILTRO ORE
            $hours=trim($r[$i]["HOURS"]);
            $arrayh=explode(",", $hours);
            $arrayh=array_map("numerized", $arrayh);
            
            // FILTRO MINUTI
            if($hours=="")
                $minutes=trim($r[$i]["MINUTES"]);
            else
                $minutes="";
            $arrayi=explode(",", $minutes);
            $arrayi=array_map("numerize", $arrayi);

            // UNA TANTUM
            $once=intval($r[$i]["UNATANTUM"]);

            // ADESSO
            $now=date("YmdHis");
            
            $counter=0;
            
            if($next<=$now){
                $counter+=1;
                // RICALCOLO LA PROSSIMA ATTIVAZIONE
                $new="";
                $dp=0;
                $di=1;
                $basey=intval(date("Y"));
                $basem=intval(date("m"));
                $based=intval(date("d"));
                $baseh=intval(date("H"));
                $basei=intval(date("i"));
                while($new==""){
                    $valid=true;
                    $curr=mktime($baseh, $basei+$di, 0, $basem, $based+$dp, $basey);
                    $newy=intval(date("Y",$curr));
                    $newm=intval(date("m",$curr));
                    $newd=intval(date("d",$curr));
                    $neww=intval(date("w",$curr));
                    $newh=intval(date("H",$curr));
                    $newi=intval(date("i",$curr));
                    $newf=(intval( date("d", mktime(0, 0, 0, $newm, $newd+1, $newy)) )==1);
                    
                    if($months!=""){
                        // GESTIONE FILTRO MESI
                        if(!in_array($newm,$arraym)){
                            $dp+=1;
                            $baseh=0;
                            $basei=0;
                            $di=0;
                            $valid=false;
                        }
                    }
                    if($valid){
                        // GESTIONE FILTRO GIORNI DEL MESE (0 indica fine mese)
                        if($days!=""){
                            if(!in_array($newd,$arrayd) && (!$newf || !in_array(0,$arrayd)) ){
                                $dp+=1;
                                $baseh=0;
                                $basei=0;
                                $di=0;
                                $valid=false;
                            }
                        }
                    }
                    if($valid){
                        // GESTIONE FILTRO GIORNI DELLA SETTIMANA
                        if($week!=""){
                            if(!in_array($neww, $arrayw)){
                                $dp+=1;
                                $baseh=0;
                                $basei=0;
                                $di=0;
                                $valid=false;
                            }
                        }
                    }
                    if($valid){
                        // GESTIONE FILTRO GIORNO LAVORATIVO
                        if($businessday>0){
                            $lav=ry_businessday($newy, $newm, $newd);
                            if(($businessday==1 && !$lav) || ($businessday==2 && $lav)){
                                $dp+=1;
                                $baseh=0;
                                $basei=0;
                                $di=0;
                                $valid=false;
                            }
                        }
                    }
                    if($valid){
                        // GESTIONE FILTRO ORE
                        if($hours!=""){
                            if(!in_array($newh.":".$newi, $arrayh)){
                                $di+=1;
                                $valid=false;
                            }
                        }
                    }
                    if($valid){
                        // GESTIONE FILTRO MINUTI
                        if($minutes!=""){
                            if(!in_array($newi, $arrayi)){
                                $di+=1;
                                $valid=false;
                            }
                        }
                    }
                    if($valid){
                        $new=date("YmdHis", $curr);
                    }
                    if($counter>100000000)
                        break;
                }
                if($new!=""){
                    $sql="UPDATE ENGAGES SET NEXTENGAGE=[:TIME($new)] WHERE SYSID='$sysid'";
                    maestro_execute($maestro_pulse, $sql, false);
                }

                if($next!=""){
                    // UNA PROSSIMA ESECUZIONE ERA STATA CALCOLATA
                    $timetolerance=$now;
                    $timelatency=$now;
                    
                    // CALCOLO IL TEMPO ENTRO LA TOLLERANZA
                    $xy=intval(substr($next,0,4));
                    $xm=intval(substr($next,4,2));
                    $xd=intval(substr($next,6,2));
                    $xh=intval(substr($next,8,2));
                    $xi=intval(substr($next,10,2));
                    $xs=intval(substr($next,12,2));
                    
                    if(strpos($tolerance,"MINUTES")!==false)
                        $timetolerance=date("YmdHis", mktime($xh,$xi+$valtolerance,$xs,$xm,$xd,$xy));
                    elseif(strpos($tolerance,"HOURS")!==false)
                        $timetolerance=date("YmdHis", mktime($xh+$valtolerance,$xi,$xs,$xm,$xd,$xy));
                    elseif(strpos($tolerance,"DAYS")!==false)
                        $timetolerance=date("YmdHis", mktime($xh,$xi,$xs,$xm,$xd+$valtolerance,$xy));
                    elseif(strpos($tolerance,"MONTHS")!==false)
                        $timetolerance=date("YmdHis", mktime($xh,$xi,$xs,$xm+$valtolerance,$xd,$xy));

                    if($last!=""){
                        // CALCOLO IL TEMPO OLTRE LA LATENZA
                        $ly=intval(substr($last,0,4));
                        $lm=intval(substr($last,4,2));
                        $ld=intval(substr($last,6,2));
                        $lh=intval(substr($last,8,2));
                        $li=intval(substr($last,10,2));
                        $ls=intval(substr($last,12,2));
                        
                        if(strpos($latency,"MINUTES")!==false)
                            $timelatency=date("YmdHis", mktime($lh,$li+$vallatency,$ls,$lm,$ld,$ly));
                        elseif(strpos($latency,"HOURS")!==false)
                            $timelatency=date("YmdHis", mktime($lh+$vallatency,$li,$ls,$lm,$ld,$ly));
                        elseif(strpos($latency,"DAYS")!==false)
                            $timelatency=date("YmdHis", mktime($lh,$li,$ls,$lm,$ld+$vallatency,$ly));
                        elseif(strpos($latency,"MONTHS")!==false)
                            $timelatency=date("YmdHis", mktime($lh,$li,$ls,$lm+$vallatency,$ld,$ly));
                    }
                    
                    // SE SONO ENTRO LA TOLLERENZA E FUORI DELLA LATENZA
                    // LANCIO LO SCRIPT
                    if($exec && $timelatency<=$now && $now<=$timetolerance){
                        if(is_file($script)){
                            try{
                                include_once $script;
                                // ESEGUO
                                pulse_execute($maestro_pulse, $sysid, $script, $notify, $now, $once, $success, $description);
                            }
                            catch(Exception $e){
                                $success=0;
                                $description=$e->getMessage();
                                writelog("pulse_heart.php:\r\n$description");
                            }
                        }
                        else{
                            $success=0;
                            $description="File ".$script." doesn't exist";
                            writelog("pulse_heart.php:\r\n$description");
                        }
                        break;
                    }
                }
            }
        }
    }
    else{
        $success=0;
        $description="Impossibile aprire il database";
    }
    
    // CHIUDO IL DATABASE
    maestro_closedb($maestro_pulse);
}
catch(Exception $e){
    $success=0;
    $description=$e->getMessage();
    $response="pulse_heart.php:\r\n".$description;
    writelog($response);
}

// USCITA JSON
$j=array();
$j["success"]=$success;
$j["description"]=htmlentities($description);
print json_encode($j);

function numerize($value){
    return intval($value);
}
function numerized($value){
    $v=explode(":",$value);
    if(count($v)>=2){
        $h=intval($v[0]);
        $m=intval($v[1]);
    }
    else{
        $h=intval($v[0]);
        $m=0;
    }
    if($h<0)
        $h=0;
    elseif($h>23)
        $h=23;
    if($m<0)
        $m=0;
    elseif($m>59)
        $m=59;
    return "$h:$m";
}
function weekize($value){
    $r=1;
    switch($value){
        case "SU":$r=0;break;
        case "MO":$r=1;break;
        case "TU":$r=2;break;
        case "WE":$r=3;break;
        case "TH":$r=4;break;
        case "FR":$r=5;break;
        case "SA":$r=6;break;
        default: $r=intval($value);
    }
    return $r;
}
?>