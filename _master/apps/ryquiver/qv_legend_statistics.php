<?php 
/****************************************************************************
* Name:            qv_legend_statistics.php                                 *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_applications."ryquiver/qv_legend_infoconfig.php";
function qv_legend_statistics($maestro, $data){
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
        
        // DATI PRATICA
        $PRATICATOT=0;
        $PRATICADESCR="(nessuna pratica selezionata)";
        if(isset($data["PRATICAID"])){
            $PRATICAID=$data["PRATICAID"];
            if($PRATICAID!=""){
                $sql="SELECT DESCRIPTION,AUXAMOUNT FROM QVQUIVERS WHERE SYSID='$PRATICAID'";
                maestro_query($maestro, $sql, $r);
                if(count($r)==1){
                    $PRATICATOT=$r[0]["AUXAMOUNT"];
                    $PRATICADESCR=$r[0]["DESCRIPTION"];
                }
            }
        }
        
        // SINCRONIZZAZIONE LISTE
        if(isset($data["SYNCHROID"]))
            $SYNCHROID=$data["SYNCHROID"];
        else
            $SYNCHROID="";
            
        if($SYNCHROID!=""){
            $INDEXES=array();
            $LISTID=array();
        }
        $CLUSTERRIF="";

        // LEGGO LE INFO DELLA CONFIGURAZIONE
        $datax=array();
        $datax["LEGENDID"]=$LEGENDID;
        $jret=qv_legend_infoconfig($maestro, $datax);
        unset($datax);
        $INFOLEGEND=$jret["params"];
        $CONTOID=$INFOLEGEND["CONTOID"];
        $GENREID=$INFOLEGEND["GENREID"];
        $lenkey=$maestro->lenid;
        
        // DETERMINO LA TOTALITA' DELLE FRECCE COINVOLTE 
        $OUTPUT=array();
        $TOTCOUNTSEL=0;
        $TOTSEL=0;
        $TOTCOUNTCLUST=0;
        $TOTCLUST=0;
        $QUERIES=$data["QUERIES"];
        foreach($QUERIES as $QUERY){
            $REQUESTID=$QUERY["REQUESTID"];
            $CURRENT=intval($QUERY["CURRENT"]);
            $SELECTION=$QUERY["SELECTION"];
            $INVERT=intval($QUERY["INVERT"]);
            $QUERYID=$QUERY["QUERYID"];
            $VIEW=$INFOLEGEND["QUERIES"][$QUERYID]["VIEWNAME"];
            $TABLE=$INFOLEGEND["QUERIES"][$QUERYID]["TABLENAME"];
            $SIGNUM=$INFOLEGEND["QUERIES"][$QUERYID]["SIGNUM"];
            $BAGNAME=$INFOLEGEND["QUERIES"][$QUERYID]["BAGNAME"];
            
            $CURRENTID="";
            $COUNTSEL=0;
            $SUBSEL=0;
            $COUNTCLUST=0;
            $SUBCLUST=0;
            
            $reqpath=$path_cambusa."ryque/requests/".$REQUESTID;

            if(file_exists($reqpath.".ndx")){
                $index=file_get_contents($reqpath.".ndx");
                
                if($index!=""){
                    // DETERMINO $CURRENTID
                    if($CURRENT>0){
                        $CURRENTID=substr($index, ($CURRENT-1)*($lenkey+1), $lenkey);
                    }

                    // CREO UN VETTORE DI SELEZIONATI
                    $SELECTLIST=array();
                    if($INVERT==0){
                        if($SELECTION!=""){
                            $v=explode("|", $SELECTION);
                            foreach($v as $i){
                                $SELECTLIST[]=$i;
                            }
                            $COUNTSEL=count($SELECTLIST);
                        }
                    }
                    // CALCOLO SALDO SELEZIONE
                    $sellist=array();
                    foreach($SELECTLIST as $i){
                        $sellist[]=substr($index, ($i-1)*($lenkey+1), $lenkey);
                    }
                    $selection="'".implode("','", $sellist)."'";
                    if($selection!="''"){
                        $sql="SELECT AMOUNT,BOWID FROM QVARROWS WHERE SYSID IN (".$selection.")";
                        maestro_query($maestro, $sql, $r);
                        for($i=0; $i<count($r); $i++){
                            $AMOUNT=floatval($r[$i]["AMOUNT"]);
                            if($r[$i]["BOWID"]==$CONTOID){
                                $AMOUNT=-$AMOUNT;
                            }
                            $SUBSEL+=$AMOUNT;
                        }
                    }
                    $CLUSTERID="";
                    // DETERMINAZIONE DEL CLUSTER CORRENTE
                    if($CURRENTID!=""){
                        $sql="SELECT CLUSTERID FROM $TABLE WHERE SYSID='$CURRENTID'";
                        maestro_query($maestro, $sql, $r);
                        if(count($r)==1){
                            $CLUSTERID=$r[0]["CLUSTERID"];
                            if($CLUSTERID!=""){
                                // LEGGO I RECORD CON MEDESIMO CLUSTER
                                $COUNTCLUST=0;
                                $sql="SELECT SYSID,AMOUNT,BOWID FROM $VIEW WHERE CLUSTERID='$CLUSTERID'";
                                maestro_query($maestro, $sql, $s);
                                for($i=0; $i<count($s); $i++){
                                    if(strpos($index, $s[$i]["SYSID"])!==false){
                                        $AMOUNT=floatval($s[$i]["AMOUNT"]);
                                        if($s[$i]["BOWID"]==$CONTOID){
                                            $AMOUNT=-$AMOUNT;
                                        }
                                        $SUBCLUST+=$AMOUNT;
                                        $COUNTCLUST+=1;
                                    }
                                }
                            }
                        }
                    }
                    if($SYNCHROID!=$QUERYID)
                        $INDEXES[$QUERYID]=$index;
                    else
                        $CLUSTERRIF=$CLUSTERID;
                }
            }
            $OUTPUT[$QUERYID]["COUNTSEL"]=$COUNTSEL;
            $OUTPUT[$QUERYID]["SELECTION"]=$SIGNUM*$SUBSEL;
            $OUTPUT[$QUERYID]["COUNTCLUST"]=$COUNTCLUST;
            $OUTPUT[$QUERYID]["CLUSTER"]=$SIGNUM*$SUBCLUST;

            // ASSEGNAMENTO PRATICA
            $OUTPUT[$QUERYID]["PRATICATOT"]=$PRATICATOT;
            $OUTPUT[$QUERYID]["PRATICADESCR"]=$PRATICADESCR;

            // TOTALIZZAZIONI
            $TOTCOUNTSEL+=$OUTPUT[$QUERYID]["COUNTSEL"];
            $TOTSEL+=$OUTPUT[$QUERYID]["SELECTION"];
            $TOTCOUNTCLUST+=$OUTPUT[$QUERYID]["COUNTCLUST"];
            $TOTCLUST+=$OUTPUT[$QUERYID]["CLUSTER"];
            
            // DETERMINAZIONE REGISTRY
            $OUTPUT[$QUERYID]["REGISTRY"]="";
            if($CURRENTID!=""){
                $sql="SELECT SYSID,DESCRIPTION,REGISTRY FROM QVARROWS WHERE SYSID='$CURRENTID'";
                maestro_query($maestro, $sql, $r);
                if(count($r)==1){
                    $OUTPUT[$QUERYID]["REGISTRY"]=$r[0]["DESCRIPTION"]."<br/>".$r[0]["REGISTRY"];
                }
            }
        }
        // TOTALI GENERALI E SINCRONIZZAZIONE LISTE
        foreach($QUERIES as $QUERY){
            $QUERYID=$QUERY["QUERYID"];
            $OUTPUT[$QUERYID]["TOTCOUNTSEL"]=$TOTCOUNTSEL;
            $OUTPUT[$QUERYID]["TOTSEL"]=$TOTSEL;
            $OUTPUT[$QUERYID]["TOTCOUNTCLUST"]=$TOTCOUNTCLUST;
            $OUTPUT[$QUERYID]["TOTCLUST"]=$TOTCLUST;

            // SINCRONIZZAZIONE LISTE
            $FIRSTID=0;
            if($SYNCHROID!=$QUERYID){
                if($CLUSTERRIF!=""){
                    $index=$INDEXES[$QUERYID];
                    // LEGGO I RECORD CON MEDESIMO CLUSTER
                    $sql="SELECT SYSID,AMOUNT,BOWID FROM $VIEW WHERE CLUSTERID='$CLUSTERRIF'";
                    maestro_query($maestro, $sql, $same);
                    foreach($same as $ROW){
                        $p=strpos($index, $ROW["SYSID"]);
                        if($p!==false){
                            $test=round($p/($lenkey+1))+1;
                            if($FIRSTID==0 || $test<$FIRSTID){
                                $FIRSTID=$test;
                            }
                        }
                    }
                }
            }
            $OUTPUT[$QUERYID]["FIRSTID"]=$FIRSTID;
        }
        // VARIABILI DI RITORNO
        $babelparams["QUERIES"]=$OUTPUT;
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