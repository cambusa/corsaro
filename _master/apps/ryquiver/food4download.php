<?php
/****************************************************************************
* Name:            food4download.php                                        *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
$tocambusa="../../cambusa/";
include_once $tocambusa."rymaestro/maestro_execlib.php";
include_once "food4_library.php";

if(isset($_GET['env']) && isset($_GET['id'])){
    $env=$_GET['env'];
    $SYSID=ryqEscapize($_GET['id']);

    // APRO IL DATABASE
    $maestro=maestro_opendb($env, false);

    flb_dirattachment($maestro, $dirattachment, $urlattachment);
    maestro_query($maestro, "SELECT * FROM QVFILES WHERE SYSID='$SYSID'", $f);
    if(count($f)==1){
        $SUBPATH=$f[0]["SUBPATH"];
        $IMPORTNAME=$f[0]["IMPORTNAME"];

        $path_parts=pathinfo($IMPORTNAME);
        if(isset($path_parts["extension"]))
            $ext="." . $path_parts["extension"];
        else
            $ext="";
        
        $file=$dirattachment.$SUBPATH.$SYSID.$ext;

        $base=$IMPORTNAME;
        $base=utf8Decode($base);
        $base=html_entity_decode($base);
        $base=str_replace("´", "'", $base);
        
        // ESISTENZA
        if(is_file($file)){
            // DOWNLOAD
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private",false);
            header("Content-Type: application/octet-stream");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=\"".$base."\";" );
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: ".filesize($file));
            header('Connection: close');

            readfile($file);
            
            if(isset($_GET["site"])){
                // GESTIONE STATISTICHE
                $site=$_GET["site"];
                if($site!=""){
                    @file_get_contents(flb_urlquiver()."food4statistics.php?env=$env&site=$site&fileid=$SYSID&user=@");
                }
            }
        }
        else{
            writelog("File\r\n\r\n".$file."\r\n\r\nnot found!");
        }
    }
    else{
        writelog("Id\r\n\r\n".$SYSID."\r\n\r\nnot found!");
    }
    // CHIUDO IL DATABASE
    maestro_closedb($maestro);
}
exit(0);
?>