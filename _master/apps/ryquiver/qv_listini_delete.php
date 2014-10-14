<?php 
/****************************************************************************
* Name:            qv_listini_delete.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_quivers_remove.php";
include_once $path_cambusa."ryquiver/qv_equivalences_remove.php";
include_once $path_cambusa."ryquiver/qv_arrows_delete.php";
function qv_listini_delete($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO LISTINOID
        qv_solverecord($maestro, $data, "QW_LISTINI", "LISTINOID", "", $LISTINOID);
        if($LISTINOID==""){
            $babelcode="QVERR_LISTINOID";
            $b_params=array();
            $b_pattern="Listino non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        if(isset($data["EQUIVALENCES"])){
            $EQUIVALENCES=$data["EQUIVALENCES"];
        }
        else{
            $babelcode="QVERR_EQUIVALENCES";
            $b_params=array();
            $b_pattern="Elenco equivalenze non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        $list=explode("|", $EQUIVALENCES);
        
        foreach($list as $EQUIVALENCEID){
            $sql="SELECT * FROM QVEQUIVALENCES WHERE SYSID='$EQUIVALENCEID'";
            maestro_query($maestro, $sql, $r);
            if(count($r)==1){
                $REFERENCEID=$r[0]["REFERENCEID"];
                $EQUIVALENTID=$r[0]["EQUIVALENTID"];
        
                // SGANCIO DAL LISTINO
                $datax=array();
                $datax["QUIVERID"]=$LISTINOID;
                $datax["ARROWID"]=$REFERENCEID;
                $jret=qv_quivers_remove($maestro, $datax);
                unset($datax);
                if(!$jret["success"]){
                    return $jret;
                }

                // RIMUOVO L'EQUIVALENZA
                $datax=array();
                $datax["SYSID"]=$EQUIVALENCEID;
                $jret=qv_equivalences_remove($maestro, $datax);
                unset($datax);
                if(!$jret["success"]){
                    return $jret;
                }

                // CANCELLO LA FRECCIA DI RIFERIMENTO
                $datax=array();
                $datax["SYSID"]=$REFERENCEID;
                $jret=qv_arrows_delete($maestro, $datax);
                unset($datax);
                if(!$jret["success"]){
                    return $jret;
                }

                // CANCELLO LA FRECCIA EQUIVALENTE
                $datax=array();
                $datax["SYSID"]=$EQUIVALENTID;
                $jret=qv_arrows_delete($maestro, $datax);
                unset($datax);
                if(!$jret["success"]){
                    return $jret;
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