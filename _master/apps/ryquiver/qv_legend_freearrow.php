<?php 
/****************************************************************************
* Name:            qv_legend_freearrow.php                                  *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_quivers_remove.php";
include_once $path_applications."ryquiver/qv_legend_infoconfig.php";
include_once $path_applications."ryquiver/pratiche_saldo.php";
function qv_legend_freearrow($maestro, $data){
    global $babelcode, $babelparams;
    global $path_cambusa;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        if(isset($data["LEGENDID"])){
            $LEGENDID=$data["LEGENDID"];
        }
        else{
            $babelcode="QVERR_LEGENDID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la configurazione";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        if(isset($data["PRATICAID"])){
            $PRATICAID=$data["PRATICAID"];
        }
        else{
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        if(isset($data["ARROWS"])){
            $ARROWS=$data["ARROWS"];
        }
        else{
            $babelcode="QVERR_ARROWS";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare i movimenti";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // LEGGO LE INFO DELLA CONFIGURAZIONE
        $datax=array();
        $datax["LEGENDID"]=$LEGENDID;
        $jret=qv_legend_infoconfig($maestro, $datax);
        unset($datax);
        $CONTOID=$jret["params"]["CONTOID"];
        $GENREID=$jret["params"]["GENREID"];
        
        $f=explode("|", $ARROWS);
        foreach($f as $ARROWID){
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
        // AGGIORNO IL SALDO DEL QUIVER
        pratiche_saldo($maestro, $CONTOID, $GENREID, $PRATICAID);
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