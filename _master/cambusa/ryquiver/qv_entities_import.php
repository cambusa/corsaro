<?php 
/****************************************************************************
* Name:            qv_entities_import.php                                   *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverfil.php";
include_once "qv_genres_insert.php";
include_once "qv_objects_insert.php";
include_once "qv_motives_insert.php";
include_once "qv_arrows_insert.php";
include_once "qv_quivers_insert.php";
include_once "qv_quivers_add.php";
include_once "qv_selections_add.php";
include_once "qv_selections_flags.php";
include_once "qv_genres_update.php";
include_once "qv_objects_update.php";
include_once "qv_motives_update.php";
include_once "qv_arrows_update.php";
include_once "qv_quivers_update.php";
function qv_entities_import($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
        $infoenv=qv_environs($maestro);
        $dirtemp=$infoenv["dirtemp"];
        $dirattach=$infoenv["dirattach"];
        
        // CARICO LA STRUTTURA DEL DATABASE
        $maestro->loadinfo();
        
        if(isset($data["PATHFILE"])){
            $PATHFILE=$dirtemp.$data["PATHFILE"];
        }
        else{
            $babelcode="QVERR_NODATA";
            $b_params=array();
            $b_pattern="Dati insufficienti";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        if( file_exists($PATHFILE) ){
            $entity=unserialize(file_get_contents($PATHFILE));
            @unlink($PATHFILE);
        }
        else{
            $babelcode="QVERR_NOFILE";
            $b_params=array();
            $b_pattern="File da importare inesistente";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // INIZIALIZZO IL PIANO DELLE SOSTITUZIONI
        $piano=array();
        $delayed=array();
        
        foreach($entity["program"] as $stat){
            $table=$stat["table"];
            $extension=$stat["extension"];
            $datax=$stat["data"];
            $datay=array();
            $inskey="";
            for($f=0; $f<=1; $f++){
                if($f==0){
                    $actualtable=$table;
                }
                else{
                    if($extension!="")
                        $actualtable=$extension;
                    else
                        break;
                }
                $fields=$maestro->infobase->{$actualtable}->fields;
                foreach($fields as $field => $attr){
                    if(isset($datax[$field])){
                        switch($attr->type){
                        case "SYSID":
                            $vl=$datax[$field];
                            if(preg_match("/\[:SYSID\(([0-9A-Z]+)\)\]/i", $vl, $m)){
                                $key="K_".$m[1];
                                if($field!="SYSID"){
                                    if(isset($piano[$key])){
                                        $vl=$piano[$key];
                                    }
                                    else{
                                        $vl=qv_createsysid($maestro);
                                        $piano[$key]=$vl;
                                    }
                                }
                                else{
                                    $inskey=$key;
                                }
                            }
                            elseif(preg_match("/\[>SYSID\(([0-9A-Z]+)\)\]/i", $vl, $m)){
                                $key="K_".$m[1];
                                if($field!="SYSID"){
                                    $delayed[]=array("table" => $table, "sysid" => $inskey, "fieldname" => $field, "value" => $key);
                                }
                                $vl="";
                            }
                            if($field!="SYSID"){
                                // LO METTO NEI DATI IN INGRESSO SOLTANTO SE NON E' CHIAVE
                                $datay[$field]=qv_actualid($maestro, $vl);
                            }
                            break;
                        default:
                            $unique=true;
                            if(isset($attr->unique)){
                                if($attr->unique){
                                    $uv=$datax[$field];
                                    if($uv!=""){
                                        maestro_query($maestro,"SELECT {AS:TOP 1} SYSID FROM $actualtable WHERE $field='$uv' {LM:LIMIT 1}{O: AND ROWNUM=1}{D:FETCH FIRST 1 ROWS ONLY}", $r);
                                        if(count($r)>0){
                                            $unique=false;
                                        }
                                    }
                                }
                            }
                            if($unique){
                                $datay[$field]=$datax[$field];
                            }
                        }
                    }
                }
            }
            // INVOCO LE FUNZIONI OPPORTUNE
            $jret=false;
            switch($table){
            case "QVGENRES":
                $jret=qv_genres_insert($maestro, $datay);
                if(!$jret["success"]){
                    return $jret;
                }
                break;
            case "QVOBJECTS":
                $jret=qv_objects_insert($maestro, $datay);
                if(!$jret["success"]){
                    return $jret;
                }
                break;
            case "QVMOTIVES":
                $jret=qv_motives_insert($maestro, $datay);
                if(!$jret["success"]){
                    return $jret;
                }
                break;
            case "QVARROWS":
                $jret=qv_arrows_insert($maestro, $datay);
                if(!$jret["success"]){
                    return $jret;
                }
                break;
            case "QVQUIVERS":
                $jret=qv_quivers_insert($maestro, $datay);
                if(!$jret["success"]){
                    return $jret;
                }
                break;
            case "QVQUIVERARROW":
                $jret=qv_quivers_add($maestro, $datay);
                if(!$jret["success"]){
                    return $jret;
                }
                break;
            case "QVSELECTIONS":
                $datay["SELECTION"]=$datay["SELECTEDID"];
                $jret=qv_selections_add($maestro, $datay);
                if(!$jret["success"]){
                    return $jret;
                }
                break;
            }
            if($jret){
                if($inskey!=""){
                    $piano[$inskey]=$jret["SYSID"];
                }
            }
            unset($datax);
            unset($datay);
        }
        foreach($delayed as $updating){
            $table=$updating["table"];
            $field=$updating["fieldname"];
            $id=$updating["sysid"];
            $key=$updating["value"];
            if(isset($piano[$id]) && isset($piano[$key])){
                $RECID=qv_actualid($maestro, $piano[$id]);
                $VALUE=qv_actualid($maestro, $piano[$key]);
                // REPERISCO TYPOLOGYID
                maestro_query($maestro, "SELECT TYPOLOGYID FROM $table WHERE SYSID='$RECID'", $r);
                if(count($r)==1){
                    $TYPOLOGYID=$r[0]["TYPOLOGYID"];
                    $datay=array();
                    $datay["SYSID"]=$RECID;
                    $datay["TYPOLOGYID"]=$TYPOLOGYID;
                    $datay[$field]=$VALUE;
                    $jret=false;
                    switch($table){
                    case "QVGENRES":
                        $jret=qv_genres_update($maestro, $datay);
                        if(!$jret["success"]){
                            return $jret;
                        }
                        break;
                    case "QVOBJECTS":
                        $jret=qv_objects_update($maestro, $datay);
                        if(!$jret["success"]){
                            return $jret;
                        }
                        break;
                    case "QVMOTIVES":
                        $jret=qv_motives_update($maestro, $datay);
                        if(!$jret["success"]){
                            return $jret;
                        }
                        break;
                    case "QVARROWS":
                        $jret=qv_arrows_update($maestro, $datay);
                        if(!$jret["success"]){
                            return $jret;
                        }
                        break;
                    case "QVQUIVERS":
                        $jret=qv_quivers_update($maestro, $datay);
                        if(!$jret["success"]){
                            return $jret;
                        }
                        break;
                    }
                }
            }
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
?>