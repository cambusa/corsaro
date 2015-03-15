<?php
/****************************************************************************
* Name:            elencoattivita_rep.php                                   *
* Project:         Corsaro - Reporting                                      *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "../../../custconfig.php";
$tocambusa="../../../".$path_cust2cambusa;
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
    
$paged=1;
$landscape=1;
$format="A4";

// APRO IL DATABASE
$maestro=maestro_opendb($env, false);

if(qv_validatesession($maestro, $sessionid, "quiver")){

    $PAPER->setformat('{"headerheight":20,"paged":'.$paged.',"pdf":'.$pdf.',"landscape":'.$landscape.',"format":"'.$format.'","environ":"'.$temp_environ.'"}');

    function myheader(){
        global $cust_header;
        global $PAPER;
        $title="ATTIVIT&Agrave;";
        $PAPER->write("<div class='report-company' style='position:absolute;top:0px;left:0px;width:100%;text-align:left;'>".$cust_header."</div>");
        $PAPER->write("<div class='report-time' style='position:absolute;top:0px;left:0px;width:100%;text-align:right;'>".$PAPER->timereport()."</div>");
        $PAPER->write("<div class='report-title' style='position:absolute;font-size:16px;top:25px;left:0px;width:100%;text-align:center;'>$title</div>");
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
    
    // INIZIALIZZO IL TOTALIZZATORE DEGLI ESECUTORI
    $esecutori=array();

    // ESEGUO LA QUERY
    maestro_query($maestro, "SELECT * FROM QW_ATTIVITABROWSER WHERE SYSID IN ($in) ORDER BY TARGETTIME", $r);
    for($i=0; $i<count($r); $i++){
        // INIZIO TABELLA
        $PAPER->begintable(
        '[
            {"d":"Descrizione", "w":70, "t":"="},
            {"d":"Durata", "w":20, "t":""},
            {"d":"Attivit&agrave;", "w":200, "t":"="}
        ]'
        );
        $richiedente=$PAPER->getvalue($r, $i, "BOW");
        $esecutore=$PAPER->getvalue($r, $i, "TARGET");
        $registry=$PAPER->getvalue($r, $i, "REGISTRY");
        $risposta=$PAPER->getvalue($r, $i, "RISPOSTA");
        $scadenza=$PAPER->getvalue($r, $i, "TARGETTIME");
        if($scadenza>=$PAPER->timestamp())
            $col="black";
        else
            $col="red";
        $durata=$PAPER->getvalue($r, $i, "AMOUNT");
        $ore=intval($durata);
        $genere=$PAPER->getvalue($r, $i, "GENREID");
        switch($genere){
        case "0TIMEHOURS0000":
            $durata.=" ore";
            break;
        case "0TIMEDAYS00000":
            $durata.=" giorni";
            $ore*=8;
            break;
        case "0TIMEWEEKS0000":
            $durata.=" settimane";
            $ore*=40;
            break;
        case "0TIMEMONTHS000":
            $durata.=" mesi";
            $ore*=160;
            break;
        case "0TIMEYEARS0000":
            $durata.=" anni";
            $ore*=1800;
            break;
        }
        // TOTALIZZO PER LO SPECCHIETTO
        if(!isset($esecutori[$esecutore])){
            $esecutori[$esecutore]=0;
        }
        $esecutori[$esecutore]+=$ore;
        
        $PAPER->tablerow("<p><b>".$PAPER->getvalue($r, $i, "DESCRIPTION")."</b></p><p>Richiedente:$richiedente</p>", 
                         "<p>".$durata."</p>", 
                         $registry);
        $PAPER->tablerow("<p>Esecutore:$esecutore</p><p>Scadenza: <span style='color:$col;'>".$PAPER->ldate( $scadenza ) ."</span></p>", 
                         "", 
                         $risposta);
        // FINE TABELLA
        $PAPER->endtable();

        //if($i<count($r)-1)
            $PAPER->pagebreak();
    }
    
    $PAPER->write("<div class='subtitle'>Riepilogo<div><br>");

    // INIZIO SPECCHIETTO
    $PAPER->begintable(
    '[
        {"d":"Esecutore", "w":70, "t":"="},
        {"d":"Totale", "w":70, "t":""}
    ]'
    );
    foreach($esecutori as $e => $t){
        $t.=" ore";
        if($t>8){
            $t.=" (".round($t/8,0)." giorni";
            if(($t%8)>0)
                $t.=" e ".($t%8)." ore)";
            else
                $t.=")";
        }
        $PAPER->tablerow($e, $t);
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