<?php 
/****************************************************************************
* Name:            qv_legend_consolidate.php                                *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_quivers_insert.php";
include_once $path_cambusa."ryquiver/qv_quivers_add.php";
include_once $path_cambusa."rymaestro/maestro_querylib.php";
include_once $path_applications."ryquiver/qv_legend_infoconfig.php";
include_once $path_applications."ryquiver/pratiche_saldo.php";
function qv_legend_consolidate($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    global $path_cambusa;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        // UTILE PER AVANZAMENTO DA CLIENT
        if(isset($data["PROGRESS"]))
            $PROGRESS=intval($data["PROGRESS"]);
        else
            $PROGRESS=0;
        $BLOCKSIZE=1000;

        // INIZIALIZZO LA CREZIONE SI SYSID DI MASSA
        qv_bulkinitialize($maestro);

        // RISOLVO LE INFO DI SESSIONE
        qv_infosession($maestro);

        // RISOLVO L'ATTORE RICHIEDENTE
        maestro_query($maestro, "SELECT SYSID FROM QW_ATTORI WHERE UTENTEID='$global_quiveruserid'", $r);
        if(count($r)>0){
            $RICHIEDENTEID=$r[0]["SYSID"];
        }
        else{
            $babelcode="QVERR_RICHIEDENTEID";
            $b_params=array();
            $b_pattern="Il richiedente non è censito come attore";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // LEGGO IL LEGEND
        $legend=qv_solverecord($maestro, $data, "QW_LEGEND", "LEGENDID", "", $LEGENDID, "*");
        if($LEGENDID==""){
            $babelcode="QVERR_LEGENDID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la configurazione";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $PROCESSOID=$legend["PROCESSOID"];

        // CONTROLLO CHE IL RICHIEDENTE SIA COINVOLTO NEL PROCESSO
        maestro_query($maestro, "SELECT SYSID FROM QVSELECTIONS WHERE PARENTID='$PROCESSOID' AND SELECTEDID='$RICHIEDENTEID'", $r);
        if(count($r)==0){
            $babelcode="QVERR_NOATTORE";
            $b_params=array();
            $b_pattern="Il richiedente non è coinvolto nel processo";
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
        
        $PRATICHE=array();
        $FRECCE=array();
        
        $positiveonly=true;
        
        // DETERMINO LA TOTALITA' DELLE FRECCE COINVOLTE 
        // ASSOCIATE ALLA TABELLA DI ESTENSIONE DATI
        // E COSTRUISCO IL PIANO DELLE PRATICHE
        $QUERIES=$data["QUERIES"];
        foreach($QUERIES as $QUERY){
            $REQUESTID=$QUERY["REQUESTID"];
            $WHERE=ryqNormalize($QUERY["WHERE"]);
            $QUERYID=$QUERY["QUERYID"];
            $VIEW=$INFOLEGEND["QUERIES"][$QUERYID]["VIEWNAME"];
            $TABLE=$INFOLEGEND["QUERIES"][$QUERYID]["TABLENAME"];
            $SIGNUM=$INFOLEGEND["QUERIES"][$QUERYID]["SIGNUM"];
            
            if($SIGNUM!=1){
                $positiveonly=false;
            }
            
            $reqpath=$path_cambusa."ryque/requests/".$REQUESTID;
            $index=file_get_contents($reqpath.".ndx");
            $lenkey=$maestro->lenid;
            
            if($index!=""){
                $sql="SELECT SYSID,DESCRIPTION,CLUSTERID FROM $VIEW WHERE $WHERE AND CLUSTERID<>''";
                $res=maestro_unbuffered($maestro, $sql);
                while( $row=maestro_fetch($maestro, $res) ){
                    $ARROWID=$row["SYSID"];
                    $DESCRIPTION=$row["DESCRIPTION"];
                    $CLUSTERID=$row["CLUSTERID"];
                    if(strpos($index, $ARROWID)!==false){
                        if(!isset($FRECCE[$ARROWID])){
                            $FRECCE[$ARROWID]=array("QUERYID" => $QUERYID, "TABLE" => $TABLE, "SIGNUM" => $SIGNUM);
                            if(!isset($PRATICHE[$CLUSTERID])){
                                $PRATICHE[$CLUSTERID]=array();
                                $PRATICHE[$CLUSTERID]["ARROWS"]=array();
                                // LA DESCRIZIONE DELLA PRIMA FRECCIA
                                // LA ASSUMO COME DESCRIZIONE DELLA PRATICA
                                $PRATICHE[$CLUSTERID]["DESCRIPTION"]=substr($CLUSTERID." (".$DESCRIPTION.")", 0, 50);
                            }
                            $PRATICHE[$CLUSTERID]["ARROWS"][]=$ARROWID;
                        }
                    }
                }
                maestro_free($maestro, $res);
            }
        }
        // UTILE PER AVANZAMENTO DA CLIENT
        if($PROGRESS){
            print substr( count($PRATICHE) . str_repeat(" ", $BLOCKSIZE), 0, $BLOCKSIZE);
            flush();
        }
        // GENERAZIONE PRATICHE
        $DATAINIZIO=date("Ymd");
        foreach($PRATICHE as $CLUSTERID => $PRATICA){
        
            // UTILE PER AVANZAMENTO DA CLIENT
            if($PROGRESS){
                print str_repeat("X", $BLOCKSIZE);
                flush();
            }
        
            // INSERIMENTO QUIVER
            $datax=array();
            $datax["DESCRIPTION"]=$PRATICA["DESCRIPTION"];
            $datax["TYPOLOGYID"]=qv_actualid($maestro, "0PRATICHE000");
            $datax["PROCESSOID"]=$PROCESSOID;
            $datax["STATOID"]=$STATOID;
            $datax["DATAINIZIO"]=$DATAINIZIO;
            $datax["RICHIEDENTEID"]=$RICHIEDENTEID;
            $jret=qv_quivers_insert($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
            $PRATICAID=$jret["SYSID"];
            
            // SCANDISCO I MOVIMENTI DA AGGANCIARE ALLA PRATICA
            foreach($PRATICA["ARROWS"] as $ARROWID){
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
                $QUERYID=$FRECCE[$ARROWID]["QUERYID"];
                $TABLE=$FRECCE[$ARROWID]["TABLE"];
                $SIGNUM=$FRECCE[$ARROWID]["SIGNUM"];
                $sql="UPDATE $TABLE SET STATOID='$STATOID', QUERYID='$QUERYID', QUERYSIGNUM=$SIGNUM WHERE SYSID='$ARROWID'";
                maestro_execute($maestro, $sql, false);
            }
            // AGGIORNO IL SALDO DEL QUIVER
            pratiche_saldo($maestro, $CONTOID, $GENREID, $PRATICAID, $positiveonly);
            
            // CHIUDO LA TRANSAZIONE IN CORSO
            maestro_commit($maestro);
            
            // RIAPRO UNA TRANSAZIONE
            maestro_begin($maestro);
        }
        // UTILE PER AVANZAMENTO DA CLIENT
        if($PROGRESS){
            print "Y";
        }
    }
    catch(Exception $e){
        $success=0;
        $message=$e->getMessage();
        // UTILE PER AVANZAMENTO DA CLIENT
        if($PROGRESS){
            print "Y";
        }
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