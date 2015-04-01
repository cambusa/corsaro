<?php
/****************************************************************************
* Name:            financial.php                                            *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

/* ESEMPI
$f=array();
$f[0]=-1000;
$f[1]=1100;

$d=array();
$d[0]=date_create("2012-01-01");
$d[1]=date_create("2013-01-01");

ryfIRR($IRR,$f, $d);
print $IRR;
print "<br>";
print "360:".ry_datediff360($d[0], $d[1]);
print "<br>";
print "365:".ry_datediff365($d[0], $d[1]);
print "<br>";
print "Effettivo:".ry_datediff($d[0], $d[1]);
print "<br>";
print "ADD BUSY: ".date_format(ry_businessadd($d[0], 10),"Y-m-d H:i:s");
print "<br>";
print "ADD SOLAR:".date_format(ry_dateadd($d[0], 10),"Y-m-d H:i:s");
*/

function ryfIRR(&$IRR, $flows, $dates, $daycount=365){
    // $x1      tassi: x[k-1]
    // $xk             x[k]
    // $xk1            x[k+1]
    // $fn1     attualizzazioni: f( x[k-1] )
    // $fnk                      f( x[k]   )
    // $psilon  valore al di sotto del quale considero x[k]=x[k+1]
    try{ 
        $IRR=0;
        $ret=true;
        $psilon=0.0000001;
        $days=0;
        $numflows=count($flows);
         
        if($numflows>0){
            // Data iniziale
            $mindate=date_create("9999-01-01");
            for($i=0;$i<$numflows;$i++){
                if($dates[$i]<$mindate)
                    $mindate=$dates[$i];
            }

            // Aggregato dei flussi iniziali da uguagliare al tel-quel
            $telquel=0;
            for($i=0;$i<$numflows;$i++){
                if($dates[$i]==$mindate)
                    $telquel-=$flows[$i];
            }
             
            // Intervalli in giorni
            $array_days=array();
            for($i=0;$i<$numflows;$i++){
                switch($daycount){
                case 365:
                    $array_days[$i]=ry_datediff365($mindate, $dates[$i]);
                    break;
                case 360:
                    $array_days[$i]=ry_datediff360($mindate, $dates[$i]);
                    break;
                default:
                    $array_days[$i]=0;
                }
            }
            
            // set valori iniziali
            $xk=-0.000009;
            $xk1=0.0001;
            $fnk=0;
            $iterazioni=0;

            do{
                // Inizializzo i valori
                $x1=$xk;
                $xk=$xk1;
                $fn1=$fnk;
                $sum=0;
                  
                // Viene utilizzato il teorema delle contrazioni
                // calcolo del valore di f( x[k] ) e della differenza delta_f = f( x[k] ) - f( x[k-1] )
                // se delta_f = 0 la funzione ha derivata nulla e quindi x[k] = x[k-1]
                for($i=0;$i<$numflows;$i++){     // attualizzazione con xk come tasso
                    $fl=$flows[$i];
                    $days=$array_days[$i];
                    if($days>0){
                        if($xk>-1){
                            $sum+=$fl/pow(1+$xk, $days/$daycount);
                        }
                        else{
                            $ret=false;
                            break;
                        }
                    }
                    $fnk=$sum-$telquel;
                    if($fnk!=$fn1)
                        $xk1=$xk-$fnk*(($xk-$x1)/($fnk-$fn1));
                    else
                        $xk1=$xk;
                }
                $iterazioni+=1;
                if($xk1>10000){
                    $ret=false;
                }
                elseif($iterazioni>10000){
                    $ret=false;
                }
                if($ret==false)
                    break;
            } while(abs($xk-$xk1)>$psilon);
            
            if($ret){
                // Se la durata è inferiore all'anno (di 365 giorni) decapitalizzo il tasso ottenuto
                if($days<365 && $daycount==365 && $days>0 && $xk>-1){
                    $d=365/$days;
                    $xk=(pow(1+$xk,$d)-1)/$d;
                }
                $IRR=round($xk,7);
            }
            else{
                $IRR=0;
            }
        }
        else{
            $IRR=0;
        }
    }
    catch(Exception $e){
        $IRR=0;
        $ret=false;
    }
    return $ret;
}

?>