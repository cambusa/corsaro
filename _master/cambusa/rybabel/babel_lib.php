<?php 
/****************************************************************************
* Name:            babel_lib.php                                            *
* Project:         Cambusa/ryBabel                                          *
* Version:         1.00                                                     *
* Description:     Language localization                                    *
* Copyright (C):   2014  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_execlib.php";

function babeldecode($lang, $codes){
    $r=array();
    try{
        if($lang!="" && $codes!=""){

            // APERTURA DATABASE
            $maestro=maestro_opendb($lang);
            
            if($maestro->conn!==false){
                $elenco=explode("|",$codes);
                foreach($elenco as $code){
                    $sql="SELECT CAPTION FROM BABELITEMS WHERE SYSID='".$code."' OR [:UPPER(NAME)]='".strtoupper($code)."'";
                    maestro_query($maestro, $sql, $s);
                    if(count($s)>0)
                        $r[$code]=$s[0]["CAPTION"];
                    else
                        $r[$code]="";
                }
            }
            
            // CHIUSURA DATABASE
            maestro_closedb($maestro);
        }
    }
    catch(Exception $e){}
    return json_encode($r);
}
?>