<?php 
/****************************************************************************
* Name:            qv_legend_search.php                                     *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryque/ryq_gauge.php";
include_once $path_cambusa."rymaestro/maestro_querylib.php";
include_once $path_applications."ryquiver/qv_legend_infoconfig.php";
function qv_legend_search($maestro, $data){
    global $global_quiveruserid, $global_quiverroleid;
    global $babelcode, $babelparams;
    global $path_cambusa, $path_customize, $path_databases, $path_applications;
    global $SEEKER;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // CHIUDO LA TRANSAZIONE IN CORSO
        maestro_commit($maestro);
        
        // LEGGO IL LEGEND
        qv_solverecord($maestro, $data, "QW_LEGEND", "LEGENDID", "", $LEGENDID);
        if($LEGENDID==""){
            $babelcode="QVERR_LEGENDID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la configurazione";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $lenkey=$maestro->lenid;

        if(isset($data["GAUGEID"]))
            $GAUGEID=$data["GAUGEID"];
        else
            $GAUGEID="";
        
        $INDEXES=array();
        $ROWS=array();

        if($GAUGEID==""){
            // LEGGO LE OPZIONI DI RICERCA
            $GAUGE=0;
            $TOLERANCE=0.0001;
            $ENABLED=1;
            $SIMPLE=1;
            $DATEMIN=LOWEST_DATE;
            $DATEMAX=HIGHEST_DATE;
            $REGMIN=LOWEST_DATE;
            $REGMAX=HIGHEST_DATE;
            $SMART="";
            $RANGE="";
            
            if(isset($data["OPTIONS"])){
                $OPTIONS=$data["OPTIONS"];
                if(isset($OPTIONS["AMOUNT"])){
                    $GAUGE=floatval($OPTIONS["AMOUNT"]);
                }
                if(isset($OPTIONS["TOLERANCE"])){
                    $TOLERANCE=floatval($OPTIONS["TOLERANCE"]);
                }
                if(isset($OPTIONS["TYPE"])){
                    switch($OPTIONS["TYPE"]){
                    case "M":
                        $SIMPLE=0;
                        break;
                    case "N":
                        $SIMPLE=0;
                        $ENABLED=0;
                    }
                }
                if(isset($OPTIONS["DATEMIN"])){
                    $DATEMIN=ryqEscapize($OPTIONS["DATEMIN"]);
                    if($DATEMIN<=LOWEST_DATE){
                        $DATEMIN=LOWEST_DATE;
                    }
                }
                if(isset($OPTIONS["DATEMAX"])){
                    $DATEMAX=ryqEscapize($OPTIONS["DATEMAX"]);
                    if($DATEMAX<=LOWEST_DATE){
                        $DATEMAX=HIGHEST_DATE;
                    }
                }
                if(isset($OPTIONS["REGMIN"])){
                    $REGMIN=ryqEscapize($OPTIONS["REGMIN"]);
                    if($REGMIN<=LOWEST_DATE){
                        $REGMIN=LOWEST_DATE;
                    }
                }
                if(isset($OPTIONS["REGMAX"])){
                    $REGMAX=ryqEscapize($OPTIONS["REGMAX"]);
                    if($REGMAX<=LOWEST_DATE){
                        $REGMAX=HIGHEST_DATE;
                    }
                }
                if(isset($OPTIONS["SMART"])){
                    $SMART=ryqEscapize($OPTIONS["SMART"]);
                    $SMART=strtoupper($SMART);
                    $SMART=str_replace(" ", "%", $SMART);
                }
                if(isset($OPTIONS["RANGE"])){
                    $RANGE=ryqEscapize($OPTIONS["RANGE"]);
                }
            }
            
            // LEGGO LE INFO DELLA CONFIGURAZIONE
            $datax=array();
            $datax["LEGENDID"]=$LEGENDID;
            $jret=qv_legend_infoconfig($maestro, $datax);
            unset($datax);
            $INFOLEGEND=$jret["params"];
            $CONTOID=$INFOLEGEND["CONTOID"];
            $GENREID=$INFOLEGEND["GENREID"];
            
            // DETERMINO LA TOTALITA' DELLE FRECCE COINVOLTE 
            $QUERIES=$data["QUERIES"];
            $VALUES=array();
            $REFS=array();
            $CLUSTERS=array();
            $PRESERVES=array();
            $REQUESTS=array();
            $maxcluster=0;
            $trdate=array("-", ":", "T", " ", "'", ".");
            foreach($QUERIES as $QUERY){
                $REQUESTID=$QUERY["REQUESTID"];
                $CURRENT=intval($QUERY["CURRENT"]);
                $SELECTION=$QUERY["SELECTION"];
                $INVERT=intval($QUERY["INVERT"]);
                $WHERE=ryqNormalize($QUERY["WHERE"]);
                $QUERYID=$QUERY["QUERYID"];
                $VIEW=$INFOLEGEND["QUERIES"][$QUERYID]["VIEWNAME"];
                $TABLE=$INFOLEGEND["QUERIES"][$QUERYID]["TABLENAME"];
                $SIGNUM=$INFOLEGEND["QUERIES"][$QUERYID]["SIGNUM"];
                $BAGNAME=$INFOLEGEND["QUERIES"][$QUERYID]["BAGNAME"];
                
                $reqpath=$path_cambusa."ryque/requests/".$REQUESTID;
                $index=file_get_contents($reqpath.".ndx");
                $INDEXES[$QUERYID]=$index;
                $ROWS[$QUERYID]=array();
                $PRESERVES[$QUERYID]=array();
                $REQUESTS[$QUERYID]=$REQUESTID;
                
                if($index!=""){
                    // CREO UN VETTORE DI SELEZIONATI
                    // I SELEZIONATI DIRETTI LI DEVO ESCLUDERE
                    $sels=array();
                    $MAXREC=(strlen($index)+1)/($lenkey+1);
                    if($SELECTION!="" && $INVERT==0){
                        $v=explode("|", $SELECTION);
                        foreach($v as $i){
                            $KEY=substr($index, ($i-1)*($lenkey+1), $lenkey);
                            $sels[]=$KEY;
                            $PRESERVES[$QUERYID][]=$KEY;
                        }
                    }
                    // FILTRO PER RANGE
                    if($RANGE=="" || $RANGE==$QUERYID){
                        $sql="SELECT SYSID,BOWID,AMOUNT,CLUSTERID FROM $VIEW WHERE $WHERE";
                        if($SIMPLE){
                            $sql.=" AND ABS( (CASE WHEN BOWID='$CONTOID' THEN -AMOUNT ELSE AMOUNT END)-(".($SIGNUM*$GAUGE).") )<=$TOLERANCE";
                        }
                        if($REGMIN>LOWEST_DATE){
                            $sql.=" AND [:DATE($REGMIN)]<=[:DATE(AUXTIME)]";
                        }
                        if($REGMAX<HIGHEST_DATE){
                            $sql.=" AND [:DATE(AUXTIME)]<=[:DATE($REGMAX)]";
                        }
                        if($DATEMIN>LOWEST_DATE){
                            $sql.=" AND ((BOWID='$CONTOID' AND [:DATE($DATEMIN)]<=[:DATE(BOWTIME)]) OR (TARGETID='$CONTOID' AND [:DATE($DATEMIN)]<=[:DATE(TARGETTIME)]))";
                        }
                        if($DATEMAX<HIGHEST_DATE){
                            $sql.=" AND ((BOWID='$CONTOID' AND [:DATE(BOWTIME)]<=[:DATE($DATEMAX)]) OR (TARGETID='$CONTOID' AND [:DATE(TARGETTIME)]<=[:DATE($DATEMAX)]))";
                        }
                        if($SMART!=""){
                            $sql.=" AND ( [:UPPER(DESCRIPTION)] LIKE '%$SMART%' OR [:UPPER(TAG)] LIKE '%$SMART%' )";
                        }
                        $res=maestro_unbuffered($maestro, $sql);
                        while( $row=maestro_fetch($maestro, $res) ){
                            $ARROWID=$row["SYSID"];
                            if(!in_array($ARROWID, $sels)){
                                // DETERMINO GLI ATTRIBUTI
                                $AMOUNT=$SIGNUM*round(floatval($row["AMOUNT"]), 2);
                                if($row["BOWID"]==$CONTOID){
                                    $AMOUNT=-$AMOUNT;
                                }
                                $CLUSTERID=$row["CLUSTERID"];
                                if($CLUSTERID!=""){
                                    $KEY=$QUERYID.$CLUSTERID;
                                    if(isset($CLUSTERS[$KEY])){
                                        $cluster=$CLUSTERS[$KEY][0];
                                        $CLUSTERS[$KEY][2][]=$ARROWID;
                                        // AGGIORNO I VETTORI PER L'ALGORITMO DI GAUGE
                                        $VALUES[$cluster]+=$AMOUNT;
                                    }
                                    else{
                                        $cluster=$maxcluster;
                                        $maxcluster+=1;
                                        $CLUSTERS[$KEY]=array($cluster, $QUERYID, array($ARROWID));
                                        // AGGIORNO I VETTORI PER L'ALGORITMO DI GAUGE
                                        $VALUES[$cluster]=$AMOUNT;
                                        $REFS[$cluster]=$KEY;
                                    }
                                }
                                else{
                                    $KEY=$QUERYID.$ARROWID;
                                    $cluster=$maxcluster;
                                    $maxcluster+=1;
                                    $CLUSTERS[$KEY]=array($cluster, $QUERYID, array($ARROWID));
                                    // AGGIORNO I VETTORI PER L'ALGORITMO DI GAUGE
                                    $VALUES[$cluster]=$AMOUNT;
                                    $REFS[$cluster]=$KEY;
                                }
                            }
                        }
                        maestro_free($maestro, $res);
                    }
                }
            }
            if($ENABLED){
                if(count($VALUES)>0){
                    if($SIMPLE){
                        $s=array();
                        $s[]=$REFS[array_rand($REFS)];
                    }
                    else{
                        set_time_limit(30);
                        // APPLICAZIONE RICERCA STOCASTICA
                        $GAUGEID=qv_createsysid($maestro);
                        $s=gaugesearch($GAUGEID, array("gauge" => $GAUGE, "exhaustive" => 2, "tolerance" => $TOLERANCE), $VALUES, $REFS);
                        // SALVATAGGIO STATO
                        file_put_contents($path_cambusa."ryque/requests/$GAUGEID.QRY", serialize($QUERIES));
                        file_put_contents($path_cambusa."ryque/requests/$GAUGEID.CLT", serialize($CLUSTERS));
                        file_put_contents($path_cambusa."ryque/requests/$GAUGEID.PRS", serialize($PRESERVES));
                        file_put_contents($path_cambusa."ryque/requests/$GAUGEID.RQS", serialize($REQUESTS));
                        file_put_contents($path_cambusa."ryque/requests/$GAUGEID.RNG", $RANGE);
                        file_put_contents($path_cambusa."ryque/requests/$GAUGEID.QID", $REQUESTID);
                    }
                }
                else{
                    $s=array();
                }
            }
            else{
                if(count($REFS)<=100)
                    $s=$REFS;
                else
                    $s=array_slice($REFS, 0, 100);
            }
        }
        else{
            $QUERIES=unserialize(file_get_contents($path_cambusa."ryque/requests/$GAUGEID.QRY"));
            $CLUSTERS=unserialize(file_get_contents($path_cambusa."ryque/requests/$GAUGEID.CLT"));
            $PRESERVES=unserialize(file_get_contents($path_cambusa."ryque/requests/$GAUGEID.PRS"));
            $REQUESTS=unserialize(file_get_contents($path_cambusa."ryque/requests/$GAUGEID.RQS"));
            $RANGE=file_get_contents($path_cambusa."ryque/requests/$GAUGEID.RNG");
            $REQUESTID=file_get_contents($path_cambusa."ryque/requests/$GAUGEID.QID");
            foreach($QUERIES as $QUERY){
                $QUERYID=$QUERY["QUERYID"];
                $REQUESTID=$REQUESTS[$QUERYID];
                $reqpath=$path_cambusa."ryque/requests/".$REQUESTID;
                $INDEXES[$QUERYID]=file_get_contents($reqpath.".ndx");
                $ROWS[$QUERYID]=array();
            }
            $s=gaugesearch($GAUGEID);
        }
        // PREPARAZIONE DOCUMENTO IN USCITA
        $RESULT=array();
        $ORDERBY=array();
        if(count($s)>0){
            foreach($s as $KEY){
                $QUERYID=$CLUSTERS[$KEY][1];
                $movs=$CLUSTERS[$KEY][2];
                foreach($movs as $ARROWID){
                    $ROWS[$QUERYID][]=$ARROWID;
                }
            }
            foreach($QUERIES as $QUERY){
                $QUERYID=$QUERY["QUERYID"];
                $index=$INDEXES[$QUERYID];
                if($index!=""){
                    $sels=array();
                    $in="'";
                    $rs=array_merge($ROWS[$QUERYID], $PRESERVES[$QUERYID]);
                    foreach($rs as $ARROWID){
                        // RIFERIMENTO NELL'INDICE
                        $p=strpos($index, $ARROWID);
                        if($p!==false){
                            $sels[]=floor($p/($lenkey+1))+1;
                            // ELENCO ORDERBY
                            if($in=="'")
                                $in.=$ARROWID;
                            else
                                $in.="','".$ARROWID;
                        }
                    }
                    $in.="'";
                    $RESULT[$QUERYID]=implode("|", $sels);
                    if($in!="''")
                        $ORDERBY[$QUERYID]="(CASE WHEN SYSID IN ($in) THEN 0 ELSE 1 END)";
                    else
                        $ORDERBY[$QUERYID]="SYSID";
                }
                else{
                    $RESULT[$QUERYID]="";
                    $ORDERBY[$QUERYID]="SYSID";
                }
            }
        }
        else{
            gaugedispose($GAUGEID);
            $GAUGEID="";
        }
        // RIAPRO UNA TRANSAZIONE
        maestro_begin($maestro);
        
        // VARIABILI DI RITORNO
        $babelparams["RESULT"]=$RESULT;
        $babelparams["ORDERBY"]=$ORDERBY;
        $babelparams["GAUGEID"]=$GAUGEID;
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