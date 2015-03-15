<?php
/****************************************************************************
* Name:            ryq_export.php                                           *
* Project:         Cambusa/ryQue                                            *
* Version:         1.69                                                     *
* Description:     Lightweight access to databases                          *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."odsgeneration/classes/OpenOfficeSpreadsheet.class.php";
include_once $tocambusa."ryquiver/quiversex.php";

$ret="0Unknown exception";

try{
    if(isset($_POST["reqid"]))
        $reqid=$_POST["reqid"];
    else
        $reqid="";

    if(isset($_POST["columns"])){
        $columns=$_POST["columns"];
    }
    else{
        $columns=array( 
            "id" => array("SYSID"),
            "caption" => array("SYSID"),
            "type" => array(""),
        );
    }
    $visibles=array();

    if(isset($_POST["checked"]))
        $checked=$_POST["checked"];
    else
        $checked="";
        
    if(isset($_POST["invert"]))
        $invert=(boolean)intval($_POST["invert"]);
    else
        $invert=false;

    if(isset($_POST["clause"]))
        $clause=$_POST["clause"];
    else
        $clause="";
        
    $filereq="requests/".$reqid.".req";
    if(is_file($filereq)){
        $env=file_get_contents("requests/".$reqid.".req");
        $select=file_get_contents("requests/".$reqid.".slt");
        $from=file_get_contents("requests/".$reqid.".tbl");
        
        // APRO IL DATABASE
        $maestro=maestro_opendb($env, false);

        // VERIFICO IL BUON ESITO DELL'APERTURA
        if($maestro->conn!==false){

            // GESTIONE VINCOLO
            $more="";
            if(is_array($clause)){
                if($maestro->provider!="oracle"){
                    foreach($clause as $key => $value)
                        $more.=" AND $key='$value'";
                }
                else{
                    foreach($clause as $key => $value)
                        $more.=" AND $key=:$key";
                }
            }
            
            $env_strconn="";
            $envtemporary=qv_setting($maestro, "_TEMPENVIRON", "temporary");
            include($path_databases."_environs/".$envtemporary.".php");
            $temporary=$env_strconn;
            
            $tempid=qv_createsysid($maestro);
            
            $doc=new OpenOfficeSpreadsheet($tempid, $temporary);

            $sheet=$doc->addSheet("Estrazione");
            
            $reqpath=$tocambusa."ryque/requests/".$reqid;
            $index=file_get_contents($reqpath.".ndx");
            $list=explode("|", $index);
            $sels=explode("|", $checked);
            
            $row=1;
            $col=1;
            $c=$sheet->getCell($col, $row);
            $c->setContent("SYSID");
            $c->setWidth(4000);
            $c->setBackgroundColor("#0084D1");
            $c->setColor("#F0F0F0");
            $c->setFontWeight(600);
            $c->setTextAlign("center");
            foreach($columns["id"] as $i => $id){
                $dim=intval($columns["dim"][$i]);
                if($dim>0){
                    $col+=1;
                    $c=$sheet->getCell($col, $row);
                    $value=$columns["caption"][$i];
                    if($value==""){
                        $value=$id;
                    }
                    $c->setContent(_inputUTF8($value));
                    $c->setWidth(30*$dim+20);
                    $c->setBackgroundColor("#0084D1");
                    $c->setColor("#F0F0F0");
                    $c->setFontWeight(600);
                    $c->setTextAlign("center");
                    $visibles[$i]=true;
                }
                else{
                    $visibles[$i]=false;
                }
            }
            foreach($list as $i => $SYSID){
                $in=in_array($i+1, $sels);
                if($in!=$invert || $checked==""){
                    maestro_query($maestro, "SELECT $select FROM $from WHERE SYSID='$SYSID' $more", $r);
                    if(count($r)==1){
                        $row+=1;
                        $col=1;
                        $c=$sheet->getCell($col, $row);
                        $c->setContent($SYSID);
                        foreach($columns["id"] as $i => $id){
                            if($visibles[$i]){
                                $col+=1;
                                $c=$sheet->getCell($col, $row);
                                if(isset($r[0][$id]))
                                    $value=$r[0][$id];
                                else
                                    $value="";
                                $c->setContent(_inputUTF8($value));
                                if(is_numeric($columns["type"][$i])){
                                    $dec=intval($columns["type"][$i]);
                                    $c->setDecimal($dec);
                                }
                            }
                        }
                    }
                }
            }
            // SCRITTURA DEL DOCUMENTO
            $buff=$doc->save(false);
            $filetmp=$temporary."$tempid.ods";
            $fp=fopen($filetmp, "wb");
            fwrite($fp, $buff);
            fclose($fp);
            
            // RESTITUZIONE DEL PERCORSO
            $ret="1".$filetmp;
        }
        else{
            throw new Exception( $maestro->errdescr );
        }
        // CHIUDO IL DATABASE
        maestro_closedb($maestro);
        
    }
    else{
        throw new Exception( "Invalid protocol ID" );
    }
}
catch(Exception $e){
    $ret="0".$e->getMessage();
}
print $ret;

function _inputUTF8($v){
    if($v!=""){
        $v=html_entity_decode(utf8_decode($v));
    }
    return $v;
}
?>