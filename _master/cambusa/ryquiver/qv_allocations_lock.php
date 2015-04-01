<?php 
/****************************************************************************
* Name:            qv_allocations_lock.php                                  *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverlck.php";
include_once "quiverinf.php";
function qv_allocations_lock($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $sets="";
        $where="";
        
        qv_solvealloc($maestro, $data, $SYSID, $TABLENAME, $RECORDID);
        
        if($SYSID==""){
            // STO PASSANDO TABLENAME+RECORDID PER UNA ALLOCAZIONE NUOVA
            // E NON PER PROLUNGARE LA SCADENZA DI UNA VECCHIA
            // DETERMINO UN NUOVO SYSID
            $SYSID=qv_createsysid($maestro);
            $oper=0;
        }
        else{
            $where="SYSID='$SYSID'";
            $oper=1;
        }
        
        // DETERMINO OWNERID E OWNERNAME
        qv_solveuser($maestro, $data, "OWNERID", "OWNEREGO", "OWNERNAME", $OWNERID, $OWNERNAME);
        if($OWNERID==""){
            $babelcode="QVERR_OWNERID";
            $b_params=array();
            $b_pattern="Utente non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINO AMOUNT
        if(isset($data["AMOUNT"])){
            $AMOUNT=floatval($data["AMOUNT"]);
            qv_appendcomma($sets,"AMOUNT=$AMOUNT");
        }
        else{
            $AMOUNT=0;
        }
            
        // DETERMINO DURATION
        if(isset($data["DURATION"]))
            $DURATION=strtoupper( ryqEscapize($data["DURATION"]) );
        else
            $DURATION="15M";
        
        $TIMEUNIT="M";
        if(strpos($DURATION, "H")!==false)
            $TIMEUNIT="H";
        elseif(strpos($DURATION, "S")!==false)
            $TIMEUNIT="S";
        
        $DURATION=intval($DURATION);
        $year=intval(date("Y"));
        $month=intval(date("m"));
        $day=intval(date("d"));
        $hour=intval(date("H"));
        $min=intval(date("i"));
        $sec=intval(date("s"));
        
        switch($TIMEUNIT){
            case "S": $ENDTIME=date("YmdHis", mktime($hour, $min, ($sec+$DURATION), $month, $day, $year) ); break;
            case "M": $ENDTIME=date("YmdHis", mktime($hour, ($min+$DURATION), $sec, $month, $day, $year) ); break;
            case "H": $ENDTIME=date("YmdHis", mktime(($hour+$DURATION), $min, $sec, $month, $day, $year) ); break;
            default:  $ENDTIME=date("YmdHis");
        }
        
        $BEGINTIME="[:NOW()]";
        $ENDTIME="[:TIME($ENDTIME)]";
        if($oper==1)
            qv_appendcomma($sets,"ENDTIME=$ENDTIME");
        
        // CONTROLLO CHE L'OGGETTO NON SIA GIA' STATO ALLOCATO
        $sql="SELECT QVUSERS.USERNAME AS USERNAME FROM QVALLOCATIONS INNER JOIN QVUSERS ON QVUSERS.SYSID=QVALLOCATIONS.OWNERID WHERE [:UPPER(TABLENAME)]='".strtoupper($TABLENAME)."' AND RECORDID='$RECORDID' AND ENDTIME>=[:NOW()]";
        maestro_query($maestro, $sql, $r);
        if(count($r)>0){
            $babelcode="QVERR_LOCKED";
            $LOCKNAME=$r[0]["USERNAME"];
            $b_params=array("USERNAME" => $LOCKNAME);
            $b_pattern="Record in uso da [{1}]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // PREDISPONGO COLONNE E VALORI DA REGISTRARE
        if($oper==0){
            // INSERIMENTO NUOVA ALLOCAZIONE
            $columns="SYSID,TABLENAME,RECORDID,OWNERID,AMOUNT,BEGINTIME,ENDTIME";
            $values="'$SYSID','$TABLENAME','$RECORDID','$OWNERID',$AMOUNT,$BEGINTIME,$ENDTIME";
            $sql="INSERT INTO QVALLOCATIONS($columns) VALUES($values)";
        }
        else{
            // PROLUNGAMENTO DELL'ALLOCAZIONE
            $sql="UPDATE QVALLOCATIONS SET $sets WHERE $where";
        }
        if(!maestro_execute($maestro, $sql, false)){
            $babelcode="QVERR_EXECUTE";
            $trace=debug_backtrace();
            $b_params=array("FUNCTION" => $trace[0]["function"] );
            $b_pattern=$maestro->errdescr;
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
    }
    catch(Exception $e){
        $success=0;
        $message=$e->getMessage();
    }
    // USCITA JSON
    $j=array();
    $j["success"]=$success;
    $j["code"]=$babelcode;
    $j["params"]=$babelparams;
    $j["message"]=$message;
    $j["SYSID"]=$SYSID;
    return $j; //ritorno standard
}
?>