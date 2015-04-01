<?php
/****************************************************************************
* Name:            zipper.php                                               *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function zipFolder($dir, $zipname){
    $zip = new ZipArchive();
    $ret=$zip->open( $zipname , ZipArchive::CREATE || ZIPARCHIVE::OVERWRITE );
    if($ret===true){
        $base=substr($dir, 0, strrpos($dir,"/")+1);
        foreach(glob($dir.'/*') as $file){
            if(is_dir($file))
                zipAddFolder($zip, $file, $base);
            else
                $zip->addFile($file, str_replace($base, '', $file));
        }
        $zip->close();
    }
    unset($zip);
    return $ret;
}
function zipAddFolder($zip, $dir, $base){
    $zip->addEmptyDir(str_replace($base, '', $dir));
    foreach(glob($dir.'/*') as $file){
        if(is_dir($file))
            zipAddFolder($zip, $file, $base);
        else
            $zip->addFile($file, str_replace($base, '', $file));
    }
}
?>