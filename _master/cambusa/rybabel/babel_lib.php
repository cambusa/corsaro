<?php 
/****************************************************************************
* Name:            babel_lib.php                                            *
* Project:         Cambusa/ryBabel                                          *
* Version:         1.69                                                     *
* Description:     Language localization                                    *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_execlib.php";

function babeldecode($lang, $codes){
    global $config_selflearning;
    $r=array();
    try{
        if($lang!="" && $codes!=""){

            // APERTURA DATABASE
            $maestro=maestro_opendb($lang);
            
            // RICERCA TRADUZIONI
            $elenco=explode("|",strtoupper($codes));
            if($maestro->conn!==false){
                $elencoin="'".str_replace("|", "','", strtoupper($codes))."'";
                $sql="SELECT NAME,CAPTION FROM BABELITEMS WHERE [:UPPER(NAME)] IN ($elencoin)";
                maestro_query($maestro, $sql, $s);
                foreach($s as $row){
                    if(($i=array_search($row["NAME"], $elenco))!==false){
                        $r[ $elenco[$i] ]=$row["CAPTION"];
                    }
                }
            }
            
            // CHIUSURA DATABASE
            maestro_closedb($maestro);
            
            // AUTOAPPRENDIMENTO
            if($config_selflearning!=""){
                $self=array();
                // ELENCO GLI INESISTENTI
                foreach($elenco as $k){
                    if(!isset($r[$k]))
                        $self[]=$k;
                }
                if(count($self)>0){
                    $r["___SELFLEARNING"]=implode("|", $self);
                }
            }

            // RESTITUISCO COMUNQUE QUALCOSA ANCHE PER LE MANCATE TRADUZIONI
            foreach($elenco as $k){
                if(!isset($r[$k]))
                    $r[$k]="";
            }
        }
    }
    catch(Exception $e){}
    array_walk_recursive($r, "babel_escapize");
    return json_encode($r);
}
function babel_escapize(&$value){
    if($value!=""){
        if(!mb_check_encoding($value, "UTF-8")){
            // CI SONO CARATTERI NON UNICODE
            $value=utf8_encode($value);
        }
    }
}
?>