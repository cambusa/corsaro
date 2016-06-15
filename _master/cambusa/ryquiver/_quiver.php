<?php 
/****************************************************************************
* Name:            _quiver.php                                              *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$path_cambusa=realpath(dirname(__FILE__)."/..");
$path_cambusa=str_replace("\\", "/", $path_cambusa);
$path_cambusa.="/";
include_once $path_cambusa."/sysconfig.php";
include_once $path_cambusa."ryquiver/quiverlib.php";

function quiver_execute($params){
    global $path_cambusa, $path_customize, $path_databases, $path_applications;
    global $maestro, $global_spacename;
    global $babelcode, $babelparams;
    global $global_lastenvname, $public_sessionid;
    global $global_progressid;
    global $VLAD, $BLOOD;
    try{
        // IMPOSTO UN TEMPO DI RISPOSTA ILLIMITATO
        set_time_limit(0);

        if(isset($params["sessionid"]))
            $sessionid=$params["sessionid"];
        else
            $sessionid="";

        if(isset($params["environ"]))
            $env=$params["environ"];
        else
            $env="";
            
        if(isset($params["progressid"]))
            $global_progressid=$params["progressid"];
        else
            $global_progressid="";

        if(isset($params["bulk"]))
            $bulk=($params["bulk"]!=false);
        else
            $bulk=false;
        
        if(isset($params["program"]))
            $statements=$params["program"];
        elseif(isset($params["function"]))
            $statements=$params["function"];
        else
            $statements="";
        
        if(isset($params["data"]))
            $bag=$params["data"];
        else
            $bag=array();
        
        if(isset($params["spacename"]))
            $spacename=$params["spacename"];
        else
            $spacename="";
        
        if(isset($params["return"]))
            $rtype=$params["return"];
        else
            $rtype=1;
        
        if($rtype==2){
            // CARICO LA LIBRERIA XML
            include_once "quiverxml.php";
        }

        // APERTURA FILE DI LOG
        log_open(log_unique("maestro"));
    
        // APRO IL DATABASE
        $maestro=maestro_opendb($env, false);

        // VERIFICO IL BUON ESITO DELL'APERTURA
        if($maestro->conn!==false){
        
            // VALIDAZIONE CODICE DI SESSIONE
            if(qv_validatesession($maestro, $sessionid, "quiver")){
            
                // VALIDO L'AMBIENTE
                if( $env==$global_lastenvname || ($sessionid==$public_sessionid && $public_sessionid!="") ){
            
                    // BEGIN TRANSACTION
                    maestro_begin($maestro);
                    
                    if(is_string($statements)){
                        $program=array();
                        $program[]=array( "function" => $statements, "space" => $spacename, "fallible" => 0, "data" => $bag, "pipe" => array(), "return" => array() );
                    }
                    else{
                        $program=&$statements;
                        // RISOLUZIONE DI EVENTUALI MACRO
                        for($index=count($program)-1; $index>=0; $index--){
                            $stat=$program[$index];
                            if(isset($stat["macro"])){
                                $macro=$stat["macro"];
                                $macro=str_replace("@customize/", $path_customize, $macro);
                                $macro=str_replace("@cambusa/", $path_cambusa, $macro);
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
                            if(!isset($stat["space"]))
                                $program[$index]["space"]="";
                            if(!isset($stat["fallible"]))
                                $program[$index]["fallible"]=0;
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
                        $global_spacename=$stat["space"];
                        if($global_spacename!=""){
                            if(substr($global_spacename, -1)!="/"){
                                $global_spacename.="/";
                            }
                        }
                        $fallible=intval($stat["fallible"]);
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
                        
                        // AGGIUNGO IL PREFISSO
                        $function="qv_" . $function;
                        // LANCIO LA FUNZIONE RICHIESTA (LE FUNZIONI DI SISTEMA NON SONO SOVRASCRIVIBILI)
                        $include=$path_cambusa . "ryquiver/" . $global_spacename . $function . ".php";
                        // DO LA PRECEDENZA ALLA FUNZIONE CUSTOM RISPETTO A QUELLA APPLICATIVA
                        $custinclude=$path_customize . "ryquiver/" . $global_spacename . $function . ".php";
                        $appinclude=$path_applications . "ryquiver/" . $global_spacename . $function . ".php";

                        if(is_file($include)){
                            include_once $include;
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
                        elseif(is_file($appinclude)){
                            include_once $appinclude;
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
                        // GESTIONE ESITO NEGATIVO
                        if($jret["success"]==0){
                            if($fallible){
                                $jret["success"]=2;
                            }
                            else{
                                $jret["statement"]=$function;
                                $jret["step"]=$index+1;
                                break;
                            }
                        }
                        // GESTIONE WARNING
                        if($jret["success"]==2){
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
                    // COMPILO L'USCITA
                    switch($rtype){
                        case 1:
                            array_walk_recursive($jret, "quiver_escapize");
                            $json=json_encode($jret);
                            break;
                        case 2:
                            $json=_qv_savexml($jret);
                            break;
                        default:
                            $json=$jret;
                    }
                }
                else{
                    $jret=array();
                    $jret["success"]=0;
                    $jret["code"]=$babelcode;
                    $jret["params"]=$babelparams;
                    $jret["message"]="Permission denied";
                    $jret["SYSID"]="";
                    $jret["infos"]=array();
                    // COMPILO L'USCITA
                    switch($rtype){
                        case 1:
                            array_walk_recursive($jret, "quiver_escapize");
                            $json=json_encode($jret);
                            break;
                        case 2:
                            $json=_qv_savexml($jret);
                            break;
                        default:
                            $json=$jret;
                    }
                }
            }
            else{
                $jret=array();
                $jret["success"]=0;
                $jret["code"]=$babelcode;
                $jret["params"]=$babelparams;
                $jret["message"]="Invalid sessionid";
                $jret["SYSID"]="";
                $jret["infos"]=array();
                // COMPILO L'USCITA
                switch($rtype){
                    case 1:
                        array_walk_recursive($jret, "quiver_escapize");
                        $json=json_encode($jret);
                        break;
                    case 2:
                        $json=_qv_savexml($jret);
                        break;
                    default:
                        $json=$jret;
                }
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
            // COMPILO L'USCITA
            switch($rtype){
                case 1:
                    array_walk_recursive($jret, "quiver_escapize");
                    $json=json_encode($jret);
                    break;
                case 2:
                    $json=_qv_savexml($jret);
                    break;
                default:
                    $json=$jret;
            }
        }
        // CHIUDO IL DATABASE
        maestro_closedb($maestro);

        // CHIUSURA FILE DI LOG
        log_close();

        // CANCELLAZIONE FILE CON I MESSAGGI DI AVANZAMENTO
        _qv_clearprogress();

      // RESTITUISCO IL RISULTATO
        return $json;
    }
    catch(Exception $e){
        if(isset($maestro)){
            // ROLLBACK TRANSACTION
            @maestro_rollback($maestro);

            // CHIUDO IL DATABASE
            @maestro_closedb($maestro);
        }

        // CHIUSURA FILE DI LOG
        log_close();

        // CANCELLAZIONE FILE CON I MESSAGGI DI AVANZAMENTO
        _qv_clearprogress();

        $jret=array();
        $jret["success"]=0;
        $jret["code"]=$babelcode;
        $jret["params"]=$babelparams;
        $jret["message"]=$e->getMessage();
        $jret["SYSID"]="";
        $jret["infos"]=array();
        // COMPILO L'USCITA
        switch($rtype){
            case 1:
                array_walk_recursive($jret, "quiver_escapize");
                return json_encode($jret);
            case 2:
                return _qv_savexml($jret);
            default:
                return $jret;
        }
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
function quiver_escapize(&$value){
    if($value!=""){
        if(!mb_check_encoding($value, "UTF-8")){
            // CI SONO CARATTERI NON UNICODE
            $value=utf8_encode($value);
        }
    }
}
?>