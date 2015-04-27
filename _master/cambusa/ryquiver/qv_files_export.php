<?php 
/****************************************************************************
* Name:            qv_files_export.php                                      *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverfil.php";
include_once $path_cambusa."rygeneral/format.php";
function qv_files_export($maestro, $data){
    global $babelcode, $babelparams;
    global $path_cambusa, $path_applications, $path_customize;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
        $infoenv=qv_environs($maestro);
        $dirtemp=$infoenv["dirtemp"];
        $dirattach=$infoenv["dirattach"];
        $envtemp=$infoenv["envtemp"];
        
        if(isset($data["SYSID"])){
            // INDIVIDUAZIONE TRAMITE SYSID
            $SYSID=ryqEscapize($data["SYSID"]);
            $where="SYSID='$SYSID'";
        }
        elseif(isset($data["NAME"])){
            // INDIVIDUAZIONE TRAMITE NOME
            $NAME=ryqEscapize($data["NAME"], 50);
            $where="[:UPPER(NAME)]='".strtoupper($NAME)."'";
        }
        else{
            $babelcode="QVERR_NODATA";
            $b_params=array();
            $b_pattern="Dati insufficienti";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // EVENTUALE MERGING
        if(isset($data["MERGE"])){
            $mergeparams=$data["MERGE"];
            $merge=array();
            $mergedata=array();

            // DATI APPLICAZIONE
            $inc=$path_applications."ryquiver/appfilemerge.php";
            if(is_file($inc)){
                include_once $inc;
                $funct="appfilemerge";
                if(function_exists($funct)){
                    $funct($maestro, $mergeparams, $merge);
                }
            }
            
            // DATI PERSONALIZZATI
            $inc=$path_customize."ryquiver/custfilemerge.php";
            if(is_file($inc)){
                include_once $inc;
                $funct="custfilemerge";
                if(function_exists($funct)){
                    $funct($maestro, $mergeparams, $merge);
                }
            }

            // RISOLUZIONE QUERY
            foreach($merge as $space => $d){
                if(isset($d["array"])){
                    $r=$d["array"];
                }
                elseif(isset($d["sql"])){
                    maestro_query($maestro, $d["sql"], $r);
                }
                qv_file_merge($r);
                $mergedata[$space]=$r;
                unset($r);
            }
            array_walk_recursive($mergedata, "qv_escapizeUTF8");
        }

        // FIRMA DIGITALE
        $sign=false;
        if(isset($data["SIGNATURE"])){
            if(intval($data["SIGNATURE"])){
                $sign=true;
                include_once $path_cambusa."rygeneral/signature.php";
            }
        }
        
        maestro_query($maestro,"SELECT SYSID,NAME,SUBPATH,IMPORTNAME FROM QVFILES WHERE $where",$r);
        if(count($r)==1){
            $SYSID=$r[0]["SYSID"];
            $NAME=$r[0]["NAME"];
            $SUBPATH=$r[0]["SUBPATH"];
            $IMPORTNAME=$r[0]["IMPORTNAME"];
            $TEMPID=qv_createsysid($maestro);
            $path_parts=pathinfo($dirtemp.$IMPORTNAME);
            if(isset($path_parts["extension"]))
                $ext="." . $path_parts["extension"];
            else
                $ext="";
            $uext=strtoupper($ext);
            if(isset($mergedata)){
                // CONTROLLO CHE L'ESTENSIONE SIA COMPATIBILE CON IL MERGING
                if(strpos("|.ODT|.ODS|.DOCX|.XLSX|.HTM|.HTML|.PHT|.TXT|", "|".$uext."|" )===false){
                    unset($mergedata);
                }
            }
            $filetmp=$dirtemp.$TEMPID.$ext;
            if(is_file($dirattach.$SUBPATH.$SYSID.$ext)){
                if(isset($mergedata)){
                    include_once $path_cambusa."tbs_us/tbs_class.php";
                    include_once $path_cambusa."tbs_us/plugins/tbs_plugin_opentbs.php";

                    $TBS=new clsTinyButStrong;
                    if(strpos("|.ODT|.ODS|.DOCX|.XLSX|", "|".$uext."|" )!==false){
                        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
                    }
                    if(strpos("|.HTM|.HTML|.PHT|", "|".$uext."|" )===false){
                        array_walk_recursive($mergedata, "qv_striptags");
                    }
                    $TBS->LoadTemplate($dirattach.$SUBPATH.$SYSID.$ext);
                    foreach($mergedata as $space => $arr){
                        $TBS->NoErr=true;
                        $TBS->MergeBlock($space, $arr);
                    }
                    if(strpos("|.ODT|.ODS|.DOCX|.XLSX|", "|".$uext."|" )!==false){
                        $TBS->Show(OPENTBS_FILE, $filetmp);
                    }
                    else{
                        $buff=html_entity_decode($TBS->Source);
                        $fp=fopen($filetmp, "wb");
                        fwrite($fp, $buff);
                        fclose($fp);
                    }
                    // TRASFORAMZIONE IN PDF
                    if(strtoupper($ext)==".PHT"){
                        $buffer=utf8_encode(file_get_contents($filetmp));
                        $filetmp=substr($filetmp, 0, -3)."pdf";
                        qv_file_pdfoutput($filetmp, $buffer);
                    }
                    // FIRMA ELETTRONICA
                    if($sign){
                        $filetmp=signature_p7m($filetmp);
                    }
                    $babelparams["EXPORT"]=basename($filetmp);
                }
                else{
                    if(@copy($dirattach.$SUBPATH.$SYSID.$ext, $filetmp)){
                        // TRASFORAMZIONE IN PDF
                        if(strtoupper($ext)==".PHT"){
                            $buffer=utf8_encode(file_get_contents($filetmp));
                            $filetmp=substr($filetmp, 0, -3)."pdf";
                            qv_file_pdfoutput($filetmp, $buffer);
                        }
                        if($sign){
                            $filetmp=signature_p7m($filetmp);
                        }
                        $babelparams["EXPORT"]=basename($filetmp);
                    }
                    else{
                        $babelcode="QVERR_EXPORTFAILED";
                        $b_params=array("NAME" => $NAME);
                        $b_pattern="Export del file [{1}] fallito";
                        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                    }
                }
            }
            else{
                $babelcode="QVERR_EXPORTNOFILE";
                $b_params=array("NAME" => $NAME);
                $b_pattern="Il file [{1}] non esiste";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            $babelparams["ENVIRON"]=$envtemp;
        }
        else{
            $babelcode="QVERR_NOFILE";
            $b_params=array("SYSID" => $SYSID, "table" => "QVFILES");
            $b_pattern="File non trovato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
    }
    catch(Exception $e){
        $success=0;
        $message=$e->getMessage();
    }
    // USCITA JSON
    $j=array();
    $j["success"]=$success;
    $j["code"]=$babelcode;
    $j["params"]=$babelparams;
    $j["message"]=$message;
    $j["SYSID"]=$SYSID;
    return $j; //ritorno standard
}
function qv_file_merge(&$r){
    foreach($r as $ind => &$row){
        foreach($row as $key => &$value){
            // SE E' UNA DATA LA NORMALIZZO
            if(preg_match("/^\d\d\d\d-\d\d-\d\d( |T)?(\d\d)?/", $value)==1 ){
                $value=substr($value,8,2)."/".substr($value,5,2)."/".substr($value,0,4);
            }
            // SE E' UN DOCUMENTO JSON LO SCOMPATTO
            if($key=="MOREDATA"){
                if(substr($value, 0, 1)=="{"){
                    $MOREDATA=json_decode($value, true);
                    foreach($MOREDATA as $k => $v){
                        $r[$ind][$k]=$v;
                    }
                }
            }
        }
    }
}
function qv_file_mergenum(&$r, $col, $numdec){
    foreach($r as $ind => &$row){
        $row[$col]=formatta_numero($row[$col], $numdec);
    }
}
function qv_file_pdfoutput(&$filename, $content, $orientation="P"){
    global $path_cambusa;
    try{
        require_once $path_cambusa."html2pdf/html2pdf.class.php";
        $objpdf=new HTML2PDF($orientation, "A4", "en", true, "UTF-8", array(12.7, 12.7, 12.7, 12.7));
        
        if(strpos($content,"</page>")===false){
            $content=preg_replace("@<!DOCTYPE html>@i", "", $content);
            $content=preg_replace("@<head>@i", "", $content);
            $content=preg_replace("@</head>@i", "", $content);
            $content=preg_replace("@</?meta[^>]*>@i", "", $content);
            $content=preg_replace("@<title>[^<]*</title>@i", "", $content);
            $content=preg_replace("@<body[^>]*>@i", "", $content);
            $content=preg_replace("@</body>@i", "", $content);
            $content=preg_replace("@<html>@i", "", $content);
            $content=preg_replace("@</html>@i", "", $content);
            $content="<page>".$content."</page>";
        }
        $objpdf->WriteHTML($content);
        $objpdf->Output($filename, "F");
    }
    catch(HTML2PDF_exception $e) {
        $filename.=".txt";
        $fp=fopen($filename, "w");
        fwrite($fp, $e);
        fclose($fp);
        writelog($content);
    }
}
?>