<?php
/****************************************************************************
* Name:            rep_motivetypes.php                                      *
* Project:         Corsaro - Reporting                                      *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
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
        $PAPER->write("<div class='report-company' style='position:absolute;top:0px;left:0px;width:100%;text-align:left;'>".$cust_header."</div>");
        $PAPER->write("<div class='report-time' style='position:absolute;top:0px;left:0px;width:100%;text-align:right;'>".$PAPER->timereport()."</div>");
        $PAPER->write("<div class='report-title' style='position:absolute;font-size:16px;top:25px;left:0px;width:100%;text-align:center;'>TIPI MOTIVO</div>");
    };
    $PAPER->header="myheader";

    // INIZIO DEL DOCUMENTO
    $PAPER->begindocument();

    // INIZIO TABELLA
    $PAPER->begintable(
    '[
        {"d":"Nome","w":40,"t":""},
        {"d":"Descrizione","w":90,"t":""},
        {"d":"Vista","w":40,"t":""},
        {"d":"Tabella","w":40,"t":""},
        {"d":"Gestione","w":20,"t":""},
        {"d":"Estensione","w":40,"t":""}
    ]'
    );

    // QUERY REPERIMENTO DATI
    $in="";
    foreach($keys as $key => $SYSID){
        if($in!="")
            $in.=",";
        $in.="'$SYSID'";
    }
    // ESEGUO LA QUERY
    maestro_query($maestro, "SELECT * FROM QVMOTIVETYPES WHERE SYSID IN ($in)", $r);
    for($i=0; $i<count($r); $i++){
        $SYSID=$r[$i]["SYSID"];
        maestro_query($maestro, "SELECT FIELDNAME FROM QVMOTIVEVIEWS WHERE TYPOLOGYID='$SYSID'", $d);
        if(count($d)>0)
            $FIELDNAME=$PAPER->getvalue($d, 0, "FIELDNAME");
        else
            $FIELDNAME="";
        $PAPER->tablerow($PAPER->getvalue($r, $i, "NAME"), 
                         $PAPER->getvalue($r, $i, "DESCRIPTION"), 
                         $PAPER->getvalue($r, $i, "VIEWNAME"), 
                         $PAPER->getvalue($r, $i, "TABLENAME"), 
                         $PAPER->cboolean($r[$i]["DELETABLE"]),
                         $FIELDNAME
        );
        for($j=1; $j<count($d); $j++){
            $PAPER->tablerow("", "....................", "....................", "....................", "....", $PAPER->getvalue($d, $j, "FIELDNAME"));
        }
    }

    // FINE TABELLA
    $PAPER->endtable();

    // FINE DEL DOCUMENTO
    $PAPER->enddocument();

    // RESTITUISCO IL PERCORSO DEL DOCUMENTO
    print $PAPER->pathfile;
}
    
// CHIUDO IL DATABASE
maestro_closedb($maestro);
?>