<?php 
/****************************************************************************
* Name:            ryq_clean.php                                            *
* Project:         Cambusa/ryQue                                            *
* Version:         1.00                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
// ELIMINO LE EVENTUALI RICHIESTE SCADUTE
if(is_file("requests/.req"))
    @unlink("requests/.req");
if(is_file("requests/.slt"))
    @unlink("requests/.slt");
if(is_file("requests/.tbl"))
    @unlink("requests/.tbl");
if(is_file("requests/.ndx"))
    @unlink("requests/.ndx");
$sec=24*60*60;
$d=glob("requests/*.req");
foreach ($d as $filename){
    try{
        if (time()-@fileatime($filename)>$sec){
            $id=substr(basename($filename),0,-4);
            @unlink("requests/".$id.".req");
            @unlink("requests/".$id.".slt");
            @unlink("requests/".$id.".tbl");
            @unlink("requests/".$id.".ndx");
        }
    }
    catch(Exception $e){ }
}
$d=glob("requests/*.sts");
foreach ($d as $filename){
    try{
        if (time()-@fileatime($filename)>$sec){
            $id=substr(basename($filename),0,-4);
            @unlink("requests/".$id.".sts");
            @unlink("requests/".$id.".sto");
            @unlink("requests/".$id.".err");
        }
    }
    catch(Exception $e){ }
}
?>