<?php 
/****************************************************************************
* Name:            ryq_close.php                                            *
* Project:         Cambusa/ryQue                                            *
* Version:         1.00                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "../rygeneral/writelog.php";
try{
    if(isset($_POST["reqid"])){
        $reqid=$_POST["reqid"];
        if(is_file("requests/".$reqid.".req"))
            unlink("requests/".$reqid.".req");
        if(is_file("requests/".$reqid.".slt"))
            unlink("requests/".$reqid.".slt");
        if(is_file("requests/".$reqid.".tbl"))
            unlink("requests/".$reqid.".tbl");
        if(is_file("requests/".$reqid.".ndx"))
            unlink("requests/".$reqid.".ndx");
        // file relativi all'algoritmo zero
        if(is_file("requests/".$reqid.".sts")){
            unlink("requests/".$reqid.".sts");
            if(is_file("requests/".$reqid.".sto"))
                unlink("requests/".$reqid.".sto");
            if(is_file("requests/".$reqid.".err"))
                unlink("requests/".$reqid.".err");
        }
    }
}
catch(Exception $e){
    writelog("Problemi in chiusura $reqid:\n".$e->getMessage());
}
print "DONE";
?>