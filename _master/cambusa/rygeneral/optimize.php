<?php
/****************************************************************************
* Name:            optimize.php                                             *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2012-2013  Rodolfo Calzetti                              *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

/* ESEMPIO D'USO ************************************************************

// Coefficienti della funzione di valutazione
$v=array();
$v[0]=-3;
$v[1]=-2;

// Coefficienti dei vincoli
$A=array();
$A[0]=array();
$A[0][0]=2;
$A[0][1]=1;
$A[1]=array();
$A[1][0]=2;
$A[1][1]=3;
$A[2]=array();
$A[2][0]=3;
$A[2][1]=1;

// Limiti (<=)
$b=array();
$b[0]=18;
$b[1]=42;
$b[2]=24;

optimize($v, $b, $A, $x, $o);

print "<br>Valore ottimo:".$o."<br>";
print_r($x);
****************************************************************************/

function optimize($v, $b, $A, &$x, &$opt){
    // BOOLEANO DI RITORNO
    $ret=true;
    // DETERMINO LE DIMENSIONI DEL PROBLEMA
    $m=count($b);   // Vincoli
    $n=count($v);   // Variabili
    // INIZIALIZZO L'USCITA
    $opt=false;
    $x=array();
    for($i=0;$i<$n;$i++)
        $x[$i]=0;
    // CARICAMENTO VETTORE BASE
    $base=array();
    $base[0]=0;
    for($i=1;$i<=$m;$i++)
        $base[$i]=$n+$i;
    // INIZIALIZZAZIONE MATRICE
    // 0 | v | 0
    // b | A | I
    $p=array();
    // CARICAMENTO MATRICE CON 0 (futuro valore ottimo)
    $p[0][0]=0;
    // CARICAMENTO MATRICE CON $v
    for($j=1;$j<=$n;$j++)
        $p[0][$j]=$v[$j-1];
    // CARICAMENTO MATRICE CON 0
    for($j=1;$j<=$m;$j++)
        $p[0][$j+$n]=0;
    // CARICAMENTO MATRICE CON $b
    for($i=1;$i<=$m;$i++)
        $p[$i][0]=$b[$i-1];
    // CARICAMENTO MATRICE CON $A
    for($i=1;$i<=$m;$i++){
        for($j=1;$j<=$n;$j++)
            $p[$i][$j]=$A[$i-1][$j-1];
    }
    // CARICAMENTO MATRICE CON I (varibili slack)
    for($i=1;$i<=$m;$i++){
        for($j=1;$j<=$m;$j++)
            $p[$i][$j+$n]=( $j==$i ? 1 : 0 );
    }
    while( opt_checkcontinue($p[0]) ){
        // CERCO LA COLONNA PIVOT
        $min=$p[0][0];
        $pivotvcol=0;
        for($i=1;$i<=$n+$m;$i++){
            if($p[0][$i]<$min){
                $min=$p[0][$i];
                $pivotvcol=$i;
            }
        }
        // CERCO LA RIGA PIVOT
        $min=0;
        $pivotvrow=0;
        for($i=1;$i<=$m;$i++){
            $test=$p[$i][0]/$p[$i][$pivotvcol];
            if(0<$test && ($test<$min || $min==0) ){
                $min=$test;
                $pivotvrow=$i;
            }
        }
        if($pivotvrow==0){
            $ret=false;
            break;
        }
        // DETERMINO IL PIVOT
        $pivot=$p[$pivotvrow][$pivotvcol];
        
        // NUOVA RIGA PIVOT
        for($j=0;$j<=$n+$m;$j++){
            $p[$pivotvrow][$j]/=$pivot;
        }
        // LE ALTRE RIGHE
        $zero=true;
        for($i=0;$i<=$m;$i++){
            if($i!=$pivotvrow){
                $coeff=$p[$i][$pivotvcol];
                if($coeff!=0)
                    $zero=false;
                for($j=0;$j<=$n+$m;$j++){
                    $p[$i][$j]-=$coeff*$p[$pivotvrow][$j];
                }
            }
        }
        if($zero){
            $ret=false;
            break;
        }
        $base[$pivotvrow]=$pivotvcol;
    }
    if($ret){
        $opt=$p[0][0];
        for($i=1;$i<=$m;$i++){
            if($base[$i]<=$n){
                $x[ $base[$i]-1 ]=$p[$i][0];
            }
        }
    }
    return $ret;
}

function opt_checkcontinue($v){
    $ret=false;
    for($j=1;$j<count($v);$j++){
        if($v[$j]<0){
            $ret=true;
            break;
        }
    }
    return $ret;
}
?>