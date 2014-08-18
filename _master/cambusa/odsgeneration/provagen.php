<?php

include("../rygeneral/writelog.php");
include("classes/OpenOfficeSpreadsheet.class.php");

$doc=new OpenOfficeSpreadsheet("prova");

$sheet=$doc->addSheet("Pìppo");

$sheet->setCellContent("Pippò iiuhiuh",1,1);
$sheet->setCellContent("2013-09-02",1,2);
$sheet->setCellContent(-23423.34 ,1,3);

$sheet->setCellContent(23423.34 ,1,4);
$sheet->setCellBackgroundColor("#0084d1",1,4);

$c=$sheet->getCell(2,2);
$c->setBackgroundColor("#0084d1");
$c->setContent(32452345.2345,false);


$doc->output();
//$doc->save();

?>