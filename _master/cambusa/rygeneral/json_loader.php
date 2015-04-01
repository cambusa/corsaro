<?php
/****************************************************************************
* Name:            json_loader.php                                          *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function json_load($pathbase, $json){
    if(substr($json,0,1)=="/" || substr($json,1,1)==":")
        $pathname=$json;
    else
        $pathname=$pathbase.$json;
    $infobase=json_decode(file_get_contents($pathname));
    $ret=json_decode("{}");
    if($infobase===null){
        jsonValidate($pathname);
    }
    else{
        for(reset($infobase); $table=current($infobase); next($infobase)){
            if(isset($table->enabled))
                $enabled=intval($table->enabled);
            else
                $enabled=1;
            if($enabled){
                if($table->type=="include"){       // LEGGO RICORSIVAMENTE E FACCIO IL MERGE
                    $inc=json_load($pathbase,$table->path);
                    for(reset($inc); $table=current($inc); next($inc)){
                        $tabname=key($inc);
                        if(property_exists($ret, $tabname))
                            $ret->{$tabname} = object_merge_recursive( $ret->{$tabname}, $table);
                        else
                            $ret->{$tabname}=$table;
                    }
                }
                else{   // TRAVASO BRUTALMENTE
                    $tabname=key($infobase);
                    if(property_exists($ret, $tabname))
                        $ret->{$tabname} = object_merge_recursive( $ret->{$tabname}, $table);
                    else
                        $ret->{$tabname}=$table;
                }
            }
        }
    }
    return $ret;
}
function object_merge_recursive($o1, $o2){
    $ret=$o1;
    foreach($o2 as $k => $o){
        if(is_object($o)){
            if(property_exists($ret, $k))
                $ret->{$k}=object_merge_recursive($ret->{$k}, $o);
            else
                $ret->{$k}=$o;
        }
        elseif(is_array($o)){
            if(property_exists($ret, $k)){
                if(is_array($ret->{$k}))
                    $ret->{$k}=array_merge($ret->{$k}, $o);
                else
                    $ret->{$k}=$o;
            }
            else
                $ret->{$k}=$o;
        }
        else
            $ret->{$k}=$o;
    }
    return $ret;
}
function jsonObjectToArray($d) {
    if(is_object($d)){
        // Gets the properties of the given object
        // with get_object_vars function
        $d=get_object_vars($d);
    }
    if(is_array($d)){
        /*
        * Return array converted to object
        * Using __FUNCTION__ (Magic constant)
        * for recursive call
        */
        return array_map(__FUNCTION__, $d);
    }
    else{
        // Return array
        return $d;
    }
}
function jsonArrayToObject($d) {
    if(is_array($d)){
        /*
        * Return array converted to object
        * Using __FUNCTION__ (Magic constant)
        * for recursive call
        */
        return (object) array_map(__FUNCTION__, $d);
    }
    else{
        // Return object
        return $d;
    }
}
function jsonValidate($pathname){
    $ret=true;
    $row=1;
    $col=1;
    $descr="";
    $level=0;                   // Livello parentesi
    $quote_flag=0;              // Fase virgolette: 0,1
    $newline=0;                 // Tipo accapo: 0 (non ancora individuato), 1 (\n), 2 (\r)
    
    $type=array();              // Tipo livello: 0 (fuori), 1 (graffe), 2 (quadre), 3 (tonde)
    $assign_lev=array();        // Livello assegnamento: 0,1,2
    $comma_flag=array();        // Fase virgola: 0,1
    
    $type[$level]=0;
    $assign_lev[$level]=0;
    $comma_flag[$level]=0;
    
    $buff=file_get_contents($pathname);
    for($i=0;$i<strlen($buff);$i++){
        $char=substr($buff,$i,1);
        if($quote_flag==1){
            // SIAMO DENTRO UNA STRINGA
            switch($char){
            case "\"":
                $quote_flag=0;
                $col+=1;
                break;
            case "\n":
                if($newline==0)
                    $newline=1;
                if($newline==1)
                    $row+=1;
                $col=0;
                break;    
            case "\r":
                if($newline==0)
                    $newline=2;
                if($newline==2)
                    $row+=1;
                $col=0;
                break;
            default:
                $col+=1;
                break;
            }
        }
        else{
            // SIAMO FUORI DALLE STRINGHE
            switch($char){
            case "\"":
                $quote_flag=1;
                if($assign_lev[$level]==0)
                    $assign_lev[$level]=1;
                $comma_flag[$level]=0;
                $col+=1;
                break;
            case "{":
                if($comma_flag[$level]==1){
                    // ERRORE
                    $ret=false;
                    $descr="Atteso assegnamento dopo la virgola";
                }
                $level+=1;
                $type[$level]=1;
                $assign_lev[$level]=0;
                $comma_flag[$level]=0;
                $col+=1;
                break;
            case "}":
                if($type[$level]!=1){
                    // ERRORE
                    $ret=false;
                    $descr="Chiusura di parentesi diversa da quella corrente";
                }
                if($level==0){
                    // ERRORE
                    $ret=false;
                    $descr="Livelli di parentesi errati";
                }
                $level-=1;
                $col+=1;
                break;
            case "[":
                if($comma_flag[$level]==1){
                    // ERRORE
                    $ret=false;
                    $descr="Atteso assegnamento dopo la virgola";
                }
                $level+=1;
                $type[$level]=2;
                $assign_lev[$level]=0;
                $comma_flag[$level]=0;
                $col+=1;
                break;
            case "]":
                if($type[$level]!=2){
                    // ERRORE
                    $ret=false;
                    $descr="Chiusura di parentesi diversa da quella corrente";
                }
                if($level==0){
                    // ERRORE
                    $ret=false;
                    $descr="Livelli di parentesi errati";
                }
                $level-=1;
                $col+=1;
                break;
            case "(":
                if($comma_flag[$level]==1){
                    // ERRORE
                    $ret=false;
                    $descr="Atteso assegnamento dopo la virgola";
                }
                $level+=1;
                $type[$level]=3;
                $assign_lev[$level]=0;
                $comma_flag[$level]=0;
                $col+=1;
                break;
            case ")":
                if($type[$level]!=3){
                    // ERRORE
                    $ret=false;
                    $descr="Chiusura di parentesi diversa da quella corrente";
                }
                if($level==0){
                    // ERRORE
                    $ret=false;
                    $descr="Livelli di parentesi errati";
                }
                $level-=1;
                $col+=1;
                break;
            case ",":
                $assign_lev[$level]=0;
                $comma_flag[$level]=1;
                $col+=1;
                break;
            case ":":
                if($assign_lev[$level]==0){
                    // ERRORE
                    $ret=false;
                    $descr="Assegnamento senza nome";
                }
                if($assign_lev[$level]==2){
                    // ERRORE
                    $ret=false;
                    $descr="Assegnamento incontrato due volte";
                }
                $assign_lev[$level]=2;
                
                $col+=1;
                break;
            case "\n":
                if($newline==0)
                    $newline=1;
                if($newline==1)
                    $row+=1;
                $col=0;
                break;    
            case "\r":
                if($newline==0)
                    $newline=2;
                if($newline==2)
                    $row+=1;
                $col=0;
                break;
            case " ":
            case "\t":
                $col+=1;
                break;
            default:
                if($assign_lev[$level]==0)
                    $assign_lev[$level]=1;
                $comma_flag[$level]=0;
                $col+=1;
                break;
            }
        }
        if(!$ret){
            break;
        }
    }
    if($ret==true && $level>0){
        // ERRORE
        $ret=false;
        $descr="Parentesi non chiusa";
    }
    if(!$ret){
        include_once("../rygeneral/writelog.php");
        writelog("Error in jsonValidate - row:$row col:$col descr:$descr");
    }
    return $ret;
}
?>