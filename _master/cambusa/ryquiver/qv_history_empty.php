<?php 
/****************************************************************************
* Name:            qv_history_empty.php                                     *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "../rymaestro/maestro_querylib.php";
function qv_history_empty($maestro, $data){
    global $babelcode, $babelparams;
    global $global_lastadmin;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // GESTIONE AMMINISTRATORE
        if($global_lastadmin==0){
            $babelcode="QVERR_FORBIDDEN";
            $b_params=array();
            $b_pattern="Autorizzazioni insufficienti";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // CANCELLAZIONE MASSIVA PER... 
        
        // OPERAZIONE... 
        if(isset($data["OPERTYPE"])){
            $OPERTYPE=intval($data["OPERTYPE"]);
            $CLAUSEOPER=" OPERTYPE=$OPERTYPE AND ";
        }
        else{
            $CLAUSEOPER="";
        }

        // ... TABLEBASE... 
        if(isset($data["TABLEBASE"])){
            $TABLEBASE=ryqEscapize($data["TABLEBASE"]);
            $CLAUSEBASE=" TABLEBASE='$TABLEBASE' AND ";
        }
        else{
            $CLAUSEBASE="";
        }

        // ... TIPOLOGIA... 
        if(isset($data["TYPOLOGYID"])){
            $TYPOLOGYID=ryqEscapize($data["TYPOLOGYID"]);
            $CLAUSETYPOLOGY=" TYPOLOGYID='$TYPOLOGYID' AND ";
        }
        else{
            $CLAUSETYPOLOGY="";
        }
            
        // ... E DATA STORICIZZAZIONE
        if(isset($data["EVENTTIME"])){
            $EVENTTIME=qv_escapizetime($data["EVENTTIME"], HIGHEST_TIME);
        }
        else{
            $year=intval(date("Y"));
            $month=intval(date("m"));
            $day=intval(date("d"));
            $EVENTTIME=date("YmdHis", mktime(0, 0, 0, $month-1, $day, $year));
        }

        // ... DATA INSERIMENTO/MODIFICA...
        if(isset($data["RECORDTIME"]))
            $RECORDTIME=qv_escapizetime($data["RECORDTIME"], HIGHEST_TIME);
        else
            $RECORDTIME=$EVENTTIME;

        // CANCELLAZIONE A BLOCCHI DI TRANSAZIONI
        $LASTID="";
        $LIMIT=100;
        do{
            $sql="SELECT {AS:TOP $LIMIT} SYSID FROM QVHISTORY WHERE SYSID>'$LASTID' AND $CLAUSEOPER $CLAUSEBASE $CLAUSETYPOLOGY RECORDTIME<=[:TIME($RECORDTIME)] AND EVENTTIME<=[:TIME($EVENTTIME)] {O: AND ROWNUM=$LIMIT} ORDER BY SYSID {LM:LIMIT $LIMIT}{D:FETCH FIRST $LIMIT ROWS ONLY}";
            maestro_query($maestro, $sql, $b);
            $cnt=count($b);
            for($i=0;$i<$cnt;$i++){
                $LASTID=$b[$i]["SYSID"];
                $sql="DELETE FROM QVHISTORY WHERE SYSID='$LASTID'";
                if(!maestro_execute($maestro, $sql, false)){
                    $babelcode="QVERR_EXECUTE";
                    $trace=debug_backtrace();
                    $b_params=array("FUNCTION" => $trace[0]["function"] );
                    $b_pattern=$maestro->errdescr;
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
            // COMMIT TRANSACTION
            maestro_commit($maestro);
            
            // BEGIN TRANSACTION
            maestro_begin($maestro);

        }while($cnt==$LIMIT);
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