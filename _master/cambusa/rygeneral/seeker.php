<?php
/****************************************************************************
* Name:            seeker.php                                               *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.00                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
// Per debug
/*
$bulk="Studio porco cane associato";
$needle="Cne Porco";

//$bulk="Fabbrica della brianza";
//$needle="Industria della periferia";

if(text_seeker($needle, $bulk))
    print "Sì";
else
    print "No";
*/
function text_seeker($needle, $bulk){
    try{
        // NORMALIZZO LA STRINGA CERCANDA
        $needle=strtolower($needle);
        $needle=strtr($needle, 
                    array("à" => "a", 
                          "è" => "e", 
                          "é" => "e", 
                          "ì" => "i", 
                          "ò" => "o", 
                          "ù" => "u"
                    )
        );
        $needle=preg_replace("/ +/"," ",$needle);
        $needle=preg_replace("/[^a-z0-9 ]/","",$needle);
        
        // NORMALIZZO LA BULK
        $bulk=strtolower($bulk);
        $bulk=strtr($bulk, 
                    array("à" => "a", 
                          "è" => "e", 
                          "é" => "e", 
                          "ì" => "i", 
                          "ò" => "o", 
                          "ù" => "u"
                    )
        );
        $bulk=preg_replace("/ +/"," ",$bulk);
        $bulk=preg_replace("/[^a-z0-9 ]/","",$bulk);
        
        // ANALISI
        text_similarity($needle, $bulk, $found, $num1, $num2);
        
        // Per debug
        //print $found." ".$num1." ".$num2;
        
        if($num1>0)
            return (($found/$num1)>0.6);
        else
            return false;
    }
    catch(Exception $e){
        return false;
    }
}

function text_similarity($text1, $text2, &$found, &$num1, &$num2){
    $found=0;
    $num1=0;
    $num2=0;
     
    // ESTRAGGO LE PAROLE DALLA PRIMA STRINGA
    preg_match_all("/(\w{3,})/", $text1, $m);
    $vett_word1=$m[0];
    $num1=count($vett_word1);
    $vett_used1=array_fill(0, $num1, false);
    unset($m);

    // ESTRAGGO LE PAROLE DALLA SECONDA STRINGA
    preg_match_all("/(\w{3,})/", $text2, $m);
    $vett_word2=$m[0];
    $num2=count($vett_word2);
    $vett_used2=array_fill(0, $num2, false);
    unset($m);
    
    for($i=0;$i<$num1;$i++){
        for($j=0;$j<$num2;$j++){
            if($vett_used1[$i]==false && $vett_used2[$j]==false){
                if(ctype_digit($vett_word1[$i]) || ctype_digit($vett_word2[$j])){
                    if($vett_word1[$i]==$vett_word2[$j]){
                        $vett_used1[$i]=true;
                        $vett_used2[$j]=true;
                        $found+=1;
                        break;
                    }
                }
                else{
                    if(levenshtein($vett_word1[$i], $vett_word2[$j])<=1){
                        $vett_used1[$i]=true;
                        $vett_used2[$j]=true;
                        $found+=1;
                        break;
                    }
                }
            }
        }
    }
}
?>