<?php
/****************************************************************************
* Name:            rycurrencyload.php                                       *
* Project:         Cambusa/sysInstall                                       *
* Version:         1.69                                                     *
* Description:     Cambusa installer                                        *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
set_time_limit(0);

// APERTURA FILE SQL
$pathname="currency.sql";
$fp=fopen($pathname, "w");

$x=simplexml_load_file("currency.xml");

$list=array();

foreach($x->CcyTbl->CcyNtry as $Ntry){
    $descr=str_replace("'", "''", $Ntry->CcyNm);
    $symb=(string)$Ntry->Ccy;
    $dec=$Ntry->CcyMnrUnts;
    if($symb!="EUR" && substr($symb, 0, 1)!="X" && $symb!=""){
        if(!in_array($symb, $list)){
            $list[]=$symb;
            $buff="                    \"INSERT INTO QVGENRES(SYSID,NAME,DESCRIPTION,BREVITY,ROUNDING,TYPOLOGYID,TAG,DELETED) VALUES([:SYSID(0MONEY".$symb."000)], '_MONEY$symb', '$descr', '$symb', $dec, [:SYSID(0MONEY000000)], '', 0)\",\r\n";
            fwrite($fp, $buff);
        }
    }
}
print_r($list);

// CHIUSURA FILE SQL
fclose($fp);
?>