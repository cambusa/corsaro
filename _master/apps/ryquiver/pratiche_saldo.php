<?php 
/****************************************************************************
* Name:            pratiche_saldo.php                                       *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$TYPOLOGY_TABLE=array();
function pratiche_saldo($maestro, $CONTOID, $GENREID, $PRATICAID, $positiveonly=false){
    global $babelcode, $babelparams;
    global $TYPOLOGY_TABLE;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        if($positiveonly){
            // CALCOLO IL SALDO DARE DEL QUIVER
            $DARE=0;
            $sql="SELECT SUM(AMOUNT) AS SALDO FROM QVARROWS WHERE BOWID='$CONTOID' AND GENREID='$GENREID' AND CONSISTENCY<=1 AND SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$PRATICAID')";
            maestro_query($maestro, $sql, $r);
            $DARE=floatval($r[0]["SALDO"]);
            
            // CALCOLO IL SALDO AVERE DEL QUIVER
            $AVERE=0;
            $sql="SELECT SUM(AMOUNT) AS SALDO FROM QVARROWS WHERE TARGETID='$CONTOID' AND GENREID='$GENREID' AND CONSISTENCY<=1 AND SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$PRATICAID')";
            maestro_query($maestro, $sql, $r);
            $AVERE=floatval($r[0]["SALDO"]);
            
            $TOTAL=$AVERE-$DARE;
        }
        else{
            $TOTAL=0;

            $sql="SELECT SYSID,AMOUNT,TYPOLOGYID,BOWID FROM QVARROWS WHERE (BOWID='$CONTOID' OR TARGETID='$CONTOID') AND GENREID='$GENREID' AND CONSISTENCY<=1 AND SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$PRATICAID')";
            maestro_query($maestro, $sql, $r);
            for($i=0; $i<count($r); $i++){
                $ARROWID=$r[$i]["SYSID"];
                $TYPOLOGYID=$r[$i]["TYPOLOGYID"];
                $BOWID=$r[$i]["BOWID"];
                $AMOUNT=floatval($r[$i]["AMOUNT"]);
                if($BOWID==$CONTOID){
                    $AMOUNT=-$AMOUNT;
                }
                $TABLE="";
                if(isset($TYPOLOGY_TABLE[$TYPOLOGYID])){
                    $TABLE=$TYPOLOGY_TABLE[$TYPOLOGYID];
                }
                else{
                    $sql="SELECT TABLENAME FROM QVARROWTYPES WHERE SYSID='$TYPOLOGYID'";
                    maestro_query($maestro, $sql, $t);
                    if(count($t)>0){
                        $TABLE=$t[0]["TABLENAME"];
                        $TYPOLOGY_TABLE[$TYPOLOGYID]=$TABLE;
                    }
                }
                if($TABLE!=""){
                    $sql="SELECT QUERYSIGNUM FROM $TABLE WHERE SYSID='$ARROWID'";
                    maestro_query($maestro, $sql, $t);
                    if(count($t)>0){
                        switch(intval($t[0]["QUERYSIGNUM"])){
                        case 0:
                            $AMOUNT=0;
                            break;
                        case -1:
                            $AMOUNT=-$AMOUNT;
                        }
                    }
                }
                $TOTAL+=$AMOUNT;
            }
        }
        // AGGIORNO IL SALDO DEL QUIVER 
        $sql="UPDATE QVQUIVERS SET AUXAMOUNT=".$TOTAL." WHERE SYSID='$PRATICAID'";
        maestro_execute($maestro, $sql, false);
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