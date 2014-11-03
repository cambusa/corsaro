<?php
/****************************************************************************
* Name:            appvalidatequiver.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function appvalidatequiver(
            $maestro, 
            &$data, 
            $prevdata, 
            $SYSID, 
            $TYPOLOGYID, 
            $oper, 
            $user, 
            $role, 
            &$babelcode, 
            &$failure){
    $ret=1;
    switch( substr($TYPOLOGYID, 0, 12) ){
    case "0PRATICHE000":
        if($oper==1){
            // CONGRUENZA DATE
            if(isset($data["DATAINIZIO"]))
                $DATAINIZIO=qv_strtime($data["DATAINIZIO"]);
            else
                $DATAINIZIO=qv_strtime($prevdata["DATAINIZIO"]);

            if(isset($data["DATAFINE"]))
                $DATAFINE=qv_strtime($data["DATAFINE"]);
            else
                $DATAFINE=qv_strtime($prevdata["DATAFINE"]);

            if($DATAINIZIO>$DATAFINE && $DATAFINE>LOWEST_TIME){
                $babelcode="QVERR_CONGRUENZADATE";
                $failure="Date incongruenti";
                $ret=0;
            }
        }
        elseif($oper>=2){
            // IMPEDISCO LA CANCELLAZIONE DI PRATICHE PROTOCOLLATE
            if($prevdata["REFERENCE"]!=""){
                $babelcode="QVERR_PRATPROTOCOLLATA";
                $failure="Non si può cancellare una pratica protocollata";
                $ret=0;
            }
            // IMPEDISCO LA CANCELLAZIONE DI PRATICHE CON ATTIVITA' PROTOCOLLATE
            $TYPEID=qv_actualid($maestro, "0ATTIVITA000");
            $sql="SELECT QVQUIVERARROW.ARROWID AS ARROWID FROM QVQUIVERARROW INNER JOIN QVARROWS ON QVARROWS.SYSID=QVQUIVERARROW.ARROWID WHERE QVQUIVERARROW.QUIVERID='$SYSID' AND QVARROWS.TYPOLOGYID='$TYPEID' AND QVARROWS.REFERENCE<>''";
            maestro_query($maestro, $sql, $r);
            if(count($r)>0){
                $babelcode="QVERR_ATTPROTOCOLLATE";
                $failure="Non si può cancellare una pratica con attività protocollate";
                $ret=0;
            }
        }
        break;
    case "0PROCESSI000":
        if($oper<=1){
            if(isset($data["DATIAGGIUNTIVI"])){
                $data["DATIAGGIUNTIVI"]=str_replace(" ", "", $data["DATIAGGIUNTIVI"]);
                $data["DATIAGGIUNTIVI"]=preg_replace("/[^A-Z_]/i", ",", $data["DATIAGGIUNTIVI"]);
            }
        }
        break;
    }
    return $ret;
}
?>