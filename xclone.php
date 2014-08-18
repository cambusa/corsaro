<?php
include_once "xcopy.php";
include_once "xpassword.php";
if(isset($_POST["project"]) && isset($_POST["password"])){
    $password=$_POST["password"];
    if($password==$_password){
        $project=$_POST["project"];
        $project=strtolower($project);
        $project=preg_replace("/[^_a-z0-9]/i", "", $project);
        if($project!="_master"){
            xcopy("./_master", "./$project", "");

            // RINOMINO IL DESCRITTORE DEL DATABASE ACME
            rename("$project/databases/_environs/acme.php", "$project/databases/_environs/$project.php");
        }
    }
}
?>