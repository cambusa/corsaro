<?php
/****************************************************************************
* Name:            ods2array_lib.php                                        *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once "$tocambusa/tbs_us/tbs_class.php";
include_once "$tocambusa/tbs_us/plugins/tbs_plugin_opentbs.php";
include_once "$tocambusa/rygeneral/writelog.php";

function ods2array(&$rows, $ods, $sheet=1){

    if(file_exists($ods)){
    
        $rows=array();

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $TBS->LoadTemplate($ods);
        $buffer=$TBS->Source;
        
        $x=simplexml_load_string($buffer);
        
        $nsOffice=$x->getNamespaces(true);
        $office=$x->children($nsOffice['office']);

        foreach($office->body[0] as $table){
            $nsTable=$table->getNamespaces(true);
            $tables=$table->children($nsTable['table']);
            $tbl=$tables->table;
            foreach($tbl as $t){
                foreach($t->{"table-row"} as $r){
                    $a=array();
                    foreach($r->{"table-cell"} as $c){
                        // NUMERO DI RIPETIZIONI DELLA STESSA CELLA
                        if(isset($c["number-columns-repeated"]))
                            $n=intval($c["number-columns-repeated"]);
                        else
                            $n=1;
                        $nsText=$c->getNamespaces(true);
                        if(isset($nsText['text'])){
                            $text=$c->children($nsText['text']);
                            $y=$text->p[0];
                            if(count($y->a)>0){
                                $h=$y->a[0];
                                for($i=1; $i<=$n; $i++)
                                    $a[]=$h;
                            }
                            else{
                                for($i=1; $i<=$n; $i++)
                                    $a[]=$y;
                            }
                        }
                        else{
                            for($i=1; $i<=$n; $i++)
                                $a[]="";
                        }
                    }
                    $rows[]=$a;
                    unset($a);
                }
                // MI FERMO AL PRIMO FOGLIO
                break;
            }
        }
        return true;
    }
    else{
        $rows=array();
        return false;
    }
}
?>