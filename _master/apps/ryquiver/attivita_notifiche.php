<?php 
/****************************************************************************
* Name:            attivita_notifiche.php                                   *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function _qv_attivita_notifica($maestro, $ATTIVITAID, $BOWID, $TARGETID, $action){
    global $global_quiveruserid;
    try{
        $sql="SELECT SYSID,UTENTEID FROM QW_ATTORI WHERE SYSID IN ('$BOWID','$TARGETID') AND UTENTEID<>''";
        maestro_query($maestro, $sql, $r);
        if(count($r)>0){
            $EGOBOWID="";
            $EGOTARGETID="";
            for($i=0; $i<count($r); $i++){
                if($r[$i]["SYSID"]==$BOWID){
                    $EGOBOWID=$r[$i]["UTENTEID"];
                }
                if($r[$i]["SYSID"]==$TARGETID){
                    $EGOTARGETID=$r[$i]["UTENTEID"];
                }
            }
            if($EGOBOWID!="" || $EGOTARGETID!=""){
                $sql="";
                $sql.="SELECT ";
                $sql.="QW_ATTIVITA.DESCRIPTION AS DESCRIPTION, ";
                $sql.="QW_ATTIVITA.REGISTRY AS REGISTRY, ";
                $sql.="QW_ATTIVITA.CONSISTENCY AS CONSISTENCY, ";
                $sql.="QW_ATTIVITA.TARGETID AS TARGETID, ";
                $sql.="QW_ATTIVITA.IMPORTANZA AS IMPORTANZA, ";
                $sql.="QW_PRATICHE.DESCRIPTION AS PRATICA ";
                $sql.="FROM QW_ATTIVITA ";
				$sql.="INNER JOIN QVQUIVERARROW ON QVQUIVERARROW.ARROWID=QW_ATTIVITA.SYSID ";
                $sql.="INNER JOIN QW_PRATICHE ON QW_PRATICHE.SYSID=QVQUIVERARROW.QUIVERID ";
                $sql.="WHERE QW_ATTIVITA.SYSID='$ATTIVITAID'";
                maestro_query($maestro, $sql, $r);
                if(count($r)>0){
                    $PRATICA=$r[0]["PRATICA"];
                    if(strlen($PRATICA)>30){
                        $PRATICA=substr($PRATICA, 0, 30) . "...";
                    }
                    $DESCRIPTION=$PRATICA.": ".$r[0]["DESCRIPTION"];
                    $REGISTRY=$r[0]["REGISTRY"];
                    $CONSISTENCY=intval($r[0]["CONSISTENCY"]);
                    $IMPORTANZA=intval($r[0]["IMPORTANZA"]);
                    $TARGETID=$r[0]["TARGETID"];
                    if($CONSISTENCY<2){
                        if($EGOBOWID!="" && $EGOBOWID!=$global_quiveruserid){
                            $datax=array();
                            $datax["SENDERNAME"]="SERVER";
                            $datax["RECEIVERID"]=$EGOBOWID;
                            $datax["DESCRIPTION"]=$DESCRIPTION;
                            $datax["REGISTRY"]=$REGISTRY;
                            $datax["PRIORITY"]=$IMPORTANZA;
                            if($action){
                                $datax["ACTION"]='{"formname":"qvinterazioni", "formpath":"qvpratiche/", "formtitle":"Interazioni", "attivita":"'.$ATTIVITAID.'" }';
                            }
                            $jret=qv_messages_send($maestro, $datax);
                            unset($datax);
                            if(!$jret["success"]){
                                writelog($jret["message"]);
                            }
                        }
                        if($EGOTARGETID!="" && $EGOTARGETID!=$global_quiveruserid && $EGOTARGETID!=$EGOBOWID){
                            $datax=array();
                            $datax["SENDERNAME"]="SERVER";
                            $datax["RECEIVERID"]=$EGOTARGETID;
                            $datax["DESCRIPTION"]=$DESCRIPTION;
                            $datax["REGISTRY"]=$REGISTRY;
                            $datax["PRIORITY"]=$IMPORTANZA;
                            if($action){
                                $datax["ACTION"]='{"formname":"qvinterazioni", "formpath":"qvpratiche/", "formtitle":"Interazioni", "attivita":"'.$ATTIVITAID.'" }';
                            }
                            $jret=qv_messages_send($maestro, $datax);
                            unset($datax);
                            if(!$jret["success"]){
                                writelog($jret["message"]);
                            }
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
}
?>