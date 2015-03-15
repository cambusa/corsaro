<?php 
/****************************************************************************
* Name:            unicode.php                                              *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function utf8Decode($buffer){
    $offset=0;
    $lenbuffer=strlen($buffer);
    $vettreplace=array();
    $r="";
    $lencode=1;
    $ultras=array();
     
    preg_match_all("/[\x80-\xFF]/", $buffer, $m);
    $v=$m[0];
    for ($i=0;$i<count($v);$i++){
        if (!in_array($v[$i],$ultras))
            $ultras[]=$v[$i];
    }
    
    for ($i=0;$i<count($ultras);$i++){
    
        $offset=stripos($buffer,$ultras[$i]);
        
        while ($offset!==false){
            $k=ord($ultras[$i]);
            
            if (($k & 0x80)==0){
                   $lencode=1;
            }
            elseif (($k & 0xE0)==0xC0){
                $k2 = ord(substr($buffer, $offset+1, 1));
                if (($k2 & 0xC0)==0x80){
                    $lencode=2;
                    
                    $k &= 0x1F;
                    $k2 &= 0x3F;
                    //$lres = 64 * $k + $k2;
                    $k <<= 6;
                    $lres = (integer)($k+$k2);
                    
                    if ($lres<0x100)
                        $r=chr($lres % 0x100);
                    else
                        $r="&#x".substr("00".dechex((integer)($lres >> 8)), -2).substr("00".dechex($lres % 0x100), -2).";";
                    }
                    else{
                        $lencode=1;
                   }
                }
                elseIf (($k & 0xF0)==0xE0){
                    $k2=ord(substr($buffer, $offset+1, 1));
                    $k3=ord(substr($buffer, $offset+2, 1));
                   
                    if (($k2 & 0xC0)==0x80 && ($k3 & 0xC0)==0x80){
                        $lencode=3;
                        
                        $k &=  0xF;
                        $k2 &=  0x3F;
                        $k3 &= 0x3F;
                        //$lres = 4096 * $k + 64 * $k2 + $k3;
                        $k <<= 12;
                        $k2 <<= 6;
                        $lres = (integer)($k+$k2+$k3);
                        
                        if ($lres < 0x10000)
                             $r= "&#x" . substr("00" . dechex(((integer)($lres % 0x10000) >> 8)), -2) . substr("00" . dechex($lres % 0x100), -2) . ";";
                        else
                             $r= "&#x" . substr("00" . dechex((integer)($lres >> 16)), -2) . substr("00" . dechex(((integer)($lres % 0x10000) >> 8)), -2) . substr("00" . dechex($lres % 0x100), -2) . ";";
                   }
                   else{
                        $lencode=1;
                   }
            }
            else{
                $k2 = ord(substr($buffer, $offset+1, 1));
                $k3 = ord(substr($buffer, $offset+2, 1));
                $k4 = ord(substr($buffer, $offset+3, 1));
                   
                if (($k2 & 0xC0)==0x80 && ($k3 & 0xC0)==0x80 && ($k4 & 0xC0)==0x80){
                    $lencode=3;
                        
                    $k &= 0xF;
                    $k2 &= 0x3F;
                    $k3 &= 0x3F;
                    $k4 &= 0x3F;
                    //$lres = 262144 * $k + 4096 * $k2 + 64 * $k3 + $k4;
                    $k <<= 18;
                    $k2 <<= 12;
                    $k3 <<= 6;
                    $lres = (integer)($k+$k2+$k3+$k4);
                        
                    if ($lres < 0x1000000){
                        $lres = ($lres % 0x1000000);
                        $r= "&#x";
                        $r.=substr("00" . dechex((integer)($lres >> 16)), -2); $lres = ($lres % 0x10000);
                        $r.=substr("00" . dechex((integer)($lres >> 8)), -2); $lres = ($lres % 0x100);
                        $r.=substr("00" . dechex($lres), -2);
                        $r.= ";";
                    }
                    else{
                        $r="&#x";
                        $r.=substr("00" . dechex((integer)($lres >> 24)), -2); $lres = ($lres % 0x1000000);
                        $r.=substr("00" . dechex((integer)($lres >> 16)), -2); $lres = ($lres % 0x10000);
                        $r.=substr("00" . dechex((integer)($lres >> 8)), -2); $lres = ($lres % 0x100);
                        $r.=substr("00" . dechex($lres), -2);
                        $r.=";";
                    }
                }          
                else{
                    $lencode=1;
                }
            }
              
            if ($lencode>1){
                $s=substr($buffer, $offset, $lencode);
                if(!isset($vettreplace[$s]))
                    $vettreplace[$s]=$r;
            }
            $offset=stripos($buffer,$ultras[$i],$offset+1);
        }
    }
    return strtr($buffer, $vettreplace);
}
?>