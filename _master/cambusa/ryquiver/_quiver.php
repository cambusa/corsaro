<?php 
/****************************************************************************
* Name:            _quiver.php                                              *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."ryquiver/quiverlib.php";

function quiver_execute($sessionid, $env, $bulk, $statements, $bag=array()){
    global $tocambusa, $path_cambusa, $path_customize, $path_databases, $path_applications;
    global $maestro;
    global $babelcode, $babelparams;
    try{
        set_time_limit(0);

        // APERTURA FILE DI LOG
        log_open(log_unique("maestro"));
    
        // APRO IL DATABASE
        $maestro=maestro_opendb($env, false);

        // VERIFICO IL BUON ESITO DELL'APERTURA
        if($maestro->conn!==false){
        
            // VALIDAZIONE CODICE DI SESSIONE
            if(qv_validatesession($maestro, $sessionid, "quiver")){
            
                // BEGIN TRANSACTION
                maestro_begin($maestro);
                
                if(is_string($statements)){
                    $program=array();
                    $program[]=array( "function" => $statements, "data" => $bag, "pipe" => array(), "return" => array() );
                }
                else{
                    $program=&$statements;
                    // RISOLUZIONE DI EVENTUALI MACRO
                    for($index=count($program)-1; $index>=0; $index--){
                        $stat=$program[$index];
                        if(isset($stat["macro"])){
                            $macro=$stat["macro"];
                            $macro=str_replace("@customize/", $path_customize, $macro);
                            $macro=str_replace("@cambusa/", $tocambusa, $macro);
                            $macro=str_replace("@databases/", $path_databases, $macro);
                            $macro=str_replace("@apps/", $path_applications, $macro);
                            if(file_exists($macro)){
                                if(isset($stat["data"]))
                                    $data=$stat["data"];
                                else
                                    $data=array();
                                $buffer=file_get_contents($macro);
                                foreach($data as $n => $v){
                                    $buffer=str_replace("[!$n]", $v, $buffer);
                                }
                                if(preg_match("/\[![0-9A-Z]+\]/i" , $buffer, $m)==1){
                                    $d=$m[0];
                                    $babelcode="QVERR_MACRONODATA";
                                    throw new Exception( "Macro [$macro]: dato $d mancante" );
                                }
                                $arraymacro=jsonObjectToArray(json_decode($buffer));
                                array_walk_recursive($arraymacro, "_solveUTF8");
                            }
                            else{
                                $babelcode="QVERR_NOMACRO";
                                throw new Exception( "Macro [$macro] non trovata]" );
                            }
                            array_splice ($program, $index, 1, $arraymacro);
                        }
                    }
                    // COMPLETAMENTO DELLE ISTRUZIONI
                    foreach($program as $index => $stat){
                        if(!isset($stat["function"]))
                            $program[$index]["function"]="";
                        if(!isset($stat["data"]))
                            $program[$index]["data"]=array();
                        if(!isset($stat["pipe"]))
                            $program[$index]["pipe"]=array();
                        if(!isset($stat["return"]))
                            $program[$index]["return"]=array();
                    }
                }

                // GESTISCO IL PARAMETRO _JOURNALLOG
                if(qv_setting($maestro, "_JOURNALLOG", 0)==1){
                    $buff=$sessionid . "|" . serialize($program);
                    qv_journal($env, $buff);
                }
            
                // SE IL PROGRAMMA SCRIVE MOLTISSIME RIGHE
                // CONVIENE ALZARE IL FLAG BULK PER GENERARE I SYSID
                // SU UNA BASE UNIVOCA PRIVATA
                if($bulk){
                    qv_bulkinitialize($maestro);
                }
                    
                // PREDISPONGO UNA PIPE VUOTA PER IL PRIMO GIRO
                $pipe=array();
                // PREDISPONGO UN CONTENITORE VUOTO PER I DATI DI RITORNO RICHIESTI
                $retbag=array();
                // VARIABILE CHE MI MEMORIZZA UN WARNING
                $warning="";
                foreach($program as $index => $stat){
                    $function=$stat["function"];
                    $data=$stat["data"];

                    // TRAVASO I DATI DEFINITI IN "PIPE" 
                    // DAL RITORNO DELL'ISTRUZIONE PRECEDENTE
                    // ALL'ENTRATA DELL'ISTRUZIONE CORRENTE
                    foreach($pipe as $n => $v){
                        try{
                            if(substr($v,0,1)=="#")
                                $data[$n]=$jret["params"][substr($v,1)];
                            elseif(substr($v,0,1)=="@")
                                $data[$n]=$retbag[substr($v,1)];
                            else
                                $data[$n]=$jret[$v];
                        }
                        catch(Exception $e){}
                    }

                    // INIZIALIZZO LE VARIABILI DI RITORNO
                    unset($jret);
                    unset($pipe);
                    $pipe=$stat["pipe"];
                    $return=$stat["return"];
                    
                    // LANCIO LA FUNZIONE RICHIESTA (LE FUNZIONI DI SISTEMA NON SONO SOVRASCRIVIBILI)
                    $include=$tocambusa . "ryquiver/" . "qv_" . $function . ".php";
                    // DO LA PRECEDENZA ALLA FUNZIONE CUSTOM RISPETTO A QUELLA APPLICATIVA
                    $custinclude=$path_customize . "ryquiver/qv_" . $function . ".php";
                    $appinclude=$path_applications . "ryquiver/qv_" . $function . ".php";
                    $function="qv_" . $function;

                    if(is_file($include)){
                        include_once $include;
                        if(function_exists($function)){
                            $jret=$function($maestro, $data);
                        }
                    }
                    elseif(is_file($appinclude)){
                        include_once $appinclude;
                        if(function_exists($function)){
                            $jret=$function($maestro, $data);
                        }
                    }
                    elseif(is_file($custinclude)){
                        include_once $custinclude;
                        if(function_exists($function)){
                            $jret=$function($maestro, $data);
                        }
                    }
                    if(!isset($jret)){
                        // LA FUNZIONE NON E' STATA TROVATA
                        $jret=array();
                        $jret["success"]=0;
                        $jret["code"]="QVERR_NOP";
                        $jret["params"]=$babelparams;
                        $jret["message"]="No operation";
                        $jret["SYSID"]="";
                    }
                    if($jret["success"]==0){
                        $jret["statement"]=$function;
                        $jret["step"]=$index+1;
                        break;
                    }
                    elseif($jret["success"]==2){
                        $warning=$jret["message"];
                    }
                    // TRAVASO I DATI DEFINITI IN "RETURN" 
                    // DAL RITORNO DELL'ISTRUZIONE PRECEDENTE
                    // AI DATI GLOBALI IN USCITA DAL PROGRAMMA
                    foreach($return as $n => $v){
                        try{
                            if(substr($v,0,1)=="#")
                                $retbag[$n]=$jret["params"][substr($v,1)];
                            elseif(substr($v,0,1)=="@")
                                $retbag[$n]=$retbag[substr($v,1)];
                            else
                                $retbag[$n]=$jret[$v];
                        }
                        catch(Exception $e){}
                    }
                    unset($return);
                    if($index>10){
                        // INVIO DI SPAZI PER MANTENERE VIVA LA CONNESSIONE
                        print str_repeat(" ",1000);
                        flush();
                    }
                }
                if(!isset($jret)){
                    // NESSUNA FUNZIONE NON E' STATA TROVATA
                    $jret=array();
                    $jret["success"]=0;
                    $jret["code"]="QVERR_NOP";
                    $jret["params"]=$babelparams;
                    $jret["message"]="No operation";
                    $jret["SYSID"]="";
                    $jret["statement"]="";
                    $jret["step"]=0;
                }
                if($jret["success"]){
                    // COMMIT TRANSACTION
                    maestro_commit($maestro);
                }
                else{
                    // ROLLBACK TRANSACTION
                    maestro_rollback($maestro);
                }
                // TRAVASO NEL DOCUMENTO DI RITORNO I DATI RICHIESTI IN RETURN
                $jret["infos"]=$retbag;
                // GESTISCO IL WARNING
                if($warning!="" && $jret["success"]==1){
                    $jret["success"]=2;
                    $jret["message"]=$warning;
                }
                // ESCAPIZZO I CARATTERI NON STANDARD
                array_walk_recursive($jret, "quiver_escapize");
                // COMPILO L'USCITA JSON
                $json=json_encode($jret);
            }
            else{
                $jret=array();
                $jret["success"]=0;
                $jret["code"]=$babelcode;
                $jret["params"]=$babelparams;
                $jret["message"]="Invalid sessionid";
                $jret["SYSID"]="";
                $jret["infos"]=array();
                array_walk_recursive($jret, "quiver_escapize");
                $json=json_encode($jret);
            }
        }
        else{
            $jret=array();
            $jret["success"]=0;
            $jret["code"]=$babelcode;
            $jret["params"]=$babelparams;
            $jret["message"]=$maestro->errdescr;
            $jret["SYSID"]="";
            $jret["infos"]=array();
            array_walk_recursive($jret, "quiver_escapize");
            $json=json_encode($jret);
        }
        // CHIUDO IL DATABASE
        maestro_closedb($maestro);

        // CHIUSURA FILE DI LOG
        log_close();

        // RESTITUISCO IL RISULTATO
        return $json;
    }
    catch(Exception $e){
        if(isset($maestro)){
            // ROLLBACK TRANSACTION
            maestro_rollback($maestro);
        }
        $jret=array();
        $jret["success"]=0;
        $jret["code"]=$babelcode;
        $jret["params"]=$babelparams;
        $jret["message"]=$e->getMessage();
        $jret["SYSID"]="";
        $jret["infos"]=array();
        array_walk_recursive($jret, "quiver_escapize");
        return json_encode($jret) ;
    }
}
function _solveUTF8(&$value){
    global $maestro;
    if($value=="[:TODAY()]"){
        $value=date("Ymd");
    }
    elseif($value=="[:TIME()]"){
        $value=date("YmdHis");
    }
    elseif($value=="[:SYSID]"){
        $value=qv_createsysid($maestro);
    }
    elseif(substr($value,0,8)=="[:SYSID(" && substr($value,-2,2)==")]"){
        $value=substr($value,8,strlen($value)-10);
        $value=substr($value . str_repeat("0", $maestro->lenid), 0, $maestro->lenid);
    }
    else{
        $value=utf8_encode(html_entity_decode($value));
    }
}
function quiver_escapize(&$sql){
    $sql=htmlentities($sql);
}
?>