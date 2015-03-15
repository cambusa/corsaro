<?php 
/****************************************************************************
* Name:            qv_selections_copy.php                                   *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_selections_copy($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        $where="";
        
        // DETERMINO PARENTID
        if(isset($data["PARENTID"])){
            $PARENTID=ryqEscapize($data["PARENTID"]);
        }
        else{
            $babelcode="QVERR_PARENTID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO TARGETID
        if(isset($data["TARGETID"])){
            $TARGETID=ryqEscapize($data["TARGETID"]);
        }
        else{
            $babelcode="QVERR_TARGETID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il bersaglio";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO SELECTEDID
        if(isset($data["SELECTEDID"]))
            $SELECTEDID=$data["SELECTEDID"];
        else
            $SELECTEDID=false;
        
        // SCANDISCO LE SELEZIONI
        maestro_query($maestro,"SELECT * FROM QVSELECTIONS WHERE PARENTID='$PARENTID'", $r);
        for($i=0; $i<count($r); $i++){
            $T_SYSID=qv_createsysid($maestro);
            $T_PARENTTABLE=$r[$i]["PARENTTABLE"];
            $T_PARENTFIELD=$r[$i]["PARENTFIELD"];
            $T_PARENTID=$TARGETID;
            $T_SELECTEDTABLE=$r[$i]["SELECTEDTABLE"];
            $SELID=$r[$i]["SELECTEDID"];
            if($SELECTEDID){
                if(isset($SELECTEDID[ $SELID ])){
                    $T_SELECTEDID=$SELECTEDID[ $SELID ];
                    // CONTROLLO DI ESISTENZA DEL NUOVO SELEZIONATO
                    maestro_query($maestro,"SELECT SYSID FROM $T_SELECTEDTABLE WHERE SYSID='$T_SELECTEDID'", $s);
                    if(count($s)==0){
                        $babelcode="QVERR_SELECTEDNOTFOUND";
                        $b_params=array("SELECTEDTABLE" => $T_SELECTEDTABLE, "SELECTEDID" => $T_SELECTEDID);
                        $b_pattern="Elemento selezionato [{2}] non trovato nella tabella [{1}]";
                        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                    }
                }
                else{
                    $babelcode="QVERR_SELECTEDNOTSET";
                    $b_params=array("SELECTEDID" => $SELID);
                    $b_pattern="Elemento selezionato [{1}] senza corrispondente";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
            else{
                $T_SELECTEDID=$SELID;
            }
            $T_ENABLED=$r[$i]["ENABLED"];
            $T_FLAG1=$r[$i]["FLAG1"];
            $T_FLAG2=$r[$i]["FLAG2"];
            $T_FLAG3=$r[$i]["FLAG3"];
            $T_FLAG4=$r[$i]["FLAG4"];
            $T_SORTER=$r[$i]["SORTER"];
            $T_DISTANCE=$r[$i]["DISTANCE"];
            $columns="SYSID,PARENTTABLE,PARENTFIELD,PARENTID,SELECTEDTABLE,SELECTEDID,ENABLED,FLAG1,FLAG2,FLAG3,FLAG4,SORTER,DISTANCE";
            $values="'$T_SYSID','$T_PARENTTABLE','$T_PARENTFIELD','$T_PARENTID','$T_SELECTEDTABLE','$T_SELECTEDID',$T_ENABLED,$T_FLAG1,$T_FLAG2,$T_FLAG3,$T_FLAG4,$T_SORTER,$T_DISTANCE";
            $sql="INSERT INTO QVSELECTIONS($columns) VALUES($values)";
            if(!maestro_execute($maestro, $sql, false)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"] );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
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