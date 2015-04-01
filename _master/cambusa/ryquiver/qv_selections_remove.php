<?php 
/****************************************************************************
* Name:            qv_selections_remove.php                                 *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_selections_remove($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        $where="";
        
        // DETERMINO ELENCO SYSID
        if(isset($data["KEYS"])){
            $KEYS=$data["KEYS"];
            if(!is_array($KEYS)){
                if($KEYS!="")
                    $KEYS=explode("|", $KEYS);
                else
                    $KEYS=array();
            }
            if(count($KEYS)>0){
                $where="SYSID IN ('" . implode("','", $KEYS) . "')";
            }
        }
        else{
            // DETERMINO PARENTID
            if(isset($data["PARENTID"])){
                $PARENTID=ryqEscapize($data["PARENTID"]);
            }
            else{
                $babelcode="QVERR_PARENTID";
                $b_params=array();
                $b_pattern="Riferimento non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            // DETERMINO I SELEZIONATI DA TOGLIERE
            if(isset($data["SELECTION"])){
                $SELECTION=$data["SELECTION"];
                if(!is_array($SELECTION)){
                    if($SELECTION!="")
                        $SELECTION=explode("|", $SELECTION);
                    else
                        $SELECTION=array();
                }
                if(count($SELECTION)>0){
                    $where="PARENTID='$PARENTID' AND SELECTEDID IN ('" . implode("','", $SELECTION) . "')";
                }
            }
            else{
                // LI TOLGO TUTTI
                $where="PARENTID='$PARENTID'";
            }
        }
        if($where!=""){
            $sql="DELETE FROM QVSELECTIONS WHERE $where";
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