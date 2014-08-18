<?php
include_once "xcopy.php";
include_once "xpassword.php";
$counter=0;
if(isset($_POST["project"]) && isset($_POST["password"])){
    $password=$_POST["password"];
    if($password==$_password){
        $project=$_POST["project"];
        $project=strtolower($project);
        $project=preg_replace("/[^_a-z0-9]/i", "", $project);
        if($project!="_master"){
            $counter=xcopy("./_master", "./$project", "counter");
        }
    }
    else{
        $counter=-1;
    }
}
print $counter;
?>