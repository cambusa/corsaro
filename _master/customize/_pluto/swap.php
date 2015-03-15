<?php
function plutoMain($DEVELOPER){
    
	$DEVELOPER->sviluppo[]=array("DATA" => "20140101", "NOMINALE" => 10000, "COMMPAG" => 30);
    $DEVELOPER->sviluppo[]=array("DATA" => "20140101", "TASSOINC" => 1.8, "TASSOPAG" => 2.3);
    $DEVELOPER->sviluppo[]=array("DATA" => "20140201", "TASSOINC" => 2.6, "INTINC" => 0, "INTPAG" => 0);
    $DEVELOPER->sviluppo[]=array("DATA" => "20140301", "TASSOINC" => 2.7, "INTINC" => 0, "INTPAG" => 0);

    return true;
}
?>