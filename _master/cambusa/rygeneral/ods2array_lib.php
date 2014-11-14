<?php
/****************************************************************************
* Name:            ods2array_lib.php                                        *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.00                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once "$tocambusa/tbs_us/tbs_class.php";
include_once "$tocambusa/tbs_us/plugins/tbs_plugin_opentbs.php";

function ods2array(&$rows, $ods, $sheet=1){

    if(file_exists($ods)){

        $TBS = new clsTinyButStrong;
        $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);

        $TBS->LoadTemplate($ods);
        $buffer=$TBS->Source;

        // PRENDO SOLO IL PRIMO FOGLIO
        if(floatval(phpversion())>=5.3)
            preg_match("@<table:table[^\\x01]+?</table:table>@", $buffer, $m);
        else
            preg_match("@<table:table[^\\x01]+</table:table>@", $buffer, $m);
        $buffer=$m[0];
        
        // INDIVIDUO RIGHE E COLONNE
        $buffer=preg_replace("@</text:p></table:table-cell></table:table-row></table:table>@", "", $buffer);
        $buffer=preg_replace("@</text:p></table:table-cell></table:table-row>@", "######", $buffer);
        $buffer=preg_replace("@</table:table-row>@", "######", $buffer);
        $buffer=preg_replace("@</text:p>@", "@@@@@@", $buffer);
        $buffer=preg_replace("@<table:table-cell [^>]*/>@", " @@@@@@", $buffer);    // CELLE VUOTE
        $buffer=strip_tags($buffer);
        
        // COSTRUISCO LA MATRICE
        $rows=explode("######", $buffer);
        foreach($rows as &$row){
            $row=explode("@@@@@@", $row);
        }
        return true;
    }
    else{
        $rows=array();
        return false;
    }
}
?>