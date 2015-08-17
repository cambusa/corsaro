<?php 
/****************************************************************************
* Name:            qv_legend_execute.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.70                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryque/ryq_gauge.php";
include_once $path_cambusa."rygeneral/datetime.php";
include_once $path_cambusa."/rygeneral/seeker.php";
include_once $path_cambusa."ryquiver/qv_quivers_add.php";
include_once $path_cambusa."rymaestro/maestro_querylib.php";
include_once $path_applications."ryquiver/qv_legend_infoconfig.php";
include_once $path_applications."ryquiver/legend_seeker.php";
function qv_legend_execute($maestro, $data){
    global $global_quiveruserid, $global_quiverroleid;
    global $babelcode, $babelparams;
    global $path_cambusa, $path_customize, $path_databases, $path_applications;
    global $SEEKER;
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
        
        // CHIUDO LA TRANSAZIONE IN CORSO
        maestro_commit($maestro);
        
        // LEGGO IL LEGEND
        $legend=qv_solverecord($maestro, $data, "QW_LEGEND", "LEGENDID", "", $LEGENDID, "*");
        if($LEGENDID==""){
            $babelcode="QVERR_LEGENDID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la configurazione";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $PROCESSOID=$legend["PROCESSOID"];
        $TOLERANCE=floatval($legend["TOLERANCE"]);
        
        // DETERMINO L'EVENTUALE PRATICA
        if(isset($data["PRATICAID"]))
            $PRATICAID=$data["PRATICAID"];
        else
            $PRATICAID="";

        // LEGGO LE INFO DELLA CONFIGURAZIONE
        $datax=array();
        $datax["LEGENDID"]=$LEGENDID;
        $jret=qv_legend_infoconfig($maestro, $datax);
        unset($datax);
        $INFOLEGEND=$jret["params"];
        $STATOID=$INFOLEGEND["STATOID"];
        $CONTOID=$INFOLEGEND["CONTOID"];
        $GENREID=$INFOLEGEND["GENREID"];
        
        // LEGGO IL SCRIPTID
        $lscript=qv_solverecord($maestro, $data, "QW_LEGENDSCRIPT", "SCRIPTID", "", $SCRIPTID, "SEEKER");
        if($SCRIPTID==""){
            $babelcode="QVERR_SCRIPTID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare lo script";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // RISOLVO IL PERCORSO DELLO SCRIPT
        $pathscript=$lscript["SEEKER"];
        if(strpos($pathscript, "@")===false && strpos($pathscript, ":")===false){
            $pathscript=$path_customize."_legend/".$pathscript;
        }
        else{
            $pathscript=str_replace("@customize/", $path_customize, $pathscript);
            $pathscript=str_replace("@cambusa/", $path_cambusa, $pathscript);
            $pathscript=str_replace("@databases/", $path_databases, $pathscript);
            $pathscript=str_replace("@apps/", $path_applications, $pathscript);
        }
        if(!is_file($pathscript)){
            $babelcode="QVERR_NOSCRIPTFILE";
            $b_params=array();
            $b_pattern="Script inesistente";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // ISTANZIO UN SEEKER
        $SEEKER=new rySeeker();
        $SEEKER->legendid=$LEGENDID;
        $SEEKER->processoid=$PROCESSOID;
        $SEEKER->tolerance=$TOLERANCE;
        $SEEKER->statoid=$STATOID;
        $SEEKER->contoid=$CONTOID;
        $SEEKER->genreid=$GENREID;
        $SEEKER->praticaid=$PRATICAID;
        
        $SEEKER->maestro=&$maestro;
        
        $SEEKER->progressenabled=$PROGRESS;
        $SEEKER->progressblock=$BLOCKSIZE;
        
        // DETERMINO LA TOTALITA' DELLE FRECCE COINVOLTE 
        $QUERIES=$data["QUERIES"];
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
            $SELECT=$INFOLEGEND["QUERIES"][$QUERYID]["LEGENDSELECT"];
            
            $reqpath=$path_cambusa."ryque/requests/".$REQUESTID;
            $index=file_get_contents($reqpath.".ndx");
            $lenkey=$maestro->lenid;
            
            // INZIALIZZO I BAGS
            if(!isset($SEEKER->bags[$BAGNAME])){
                $SEEKER->bags[$BAGNAME]=array();
                $BAG=&$SEEKER->bags[$BAGNAME];
                $BAG["MAXINDEX"]=-1;
                $BAG["TABLE"]=$TABLE;
                $BAG["VIEW"]=$VIEW;
                $BAG["SIGNUM"]=$SIGNUM;
                $BAG["SELECT"]=$SELECT;
                $BAG["WHERE"]=$WHERE;
                $BAG["ARROWS"]=array();
                $BAG["SELECTION"]=array();
                $BAG["DATA"]=array();
                $BAG["PARTITION"]=array();
            }

            if($index!=""){
                // CREO UN VETTORE DI SELEZIONATI
                $SELECTLIST=array();
                if($SELECTION!="" || $INVERT!=0){
                    $v=explode("|", $SELECTION);
                    if($INVERT==0){
                        foreach($v as $i){
                            $SELECTLIST[]=$i-1;
                        }
                    }
                    else{
                        for($i=1; $i<=round(strlen($index)/($lenkey+1)); $i++){
                            if(!in_array($i, $v)){
                                $SELECTLIST[]=$i-1;
                            }
                        }
                    }
                }
                $v=explode("|", $index);
                foreach($v as $i => $ARROWID){
                    if(!isset($SEEKER->indexes[$ARROWID])){
                        $BAG=&$SEEKER->bags[$BAGNAME];

                        // INCREMENTO L'INDICE
                        $MAXINDEX=$BAG["MAXINDEX"]+1;
                        $BAG["MAXINDEX"]=$MAXINDEX;

                        // MEMORIZZO I DETTAGLI
                        $BAG["ARROWS"][$MAXINDEX]=$ARROWID;
                        $BAG["SELECTION"][$MAXINDEX]=in_array($i, $SELECTLIST);
                        $SEEKER->bagnames[$ARROWID]=$BAGNAME;
                        $SEEKER->indexes[$ARROWID]=$MAXINDEX;
                        $SEEKER->business[$ARROWID]=false;
                    }
                }
            }
        }
        unset($index);
        unset($SELECTLIST);

        // CARICO LO SCRIPT
        include_once $pathscript;
        
        if(function_exists("legendMain")){
            // CHIAMO LA MAIN
            if(legendMain()===false){
                $babelcode="QVERR_SCRIPTERR";
                $b_params=array("ERRNUMBER" => $SEEKER->lasterrnumber);
                $b_pattern=$SEEKER->lasterrdescription;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $babelcode="QVERR_NOFUNCTION";
            $b_params=array();
            $b_pattern="La funzione legendMain(\$SEEKER) non è definita";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        // RIAPRO UNA TRANSAZIONE
        maestro_begin($maestro);
        
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
function legend_appendselect(&$SELECT, $FIELD){
    if(strpos($SELECT, $FIELD)===false){
        if($SELECT!=""){
            $SELECT.=",";
        }
        $SELECT.=$FIELD;
    }
}
?>