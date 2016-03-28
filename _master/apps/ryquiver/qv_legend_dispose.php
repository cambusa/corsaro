<?php 
/****************************************************************************
* Name:            qv_legend_dispose.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_quivers_remove.php";
include_once $path_cambusa."ryquiver/qv_quivers_delete.php";
function qv_legend_dispose($maestro, $data){
    global $babelcode, $babelparams;
    global $path_cambusa;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        if(isset($data["PRATICHE"])){
            $PRATICHE=$data["PRATICHE"];
        }
        else{
            $babelcode="QVERR_PRATICHE";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare le pratiche";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $p=explode("|", $PRATICHE);

        // INIZIALIZZAZIONE D'AVANZAMENTO
        $counter=0;
        $total=count($p);
        
        foreach($p as $PRATICAID){
            // MESSAGGIO D'AVANZAMENTO
            $counter+=1;
            _qv_progress("Pratiche rimosse " . $counter . " di " . $total);

            $MONEYTYPE=qv_actualid($maestro, "0MONEY000000");
            $altrefrecce=false;
            // SCANDISCO TUTTI I MOVIMENTI DELLA PRATICA
            $sql="SELECT QVARROWS.SYSID AS SYSID,QVARROWTYPES.GENRETYPEID AS GENRETYPEID FROM QVARROWS INNER JOIN QVARROWTYPES ON QVARROWTYPES.SYSID=QVARROWS.TYPOLOGYID WHERE QVARROWS.SYSID IN (SELECT QVQUIVERARROW.ARROWID FROM QVQUIVERARROW WHERE QVQUIVERARROW.QUIVERID='$PRATICAID')";
            maestro_query($maestro, $sql, $r);
            for($i=0; $i<count($r); $i++){
                $ARROWID=$r[$i]["SYSID"];
                $GENRETYPEID=$r[$i]["GENRETYPEID"];
                if($GENRETYPEID==$MONEYTYPE){
                    // ISTRUZIONE DI RIMOZIONE DELLA FRECCIA DAL QUIVER
                    $datax=array();
                    $datax["QUIVERID"]=$PRATICAID;
                    $datax["ARROWID"]=$ARROWID;
                    $jret=qv_quivers_remove($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    // RISOLUZIONE TABELLA DI ESTENSIONE
                    $sql="SELECT QVARROWTYPES.TABLENAME AS TABLENAME FROM QVARROWS INNER JOIN QVARROWTYPES ON QVARROWTYPES.SYSID=QVARROWS.TYPOLOGYID WHERE QVARROWS.SYSID='$ARROWID'";
                    maestro_query($maestro, $sql, $q);
                    if(count($q)>0){
                        $TABLE=$q[0]["TABLENAME"];
                        // PULIZIA DEI CAMPI DI ASSOCIAZIONE AL QUIVER
                        $sql="UPDATE $TABLE SET STATOID='', QUERYID='', QUERYSIGNUM=0 WHERE SYSID='$ARROWID'";
                        maestro_execute($maestro, $sql, false);
                    }
                }
                else{
                    $altrefrecce=true;
                }
            }
            if($altrefrecce==false){
                // C'ERANO SOLTANTO MOVIMENTI
                // ISTRUZIONE DI CANCELLAZIONE QUIVER
                $datax=array();
                $datax["SYSID"]=$PRATICAID;
                $jret=qv_quivers_delete($maestro, $datax);
                unset($datax);
                if(!$jret["success"]){
                    return $jret;
                    writelog(serialize($jret));
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