<?php
include("report.php");

if(isset($_GET["pdf"]))
    $pdf=intval($_GET["pdf"]);
else
    $pdf=0;
    
if(isset($_GET["paged"]))
    $paged=intval($_GET["paged"]);
else
    $paged=1;
    
if(isset($_GET["landscape"]))
    $landscape=intval($_GET["landscape"]);
else
    $landscape=0;
    
if(isset($_GET["format"]))
    $format=$_GET["format"];
else
    $format="A4";
    
//$PAPER->setformat('{"headerheight":40,"footerheight":40,"paged":'.$paged.',"pdf":'.$pdf.',"landscape":'.$landscape.',"format":"'.$format.'","tablerowheight":6}');
//$PAPER->setformat('{"headerheight":40,"footerheight":40,"paged":'.$paged.',"pdf":'.$pdf.',"landscape":'.$landscape.',"format":"'.$format.'","file":"d:/temp/p.htm"}');
$PAPER->setformat('{"headerheight":40,"footerheight":40,"paged":'.$paged.',"pdf":'.$pdf.',"landscape":'.$landscape.',"format":"'.$format.'"}');

function myheader(){
    global $PAPER;
    $PAPER->write("<hr>");
    $PAPER->write("INTESTAZIONE");
    $PAPER->write("<br><br><br><br><br><br>");
    $PAPER->write("<hr>");
};
$PAPER->header="myheader";

function myfooter(){
    global $PAPER;
    $PAPER->write("<hr>");
    $PAPER->write("PIEDE");
    $PAPER->write("<br><br><br><br><br><br>");
    $PAPER->write("<hr>");
};
$PAPER->footer="myfooter";

/*
print "Escapizzazione<br/>";
print htmlentities("cipollò <sdfsd>");
print "<br><br>";

print "Data espressa<br/>";
print $PAPER->cdate("20120823");
print "<br><br>";

print "Data odierna<br/>";
print $PAPER->cdate();
print "<br><br>";

print "Data verbosa<br/>";
print $PAPER->ldate("2013-12-04");
print "<br><br>";

print "Giorno settimana<br/>";
print $PAPER->lweek();
print "<br><br>";

print "Numero e decimali espressi<br/>";
print $PAPER->cnumber("12345667.67",4);
print "<br><br>";

print "Decimali non espressi<br/>";
print $PAPER->cnumber("12345667.67234");
print "<br><br>";

print "Numero in lettere<br/>";
print $PAPER->lnumber("512345600.67");
print "<br><br>";
*/

$dati=array();

for($r=0;$r<100;$r++){
    $dati[$r]=array();
    $dati[$r]["DESCRIPTION"]=str_shuffle("Pippo fa le pizze òè°");
    $dati[$r]["DATA"]="2012-11-12";
    $dati[$r]["IMPORTO"]=rand(1000, 10000);
}

$PAPER->begindocument();


for($r=0;$r<100;$r++){
    $PAPER->printblock($PAPER->getvalue($dati, $r, "DESCRIPTION")."<br>");
}

$PAPER->printblock("kfgpskopfkog spdkfg pskofd gskopdfg sio jdfoijsodjgosdfjigsdiofjgsoidfj osijdf oisjdf oisjdofijgsdiofjg soidfjg soidjfg oisd oisadj oiasj doiasjdfi ajsodijf aosidj foaisjd ofaisjd foaisjd ofiasjd ofiajsodfaosid foaisjd foiasjd ofiajs diofajsdoifasodijf asiodj aosidj foaisjd oiajsdijuerhwht oyhj ptokj pryk pot pkspdkogsd<br>");

$PAPER->pagebreak();

$PAPER->begintable(
'[
    {"d":"Descrizione","w":50,"t":""},
    {"d":"Importo","w":40,"t":"2"},
    {"d":"Data","w":30,"t":"/"},
    {"d":"Scelta","w":20,"t":"?"}
]'
);
$PAPER->tablerow("Proviamoci", 12344.43, "2012-11-23",true);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);
$PAPER->tablerow("Proviamoci", 12344.43);
$PAPER->tablerow("Altra riga", 1000234);
$PAPER->tablerow("E poi una terza", 1000234);

$PAPER->endtable();

$PAPER->enddocument();

print "<br>";
print $PAPER->pathfile;

?>