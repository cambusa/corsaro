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
include_once "_config.php";
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
    // ESEGUO LA QUERY
    maestro_query($maestro, "SELECT * FROM QW_ATTIVITA WHERE SYSID IN ($in) ORDER BY TARGETTIME", $r);
    for($i=0; $i<count($r); $i++){
        // INIZIO TABELLA
        $PAPER->begintable(
        '[
            {"d":"Descrizione","w":70,"t":""},
            {"d":"Richiesta", "w":200,"t":""}
        ]'
        );
        $registry=$PAPER->getvalue($r, $i, "REGISTRY");
        $risposta=$PAPER->getvalue($r, $i, "RISPOSTA");
        $scadenza=$PAPER->getvalue($r, $i, "TARGETTIME");
        if($scadenza>=$PAPER->timestamp())
            $col="black";
        else
            $col="red";
        
        $PAPER->tablerow("<p><b>".$PAPER->getvalue($r, $i, "DESCRIPTION")."</b></p>", $registry);
        $PAPER->tablerow("<p>Scadenza: <span style='color:$col;'>".$PAPER->ldate( $scadenza ) ."</span></p>" , $risposta);
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