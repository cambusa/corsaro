<?php 
/****************************************************************************
* Name:            quiverarw.php                                            *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.63                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function _qv_discharge($maestro, $oper, $SYSID, $TYPOLOGYID, $ROUNDING, $OLD_MOTIVEID, $OLD_AMOUNT, $OLD_BOWID, $OLD_BOWTIME, $OLD_GENREID, $NEW_MOTIVEID, $NEW_MOTIVE_DISCHARGE, $NEW_AMOUNT, $NEW_BOWID, $NEW_BOWTIME, $NEW_GENREID){
    global $babelcode, $babelparams;
    
    $storno=false;
    $scarico=false;
    switch($oper){
    case 0:
        // INSERIMENTO
        if($NEW_MOTIVE_DISCHARGE>0 && $NEW_AMOUNT>0.0001 && $NEW_BOWID!=""){
            $scarico=true;
        }
        break;

    case 1:
        // MODIFICA
        $OLD_MOTIVE_DISCHARGE=$NEW_MOTIVE_DISCHARGE;
        if($OLD_MOTIVEID!=$NEW_MOTIVEID){
            $sql="SELECT DISCHARGE FROM QVMOTIVES WHERE SYSID='$OLD_MOTIVEID'";
            maestro_query($maestro, $sql, $r);
            if(count($r)==1){
                $OLD_MOTIVE_DISCHARGE=intval($r[0]["DISCHARGE"]);
            }
        }
        if($OLD_MOTIVE_DISCHARGE>0){
            if(abs($NEW_AMOUNT-$OLD_AMOUNT)>0.0001 || $NEW_MOTIVE_DISCHARGE!=$OLD_MOTIVE_DISCHARGE || $NEW_BOWID!=$OLD_BOWID || $NEW_BOWTIME!=$OLD_BOWTIME || $NEW_GENREID!=$OLD_GENREID){
                $storno=true;
            }
        }
        if($storno){
            if($NEW_MOTIVE_DISCHARGE>0 && $NEW_AMOUNT>0.0001 && $NEW_BOWID!=""){
                $scarico=true;
            }
        }
        break;

    case 2:
        // CANCELLAZIONE
        if($OLD_AMOUNT>0.0001 && $OLD_BOWID!=""){
            $sql="SELECT DISCHARGE FROM QVMOTIVES WHERE SYSID='$OLD_MOTIVEID'";
            maestro_query($maestro, $sql, $r);
            if(count($r)==1){
                $OLD_MOTIVE_DISCHARGE=intval($r[0]["DISCHARGE"]);
                if($OLD_MOTIVE_DISCHARGE>0){
                    $storno=true;
                }
            }
        }
        break;
    }
    if($storno){
        $sql="DELETE FROM QVDISCHARGES WHERE OUTGOINGID='$SYSID'";
        maestro_execute($maestro, $sql);
    }
    if($scarico){
        switch($NEW_MOTIVE_DISCHARGE){
        case 1:     // 1) LIFO
            $ORD="QVARROWS.TARGETTIME DESC, QVARROWS.AMOUNT";
            break;
        case 2:     // 2) FIFO
            $ORD="QVARROWS.TARGETTIME, QVARROWS.AMOUNT";
            break;
        default:    // 3) Ponderato
            $ORD="QVARROWS.TARGETTIME, QVARROWS.AMOUNT DESC";
            break;
        }

        $RESIDUO=$NEW_AMOUNT;

        $sql="";
        $sql.="SELECT QVARROWS.SYSID AS SYSID, QVARROWS.AMOUNT AS OUTAMOUNT, SUM(QVDISCHARGES.AMOUNT) AS ALLOCAMOUNT ";
        $sql.="FROM QVARROWS ";
        $sql.="LEFT JOIN QVDISCHARGES ON QVDISCHARGES.INCOMINGID=QVARROWS.SYSID ";
        $sql.="WHERE ";
        $sql.="QVARROWS.TYPOLOGYID='$TYPOLOGYID' AND ";
        $sql.="QVARROWS.GENREID='$NEW_GENREID' AND ";
        $sql.="QVARROWS.TARGETID='$NEW_BOWID' AND ";
        $sql.="QVARROWS.TARGETTIME<=[:TIME($NEW_BOWTIME)] AND ";
        $sql.="QVARROWS.STATUS>0 AND ";
        $sql.="QVARROWS.CONSISTENCY=0 AND ";
        $sql.="QVARROWS.AVAILABILITY=0 ";
        $sql.="GROUP BY QVARROWS.SYSID, QVARROWS.AMOUNT ";
        $sql.="ORDER BY $ORD";

        if($NEW_MOTIVE_DISCHARGE<3){
            // SCARICO LIFO-FIFO
            $_p=array(); // Percentuale scaricata
            $_k=array(); // SYSID
            $n=0;
            
            $res=maestro_unbuffered($maestro, $sql);
            while( $r=maestro_fetch($maestro, $res) ){
                $ARROWID=$r["SYSID"];
                $ARROWAMOUNT=floatval($r["OUTAMOUNT"])-floatval($r["ALLOCAMOUNT"]);
            
                if($ARROWAMOUNT>0){
                    $n+=1;
                    $_k[$n]=$ARROWID;
                    
                    if($RESIDUO<=$ARROWAMOUNT){
                        $ARROWAMOUNT=$RESIDUO;
                        $RESIDUO=0;
                    }
                    else{
                        $RESIDUO-=$ARROWAMOUNT;
                    }
                    
                    $_p[$n]=$ARROWAMOUNT;
                    
                    // SE HO SCARICATO TUTTO L'AMMONTARE DELLA FRECCIA IN USCITA ESCO
                    if($RESIDUO<=0.0001){
                        break;
                    }
                }
            }
            maestro_free($maestro, $res);
            if($RESIDUO>0.0001){
                $babelcode="QVERR_UNDISCHARGEABLE";
                $b_params=array();
                $b_pattern="Interazione non scaricabile";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            // SCARICO PONDERATO
            $TOTALE=0;
            $_x=array(); // Carichi
            $_a=array(); // Allocazione
            $_y=array(); // Scarichi
            $_p=array(); // Percentuale scaricata
            $_r=array(); // Resto
            $_k=array(); // SYSID
            $n=0;
            $_y[0]=$NEW_AMOUNT;
            $_r[0]=0;
            
            $res=maestro_unbuffered($maestro, $sql);
            while( $r=maestro_fetch($maestro, $res) ){
                $ARROWID=$r["SYSID"];
                $ARROWAMOUNT=floatval($r["OUTAMOUNT"]);
                $ALLOC=floatval($r["ALLOCAMOUNT"]);
                
                if($ARROWAMOUNT-$ALLOC>0){
                    $TOTALE+=$ARROWAMOUNT;
                    $n+=1;
                    $_k[$n]=$ARROWID;
                    $_x[$n]=$ARROWAMOUNT;
                    $_a[$n]=$ALLOC;
                }
            }
            maestro_free($maestro, $res);
            
            if($TOTALE>0){
                for($i=1; $i<=$n; $i++){
                    // CORREGGO LO SCARICO TOTALE CON IL RESIDUO NON SCARICATO NEL CICLO PRECEDENTE
                    $_y[$i]=$_y[$i-1]+$_r[$i-1];
                    // CALCOLO LA PERCENTUALE DA SCARICARE DALLA FRECCIA IN INGRESSO
                    $_p[$i]=round($_x[$i]*$_y[$i]/$TOTALE , $ROUNDING);
                    // CALCOLO L'EVENTUALE RESIDUO DA RIPORTARE NEI PASSI SUCCESSIVI
                    $d=$_x[$i]-$_a[$i];
                    if( $_p[$i] > $d ){
                        $_r[$i]=$_p[$i]-$d;
                        $_p[$i]=$d;
                    }
                    else{
                        $_r[$i]=0;
                    }
                    $RESIDUO-=$_p[$i];
                }
                if($RESIDUO<1){
                    $_p[$n]+=$RESIDUO;
                    $RESIDUO=0;
                }
            }
        }
        if($RESIDUO>0.0001){
            $babelcode="QVERR_UNDISCHARGEABLE";
            $b_params=array();
            $b_pattern="Movimento non scaricabile";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        for($i=1; $i<=$n; $i++){
            $ID=qv_createsysid($maestro);
            $ARROWID=$_k[$i];
            $ARROWAMOUNT=$_p[$i];
            $sql="INSERT INTO QVDISCHARGES(SYSID,OUTGOINGID,INCOMINGID,AMOUNT,SORTER) VALUES('$ID', '$SYSID', '$ARROWID', $ARROWAMOUNT, $i)";
            maestro_execute($maestro, $sql);
        }
    }
}
?>