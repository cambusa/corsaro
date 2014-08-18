<?php 
/****************************************************************************
* Name:            qv_selections_arrange.php                                *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_selections_arrange($maestro, $data){
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
        // DETERMINO SELECTEDID
        if(isset($data["SELECTEDID"])){
            $SELECTEDID=ryqEscapize($data["SELECTEDID"]);
        }
        else{
            $babelcode="QVERR_SELECTEDID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        // RISOLVO IL SYSID DELLA SELEZIONE RELATIVO A SELECTEDID
        maestro_query($maestro,"SELECT SYSID FROM QVSELECTIONS WHERE PARENTID='$PARENTID' AND SELECTEDID='$SELECTEDID'", $r);
        if(count($r)==1){
            $SYSID=$r[0]["SYSID"];
        }
        else{
            $babelcode="QVERR_NOSELECTEDID";
            $b_params=array();
            $b_pattern="Record non incluso nella selezione";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO POSITION (FIRST LAST BACK FORWARD)
        if(isset($data["POSITION"])){
            $POSITION=strtoupper(ryqEscapize($data["POSITION"]));
            if(!in_array($POSITION, array("FIRST", "LAST", "BACK", "FORWARD"))){
                $babelcode="QVERR_POSITION";
                $b_params=array("POSITION" => $POSITION);
                $b_pattern="Valore [{1}] non previsto per posizione";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $POSITION="FIRST";
        }
        
        // DETERMINO I SELEZIONATI
        $ids=array();
        $selection=array();
        maestro_query($maestro,"SELECT SYSID FROM QVSELECTIONS WHERE PARENTID='$PARENTID' ORDER BY SORTER",$r);
        for($i=0; $i<count($r); $i++){
            $ids[$i]=$r[$i]["SYSID"];
        }
        $arrange=array();
        switch($POSITION){
        case "FIRST":
            $arrange[]=$SYSID;
            for($i=0; $i<count($ids); $i++){
                if($ids[$i]!=$SYSID){
                    $arrange[]=$ids[$i];
                }
            }
            break;
        case "LAST":
            for($i=0; $i<count($ids); $i++){
                if($ids[$i]!=$SYSID){
                    $arrange[]=$ids[$i];
                }
            }
            $arrange[]=$SYSID;
            break;
        case "BACK":
            if($ids[0]!=$SYSID){    // Il record non è al primo posto
                for($i=0; $i<count($ids); $i++){
                    if($i<count($ids)-1){
                        if($ids[$i+1]==$SYSID){
                            $arrange[]=$SYSID;
                        }
                    }
                    if($ids[$i]!=$SYSID){
                        $arrange[]=$ids[$i];
                    }
                }
            }
            break;
        case "FORWARD":
            if($ids[count($ids)-1]!=$SYSID){    // Il record non è all'ultimo posto
                for($i=0; $i<count($ids); $i++){
                    if($ids[$i]!=$SYSID){
                        $arrange[]=$ids[$i];
                    }
                    if($i>0){
                        if($ids[$i-1]==$SYSID){
                            $arrange[]=$SYSID;
                        }
                    }
                }
            }
            break;
        }
        // RISCRIVO LA SELEZIONE RIORDINATA
        for($ord=0; $ord<count($arrange); $ord++){
            $SELID=$arrange[$ord];
            $sql="UPDATE QVSELECTIONS SET SORTER=$ord WHERE SYSID='$SELID'";
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