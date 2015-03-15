<?php
/****************************************************************************
* Name:            rep_pratiche.php                                         *
* Project:         Corsaro - Reporting                                      *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "../../custconfig.php";
$tocambusa="../../".$path_cust2cambusa;
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
        $title="PRATICHE";
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
    // ESEGUO LA QUERY
    maestro_query($maestro, "SELECT * FROM QW_PRATICHE WHERE SYSID IN ($in)", $r);
    for($i=0; $i<count($r); $i++){
        $ID=$r[$i]["SYSID"];
        // INIZIO TABELLA
        $PAPER->begintable(
        '[
            {"d":"Descrizione","w":70,"t":""},
            {"d":"Note", "w":200,"t":""}
        ]'
        );
        
        // TESTATA
        $PAPER->tablerow("<p><span style='font-size:14px;'>".$PAPER->getvalue($r, $i, "DESCRIPTION")."</span></p>", 
                         $PAPER->getvalue($r, $i, "REGISTRY")
        );
        $PAPER->tablerow("<hr>", "<hr>");
        
        // DETTAGLIO ATTIVITA'
        maestro_query($maestro, "SELECT * FROM QW_ATTIVITA WHERE SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$ID') AND CONSISTENCY=0 AND AVAILABILITY<2 AND SCOPE=0 ORDER BY BOWTIME,SYSID", $s);
        for($j=0; $j<count($s); $j++){
            $registry=$PAPER->getvalue($s, $j, "REGISTRY");
            $risposta=$PAPER->getvalue($s, $j, "RISPOSTA");
            $inizio=$PAPER->getvalue($s, $j, "BOWTIME");
            $fine=$PAPER->getvalue($s, $j, "TARGETTIME");
            
            $PAPER->tablerow("<p><b>".$PAPER->getvalue($s, $j, "DESCRIPTION")."</b></p><p>Inizio: ".$PAPER->ldate( $inizio ) ."</p>", $registry);
            $PAPER->tablerow("<p>Fine: ".$PAPER->ldate( $fine ) ."</p>" , $risposta);
        }
        
        // FINE TABELLA
        $PAPER->endtable();

        if($i<count($r)-1)
            $PAPER->pagebreak();
    }

    // FINE DEL DOCUMENTO
    $PAPER->enddocument();

    // RESTITUISCO IL PERCORSO DEL DOCUMENTO
    print $PAPER->pathfile;
}
    
// CHIUDO IL DATABASE
maestro_closedb($maestro);
?>