<?php
/****************************************************************************
* Name:            proiezionesaldi_rep.php                                  *
* Project:         Corsaro - Reporting                                      *
* Version:         1.69                                                     *
* Description:     Arrows Oriented Modeling                                 *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."odsgeneration/classes/OpenOfficeSpreadsheet.class.php";
include_once $path_applications."ryquiver/qv_saldi_proiezione.php";
function custMain($maestro, $data){
    global $babelcode, $babelparams;
    global $path_databases, $path_customize, $safe_extensions, $url_customize;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        $env_strconn="";
        $envtemporary=qv_setting($maestro, "_TEMPENVIRON", "temporary");
        include($path_databases."_environs/".$envtemporary.".php");
        $temporary=$env_strconn;
        
        $tempid=qv_createsysid($maestro);
        
        $doc=new OpenOfficeSpreadsheet($tempid, $temporary);

        $sheet=$doc->addSheet("Saldi");

        $jret=qv_saldi_proiezione($maestro, $data);

        $row=1;
        $col=1;
        $c=$sheet->getCell($col, $row);
        $c->setContent("ESTRAZIONE SALDI");
        $c->setFontSize(18);
        $c->setFontWeight(600);
        $c->setHeight(1500);
        $c->setSpannedCols(5);
        $c->setTextAlign("center");
        $c->setVerticalAlign("middle");
        $c->setColor("#DC143C");
        
        $row=2;
        $col=1;
        $c=$sheet->getCell($col, $row);
        $c->setContent($jret["params"]["TIPOSALDI"]." - ".date("d/m/Y H:i"));
        $c->setFontSize(12);
        $c->setFontWeight(600);
        $c->setHeight(800);
        $c->setSpannedCols(5);
        $c->setVerticalAlign("middle");

        $row=4;
        foreach($jret["params"]["SALDICONTI"] as $CONTO => $SVILUPPO){
            if($row==4){
                $col=1;
                $c=$sheet->getCell($col, $row);
                $c->setContent("CONTI / DATE");
                $c->setBackgroundColor("#0084D1");
                $c->setColor("#F0F0F0");
                $c->setFontWeight(600);
                $c->setWidth(5000);
                $c->setTextAlign("center");
                foreach($SVILUPPO["SALDI"] as $DATE => $SALDO){
                    $col+=1;
                    $c=$sheet->getCell($col, $row);
                    $c->setContent(substr($DATE,0,4)."-".substr($DATE,4,2)."-".substr($DATE,6,2));
                    $c->setBackgroundColor("#0084D1");
                    $c->setColor("#F0F0F0");
                    $c->setFontWeight(600);
                    $c->setWidth(3000);
                    $c->setTextAlign("center");
                }
            }
            $row+=1;
            $col=1;
            $c=$sheet->getCell($col, $row);
            $c->setContent($SVILUPPO["DESCRIPTION"]);
            foreach($SVILUPPO["SALDI"] as $DATE => $SALDO){
                $col+=1;
                $c=$sheet->getCell($col, $row);
                $c->setContent(floatval($SALDO));
                $c->setDecimal(2);
                if(floatval($SALDO)<0){
                    $c->setColor("#FF0000");
                }
            }
        }
        $buff=$doc->save(false);
        $filetmp="$tempid.ods";
        $fp=fopen($temporary.$filetmp, "wb");
        fwrite($fp, $buff);
        fclose($fp);
        
        // VARIABILI DI RITORNO
        $babelparams["ENVIRON"]=$envtemporary;
        $babelparams["PATHNAME"]=$filetmp;
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
    $j["SYSID"]="";
    return $j; //ritorno standard
}    
?>