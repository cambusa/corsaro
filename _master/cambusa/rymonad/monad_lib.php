<?php
/****************************************************************************
* Name:            monad_lib.php                                            *
* Project:         Cambusa/ryMonad                                          *
* Version:         1.00                                                     *
* Description:     Generator of system unique identifier (SYSID)            *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_execlib.php";

function monadcall($l, $b){ // Lunghezza parte univoca, numero dei blocchi random
    try{
        // INIZIALIZZO IL VALORE DI RITORNO
        $ret="";
        
        // APERTURA DATABASE
        $maestro=maestro_opendb("rymonad");

        if($maestro->conn!==false){
        
            // DETERMINO LA CHIAVE DEL RECORD OVE REPERIRE L'ULTIMO PROGRESSIVO
            $sysname="SYSID".($l-1);
            
            // BEGIN TRANSACTION
            maestro_begin($maestro);

            // DETERMINO L'ULTIMO PROGRESSIVO
            $sql="SELECT SYSLAST AS ULTIMO FROM LASTMONAD WHERE SYSLENGTH='$sysname'";
            maestro_query($maestro, $sql, $r);
            if(count($r)>0)
                $p=$r[0]["ULTIMO"];
            else
                $p="0";
                
            // COMPATIBILITÀ: TOLGO L'EVENTUALE CARATTERE P
            if(substr($p,0,1)=="P"){
                $p=substr($p,1);
            }
            
            // INCREMENTO IL PROGRESSIVO
            $p=bcadd($p, "1", 0);
            
            // ALLE VOLTE SUCCEDE CHE METTE IL PUNTO
            $dot=strpos($p, ".");
            if($dot!==false){
                $p=substr($p, 0, $dot);
            }
            
            // SCRIVO IL NUOVO PROGRESSIVO
            if($p=="1")
                $sql="INSERT INTO LASTMONAD (SYSLENGTH,SYSLAST) VALUES('$sysname', '1')";
            else
                $sql="UPDATE LASTMONAD SET SYSLAST='$p' WHERE SYSLENGTH='$sysname'";
            maestro_execute($maestro, $sql);

            // FORMATTO IL PROGRESSIVO
            $p=base_convert($p, 10, 36);	// CONVERTO IL PROGRESSIVO IN BASE 36
            $p=substr("0000000000000000000000000000000".$p,1-$l);	// FILLO CON ZERI
            $p=base_convert($l, 10, 36).$p;	// AGGIUNGO UN PREFISSO CON LA LUNGHEZZA DELLA PARTE UNIVOCA ESPRESSA IN BASE 36

            if($b>0){
                for($i=1;$i<=$b;$i++){
                    $p.=substr("0000".base_convert(intval(rand(0,1679615)), 10, 36),-4);
                }
            }
            $ret=strtoupper($p);	// TRASFORMO IN MAIUSCOLO
            
            // COMMIT TRANSACTION
            maestro_commit($maestro);
        
        }

        // CHIUSURA DATABASE
        maestro_closedb($maestro);
    }
    catch(Exception $e){

        // ROLLBACK TRANSACTION
        maestro_rollback($maestro);

    }
    return $ret;
}
function monadset($max){
    $ret=1;
    try{
        // APERTURA DATABASE
        $maestro=maestro_opendb("rymonad");

        if($maestro->conn!==false){

            // PRIMO CARATTERE LUNGHEZZA IN BASE 36 DELLA BASE UNIVOCA
            $serie=substr($max, 0, 1);

            // PROGRESSIVO DECIMALE
            $l=intval(base_convert($serie, 36, 10));

            // PRENDO SOLO LA BASE UNIVOCA
            $max=substr($max, 0, $l);

            // PROGRESSIVO IN BASE 36
            $progr36=substr($max, 1);

            // CHIAVE DEL RECORD
            $sysname="SYSID".($l-1);

            // BEGIN TRANSACTION
            maestro_begin($maestro);

            // DETERMINO L'ULTIMO PROGRESSIVO
            $sql="SELECT SYSLAST AS ULTIMO FROM LASTMONAD WHERE SYSLENGTH='$sysname'";
            maestro_query($maestro, $sql, $r);
            if(count($r)>0)
                $p=$r[0]["ULTIMO"];
            else
                $p="0";
            
            // FORMATTO IL PROGRESSIVO
            $p=base_convert($p, 10, 36);	// CONVERTO IL PROGRESSIVO IN BASE 36
            $p=substr("0000000000000000000000000000000".$p,1-$l);	// FILLO CON ZERI
            $p=strtoupper($serie.$p);
            
            // SE IL SYSID PASSATO E' PIU' GRANDE DI QUELLO REGISTRATO AGGIORNO LA TABELLA
            if($max>$p){
                $p=base_convert($progr36, 36, 10);
                $sql="UPDATE LASTMONAD SET SYSLAST='$p' WHERE SYSLENGTH='$sysname'";
                maestro_execute($maestro, $sql);
            }

            // COMMIT TRANSACTION
            maestro_commit($maestro);
        }
        // CHIUSURA DATABASE
        maestro_closedb($maestro);
    }
    catch(Exception $e){

        // ROLLBACK TRANSACTION
        maestro_rollback($maestro);

        $ret=0;
    }
    return $ret;
}
?>