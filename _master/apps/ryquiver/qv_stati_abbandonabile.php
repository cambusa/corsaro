<?php 
/****************************************************************************
* Name:            qv_stati_abbandonabile.php                               *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_stati_abbandonabile($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO PRATICAID
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHE", "PRATICAID", "", $PRATICAID, "*");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare lo stato: [PRATICAID]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $STATOID=$pratica["STATOID"];
        
        // CERCO I MOTIVI ATTIVI DELLO STATO
        $sql="SELECT * FROM QW_MOTIVISTATO WHERE STATOID='$STATOID' AND ENABLED=1";
        maestro_query($maestro, $sql, $r);
        for($i=0; $i<count($r); $i++){
            $MOTIVEID=$r[$i]["SYSID"];
            $INIZIATA=intval($r[$i]["INIZIATA"]);
            $TERMINATA=intval($r[$i]["TERMINATA"]);
            
            // ATTIVITA' OBBLIGATORIA
            if($INIZIATA>0){
                $ora=date("YmdHis");
                $sql="SELECT SYSID FROM QW_ATTIVITAJOIN WHERE PRATICAID='$PRATICAID' AND MOTIVEID='$MOTIVEID' AND AUXTIME<=[:TIME($ora)] AND CONSISTENCY=0 AND AVAILABILITY=0";
                maestro_query($maestro, $sql, $s);
                if(count($s)==0){
                    $babelcode="QVERR_ATTOBBLIG";
                    $b_params=array();
                    $b_pattern="Attività obbligatorie non intraprese";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
            // ATTIVITA' VINCOLANTI
            if($TERMINATA>0){
                $ora=date("YmdHis");
                $sql="SELECT STATUS FROM QW_ATTIVITAJOIN WHERE PRATICAID='$PRATICAID' AND MOTIVEID='$MOTIVEID' AND CONSISTENCY=0 AND AVAILABILITY=0";
                maestro_query($maestro, $sql, $s);
                for($j=0; $j<count($s); $j++){
                    if($s[$j]["STATUS"]==0){
                        $babelcode="QVERR_ATTPENDENTI";
                        $b_params=array();
                        $b_pattern="Attività pendenti da completare";
                        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
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