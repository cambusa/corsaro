<?php 
/****************************************************************************
* Name:            qv_selections_add.php                                    *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_selections_add($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
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

        // DETERMINO PARENTTABLE
        if(isset($data["PARENTTABLE"])){
            $PARENTTABLE=ryqEscapize($data["PARENTTABLE"]);
        }
        else{
            $babelcode="QVERR_PARENTTABLE";
            $b_params=array();
            $b_pattern="Tabella di riferimento non specificata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO PARENTFIELD
        if(isset($data["PARENTFIELD"]))
            $PARENTFIELD=ryqEscapize($data["PARENTFIELD"]);
        else
            $PARENTFIELD="SYSID";
            
        // CONTROLLO DI ESISTENZA
        maestro_query($maestro,"SELECT SYSID FROM $PARENTTABLE WHERE $PARENTFIELD='$PARENTID'",$r);
        if(count($r)==0){
            $babelcode="QVERR_NOSELPARENT";
            $b_params=array("PARENTTABLE" => $PARENTTABLE, "PARENTFIELD" => $PARENTFIELD, "PARENTID" => $PARENTID);
            $b_pattern="Terna tabella [{1}], campo [{2}], valore [{3}] non trovata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINO I FLAG (COMUNI A TUTTI I SELEZIONATI)
        $ENABLED=1;
        $UPWARD=0;
        $DISTANCE=1;
        $FLAG1=0;
        $FLAG2=0;
        $FLAG3=0;
        $FLAG4=0;
        if(isset($data["ENABLED"])){
            if(intval($data["ENABLED"])==0){
                $ENABLED=0;
            }
        }
        if(isset($data["UPWARD"])){
            if(intval($data["UPWARD"])!=0){
                $UPWARD=1;
            }
        }
        if(isset($data["DISTANCE"])){
            $DISTANCE=floatval($data["DISTANCE"]);
        }
        if(isset($data["FLAG1"])){
            if(intval($data["FLAG1"])){
                $FLAG1=1;
            }
        }
        if(isset($data["FLAG2"])){
            if(intval($data["FLAG2"])){
                $FLAG2=1;
            }
        }
        if(isset($data["FLAG3"])){
            if(intval($data["FLAG3"])){
                $FLAG3=1;
            }
        }
        if(isset($data["FLAG4"])){
            if(intval($data["FLAG4"])){
                $FLAG4=1;
            }
        }
        
        // DETERMINO SELECTEDTABLE
        if(isset($data["SELECTEDTABLE"])){
            $SELECTEDTABLE=ryqEscapize($data["SELECTEDTABLE"]);
            if(strpos("QVOBJECTS|QVQUIVERS|QVMOTIVES|QVARROWS|QVGENRES", $SELECTEDTABLE)===false){
                $babelcode="QVERR_NOSELECTEDTABLE";
                $b_params=array();
                $b_pattern="Tabella di selezione non corretta";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $babelcode="QVERR_SELECTEDTABLE";
            $b_params=array();
            $b_pattern="Tabella di selezione non specificata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        if(isset($data["SELECTION"])){
            $SELECTION=$data["SELECTION"];
            if(!is_array($SELECTION)){
                if($SELECTION!="")
                    $SELECTION=explode("|", $SELECTION);
                else
                    $SELECTION=array();
            }
            if(count($SELECTION)>0){
                $SELASS=array();
                for($i=0; $i<count($SELECTION); $i++){
                    $SELASS[ ryqEscapize( $SELECTION[$i] ) ]=true;
                }
                $in="'" . implode("','", $SELECTION) . "'";
                // DETERMINO I SYSID EFFETTIVAMENTE DA AGGIUNGERE
                maestro_query($maestro,"SELECT SELECTEDID FROM QVSELECTIONS WHERE PARENTID='$PARENTID' AND SELECTEDID IN ($in)",$r);
                for($i=0; $i<count($r); $i++){
                    $SELASS[$r[$i]["SELECTEDID"]]=false;
                }
                // DETERMINO L'ULTIMO SORTER
                maestro_query($maestro,"SELECT MAX(SORTER) AS MAXSORTER FROM QVSELECTIONS WHERE PARENTID='$PARENTID'", $r);
                if(count($r)==1)
                    $SORTER=intval($r[0]["MAXSORTER"]);
                else
                    $SORTER=0;
                // INSERIMENTO SELEZIONATI
                foreach($SELASS as $SELECTEDID => $bool){
                    if($bool){
                        $SYSID=qv_createsysid($maestro);
                        $SORTER+=1;
                        $columns="SYSID,PARENTTABLE,PARENTFIELD,PARENTID,SELECTEDTABLE,SELECTEDID,ENABLED,UPWARD,FLAG1,FLAG2,FLAG3,FLAG4,SORTER,DISTANCE";
                        $values="'$SYSID','$PARENTTABLE','$PARENTFIELD','$PARENTID','$SELECTEDTABLE','$SELECTEDID',$ENABLED,$UPWARD,$FLAG1,$FLAG2,$FLAG3,$FLAG4,$SORTER,$DISTANCE";
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