<?php
include("ryvlad.php");
$VLAD->config('prova.json');
$VLAD->config('
{
    "source":"pippo.txt",
    "trace":"pippo.log"
}
');
$VLAD->bleed();

//$BLOOD->default="#";
//print "<br><br>";
//print $BLOOD("importo");
print "<br><br>";
print $BLOOD->data("importo");
print "<br><br>";
print date("d/m/Y", $BLOOD->data("data"));
print "<br><br><br>";
print $BLOOD->data(1,"subcod");
print "<br><br><br>";
print $BLOOD->count();
print "<br><br><br>";

$BLOOD->set(1, array("NOME" => "Pippo"));
print $BLOOD->data(1, "NOME");
print "<br><br><br>";
//$BLOOD->clear();
//$BLOOD->reset();
var_export($BLOOD->data());


function root_prepare($match){
    print serialize($match);
    print "<br>";

}
function topolino_prepare($match){
    print serialize($match);
    print "<br>";
}

?>