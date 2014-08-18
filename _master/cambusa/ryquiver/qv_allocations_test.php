<?php 
/****************************************************************************
* Name:            qv_allocations_test.php                                  *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverlck.php";
include_once "quiverinf.php";
function qv_allocations_test($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        qv_solvealloc($maestro, $data, $SYSID, $TABLENAME, $RECORDID);
        
        if($SYSID!="")
            $where="SYSID='$SYSID' AND ENDTIME>=[:NOW()]";
        else
            $where="[:UPPER(TABLENAME)]='".strtoupper($TABLENAME)."' AND RECORDID='$RECORDID' AND ENDTIME>=[:NOW()]";
        
        maestro_query($maestro,"SELECT QVALLOCATIONS.SYSID AS LOCKID,QVALLOCATIONS.OWNERID AS OWNERID,QVUSERS.USERNAME AS USERNAME FROM QVALLOCATIONS INNER JOIN QVUSERS ON QVUSERS.SYSID=QVALLOCATIONS.OWNERID WHERE $where",$r);
        if(count($r)>0){
            $babelparams["LOCKED"]="1";
            $babelparams["LOCKID"]=$r[0]["LOCKID"];
            $babelparams["OWNERID"]=$r[0]["OWNERID"];
            $babelparams["USERNAME"]=$r[0]["USERNAME"];
        }
        else{
            $babelparams["LOCKED"]="0";
            $babelparams["LOCKID"]="";
            $babelparams["OWNERID"]="";
            $babelparams["USERNAME"]="";
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