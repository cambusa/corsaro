<?php
/****************************************************************************
* Name:            primanota_rep.php                                        *
* Project:         Corsaro - Reporting                                      *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "../_config.php";
include_once $tocambusa."sysconfig.php";
include_once $tocambusa."rypaper/rypaper.php";
include_once $tocambusa."ryquiver/quiversex.php";

if(isset($_POST["sessionid"]))
    $sessionid=$_POST["sessionid"];
else
    $sessionid="";

if(isset($_POST["env"]))
    $env=$_POST["env"];
else
    $env="";

if(isset($_POST["pdf"]))
    $pdf=intval($_POST["pdf"]);
else
    $pdf=0;
    
if(isset($_POST["keys"]))
    $keys=$_POST["keys"];
else
    $keys=array();

if(isset($_POST["params"]))
    $params=$_POST["params"];
else
    $params=array();

$paged=1;
$landscape=1;
$format="A4";

// APRO IL DATABASE
$maestro=maestro_opendb($env, false);

if(qv_validatesession($maestro, $sessionid, "quiver")){

    $PAPER->setformat('{"headerheight":20,"paged":'.$paged.',"pdf":'.$pdf.',"landscape":'.$landscape.',"format":"'.$format.'","environ":"'.$temp_environ.'"}');

    // SELEZIONE
    if(isset($params["selezione"]))
        $selezione=$params["selezione"];
    else
        $selezione="";
        
    function myheader(){
        global $cust_header;
        global $PAPER;
        global $selezione;
        $title="PRIMA NOTA";
        $PAPER->write("<div class='report-company' style='position:absolute;top:0px;left:0px;width:100%;text-align:left;'>".$cust_header."</div>");
        $PAPER->write("<div class='report-time' style='position:absolute;top:0px;left:0px;width:100%;text-align:right;'>".$PAPER->timereport()."</div>");
        $PAPER->write("<div class='report-title' style='position:absolute;font-size:16px;top:25px;left:0px;width:100%;text-align:center;'>$title</div>");
        $PAPER->write("<div class='report-selection'>$selezione</div>");

    };
    $PAPER->header="myheader";

    // INIZIO DEL DOCUMENTO
    $PAPER->begindocument();

    // QUERY REPERIMENTO DATI
    $in="";
    foreach($keys as $key => $SYSID){
        if($in!="")
            $in.=",";
        $in.="'$SYSID'";
    }

    // RIFERIMENTI
    if(isset($params["riferimenti"]))
        $riferimenti=$params["riferimenti"];
    else
        $riferimenti="";
    
    // INIZIALIZZO IL TOTALIZZATORE
    $conti=array();
    $contidescr=array();
    $divisedescr=array();

    // INIZIO TABELLA
    $PAPER->begintable(
    '[
        {"d":"Descrizione", "w":50, "t":""},
        {"d":"Causale", "w":50, "t":""},
        {"d":"Data Reg.", "w":20, "t":"/"},
        {"d":"Data Val.", "w":20, "t":"/"},
        {"d":"Riferimento", "w":35, "t":""},
        {"d":"Controparte", "w":35, "t":""},
        {"d":"Importo", "w":30, "t":"2"},
        {"d":"Divisa", "w":20, "t":""}
    ]'
    );

    // ESEGUO LA QUERY
    maestro_query($maestro, "SELECT * FROM QW_MOVIMENTIJOIN WHERE SYSID IN ($in) ORDER BY AUXTIME", $r);
    for($i=0; $i<count($r); $i++){
        $bowid=$PAPER->getvalue($r, $i, "BOWID");
        $targetid=$PAPER->getvalue($r, $i, "TARGETID");
        
        $direction=-1;
        if($bowid!=""){
            if(strpos($riferimenti, $bowid)!==false)
                $direction=0;
        }
        if($targetid!=""){
            if(strpos($riferimenti, $targetid)!==false)
                $direction=1;
        }
        if($direction>=0){
            $bow="";
            $target="";
            maestro_query($maestro, "SELECT DESCRIPTION FROM QVOBJECTS WHERE SYSID='$bowid'", $s);
            if(count($s)==1){
                $bow=$s[0]["DESCRIPTION"];
            }
            unset($s);
            maestro_query($maestro, "SELECT DESCRIPTION FROM QVOBJECTS WHERE SYSID='$targetid'", $s);
            if(count($s)==1){
                $target=$s[0]["DESCRIPTION"];
            }
            unset($s);
            $description=$PAPER->getvalue($r, $i, "DESCRIPTION");
            $causale=$PAPER->getvalue($r, $i, "MOTIVE");
            $datareg=$PAPER->getvalue($r, $i, "AUXTIME");
            $databow=$PAPER->getvalue($r, $i, "BOWTIME");
            $datatarget=$PAPER->getvalue($r, $i, "TARGETTIME");
            $importo=floatval($PAPER->getvalue($r, $i, "AMOUNT"));
            
            if($direction==0){
                $importo=-$importo;
                $dataval=$databow;
                $rifid=$bowid;
                $conto=$bow;
                $contro=$target;
                $col="maroon";
            }
            else{
                $dataval=$datatarget;
                $rifid=$targetid;
                $conto=$target;
                $contro=$bow;
                $col="black";
            }
            $divisaid=$PAPER->getvalue($r, $i, "GENREID");
            $divisa=$PAPER->getvalue($r, $i, "GENRE");
            // TOTALIZZO PER LO SPECCHIETTO
            if(!isset($conti[$rifid])){
                $conti[$rifid]=array(); // Vettore di totalizzazioni per divisa
                $contidescr[$rifid]=$conto;
            }
            if(!isset($conti[$rifid][$divisaid])){
                $conti[$rifid][$divisaid]=0;
                $divisedescr[$divisaid]=$divisa;
            }
            $conti[$rifid][$divisaid]+=$importo;
            
            $PAPER->onceformat('{"tr":{"color":"'.$col.'"}}');
            $PAPER->tablerow($description,
                             $causale,
                             $datareg,
                             $dataval,
                             $conto,
                             $contro,
                             $importo,
                             $divisa
            );
        }
    }
    
    // FINE TABELLA
    $PAPER->endtable();
    
    $PAPER->pagebreak();

    $PAPER->write("<br><br><div class='subtitle'>Riepilogo<div><br>");

    // INIZIO SPECCHIETTO
    $PAPER->begintable(
    '[
        {"d":"Conto", "w":70, "t":""},
        {"d":"Divisa", "w":30, "t":""},
        {"d":"Totale", "w":40, "t":"2"}
    ]'
    );
    foreach($conti as $id => $tot){
        $divise=$conti[$rifid];
        foreach($divise as $div => $imp){
            $PAPER->tablerow($contidescr[$id], $divisedescr[$div], $imp);
        }
    }
    
    // FINE SPECCHIETTO
    $PAPER->endtable();

    // FINE DEL DOCUMENTO
    $PAPER->enddocument();

    // RESTITUISCO IL PERCORSO DEL DOCUMENTO
    print $PAPER->pathfile;
}
    
// CHIUDO IL DATABASE
maestro_closedb($maestro);
?>