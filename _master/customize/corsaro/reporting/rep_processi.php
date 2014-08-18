<?php
/****************************************************************************
* Name:            rep_processi.php                                         *
* Project:         Corsaro - Reporting                                      *
* Version:         1.00                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2012  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/apps/corsaro/license.html           *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "../../custconfig.php";
$tocambusa="../../".$path_cust2cambusa;
include_once $tocambusa."sysconfig.php";
include_once $tocambusa."mpdf/mpdf.php";
include_once $tocambusa."rypaper/report.php";
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
        $title="PROCESSI";
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
    maestro_query($maestro, "SELECT QW_PROCESSI.SYSID AS SYSID, QW_PROCESSI.DESCRIPTION AS DESCRIPTION, QVQUIVERARROW.ARROWID AS ARROWID FROM QW_PROCESSI INNER JOIN QVQUIVERARROW ON QVQUIVERARROW.QUIVERID=QW_PROCESSI.SYSID WHERE QW_PROCESSI.SYSID IN ($in)", $r);
    for($i=0; $i<count($r); $i++){
        // INIZIO TABELLA
        $PAPER->begintable(
        '[
            {"d":"Descrizione","w":70,"t":""},
            {"d":"Sorgente", "w":70,"t":""},
            {"d":"Transizione", "w":70,"t":""},
            {"d":"Destinazione", "w":70,"t":""}
        ]'
        );

        $SYSID=$r[$i]["SYSID"];
        $ARROWID=$r[$i]["ARROWID"];
        maestro_query($maestro, "SELECT QW_TRANSIZIONI.DESCRIPTION AS DESCRIPTION, OBJBOW.DESCRIPTION AS BOWDESCR, OBJTARGET.DESCRIPTION AS TARGETDESCR FROM QW_TRANSIZIONI LEFT JOIN QVOBJECTS AS OBJBOW ON OBJBOW.SYSID=QW_TRANSIZIONI.BOWID LEFT JOIN QVOBJECTS AS OBJTARGET ON OBJTARGET.SYSID=QW_TRANSIZIONI.TARGETID WHERE QW_TRANSIZIONI.SYSID='$ARROWID'", $s);
        for($j=0; $j<count($s); $j++){
            if($j==0)
                $DESCRIPTION=$PAPER->getvalue($r, $i, "DESCRIPTION");
            else
                $DESCRIPTION="";
             
            $BOWDESCR=$PAPER->getvalue($s, $j, "BOWDESCR");
            $TRANSDESCR=$PAPER->getvalue($s, $j, "DESCRIPTION");
            $TARGETDESCR=$PAPER->getvalue($s, $j, "TARGETDESCR");
            
            $PAPER->tablerow($DESCRIPTION, 
                             $BOWDESCR, 
                             $TRANSDESCR, 
                             $TARGETDESCR
            );
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