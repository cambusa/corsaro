<?php
include("maestro_execlib.php");
include("maestro_querylib.php");
include("../rygeneral/writelog.php");

$maestro=maestro_opendb("acme");

$res=maestro_unbuffered($maestro, "SELECT * FROM qvobjects");
print $maestro->errdescr."<br>";
while( $row=maestro_fetch($maestro, $res) ){
    var_export($row);
    print "<br>";
}

maestro_closedb($maestro);

?>