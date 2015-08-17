<?php 
/****************************************************************************
* Name:            qv_legend_praticaadd.php                                 *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_quivers_add.php";
include_once $path_applications."ryquiver/qv_legend_infoconfig.php";
include_once $path_applications."ryquiver/pratiche_saldo.php";
function qv_legend_praticaadd($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    global $path_cambusa;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        // LEGGO IL LEGEND
        qv_solverecord($maestro, $data, "QW_LEGEND", "LEGENDID", "", $LEGENDID);
        if($LEGENDID==""){
            $babelcode="QVERR_LEGENDID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la configurazione";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // LEGGO LA PRATICA
        qv_solverecord($maestro, $data, "QW_PRATICHE", "PRATICAID", "", $PRATICAID);
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la pratica";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // LEGGO LE INFO DELLA CONFIGURAZIONE
        $datax=array();
        $datax["LEGENDID"]=$LEGENDID;
        $jret=qv_legend_infoconfig($maestro, $datax);
        unset($datax);
        $INFOLEGEND=$jret["params"];
        $STATOID=$INFOLEGEND["STATOID"];
        $CONTOID=$INFOLEGEND["CONTOID"];
        $GENREID=$INFOLEGEND["GENREID"];

        $FRECCE=array();
        
        // DETERMINO LA TOTALITA' DELLE FRECCE COINVOLTE 
        // ASSOCIATE ALLA TABELLA DI ESTENSIONE DATI
        $QUERIES=$data["QUERIES"];
        foreach($QUERIES as $QUERY){
            $REQUESTID=$QUERY["REQUESTID"];
            $CURRENT=intval($QUERY["CURRENT"]);
            $SELECTION=$QUERY["SELECTION"];
            $INVERT=intval($QUERY["INVERT"]);
            $QUERYID=$QUERY["QUERYID"];
            $TABLE=$INFOLEGEND["QUERIES"][$QUERYID]["TABLENAME"];
            $SIGNUM=$INFOLEGEND["QUERIES"][$QUERYID]["SIGNUM"];
            
            $reqpath=$path_cambusa."ryque/requests/".$REQUESTID;
            $index=file_get_contents($reqpath.".ndx");
            $lenkey=$maestro->lenid;
            
            if($SELECTION!="" || $INVERT!=0){
                if($INVERT==0){
                    $v=explode("|", $SELECTION);
                    $s=array();
                    foreach($v as $i){
                        $ARROWID=substr($index, (intval($i)-1)*($lenkey+1), $lenkey);
                        $FRECCE[$ARROWID]=array("QUERYID" => $QUERYID, "TABLE" => $TABLE, "SIGNUM" => $SIGNUM);
                    }
                }
                else{
                    $v=explode("|", $SELECTION);
                    $s=array();
                    for($i=1; $i<=round(strlen($index)/($lenkey+1)); $i++){
                        if(!in_array($i, $v)){
                            $ARROWID=substr($index, ($i-1)*($lenkey+1), $lenkey);
                            $FRECCE[$ARROWID]=array("QUERYID" => $QUERYID, "TABLE" => $TABLE, "SIGNUM" => $SIGNUM);
                        }
                    }
                }
            }
        }
        // SCANDISCO I MOVIMENTI DA AGGANCIARE ALLA PRATICA
        foreach($FRECCE as $ARROWID => $FRECCIA){
            // AGGANCIO DEL MOVIMENTO AL QUIVER
            $datax=array();
            $datax["QUIVERID"]=$PRATICAID;
            $datax["ARROWID"]=$ARROWID;
            $jret=qv_quivers_add($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
            // ASSEGNAZIONE DI STATOID, QUERYID E QUERYSIGNUM AL MOVIMENTO
            $QUERYID=$FRECCIA["QUERYID"];
            $TABLE=$FRECCIA["TABLE"];
            $SIGNUM=$FRECCIA["SIGNUM"];
            $sql="UPDATE $TABLE SET STATOID='$STATOID', QUERYID='$QUERYID', QUERYSIGNUM=$SIGNUM WHERE SYSID='$ARROWID'";
            maestro_execute($maestro, $sql, false);
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