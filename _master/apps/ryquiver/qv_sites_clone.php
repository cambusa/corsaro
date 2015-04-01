<?php 
/****************************************************************************
* Name:            qv_sites_clone.php                                       *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_objects_clone.php";
include_once $path_cambusa."ryquiver/qv_objects_update.php";
include_once $path_cambusa."ryquiver/qv_arrows_clone.php";
include_once $path_cambusa."ryquiver/qv_arrows_update.php";
include_once $path_cambusa."ryquiver/qv_selections_add.php";
function qv_sites_clone($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // LEGGO IL SITO
        $site=qv_solverecord($maestro, $data, "QW_WEBSITES", "SITEID", "", $SITEID, "*");
        if($SITEID==""){
            $babelcode="QVERR_SITEID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il sito";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $HOMEPAGEID=$site["HOMEPAGEID"];
        $DEFAULTID=$site["DEFAULTID"];
        
        // ISTRUZIONE DI CLONAZIONE OBJECT SITO
        $datax=array();
        $datax["SYSID"]=$SITEID;
        $datax["DESCRIPTION"]="(nuovo sito)";
        $jret=qv_objects_clone($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $CLONE_SITEID=$jret["SYSID"];
        
        $newcontainers=array(); // Corrispondenza vecchio contenitore => nuovo contenitore
        $oldcontents=array();   // Corrispondenza nuovo contenitore => vecchio contenuto
        $oldrefobject=array();  // Corrispondenza vecchio contenitore => vecchio genitore
        
        // ISTRUZIONI DI CLONAZIONE CONTENITORI
        maestro_query($maestro, "SELECT SYSID,CONTENTID,REFOBJECTID FROM QW_WEBCONTAINERS WHERE SITEID='$SITEID'", $r);
        for($i=0; $i<count($r); $i++){
            $CONTAINERID=$r[$i]["SYSID"];
            $CONTENTID=$r[$i]["CONTENTID"];
            $REFOBJECTID=$r[$i]["REFOBJECTID"];
            
            // CLONAZIONE CONTENITORE
            $datax=array();
            $datax["SYSID"]=$CONTAINERID;
            $jret=qv_objects_clone($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
            $CLONE_CONTAINERID=$jret["SYSID"];
            
            $oldcontents[$CLONE_CONTAINERID]=$CONTENTID;
            $newcontainers[$CONTAINERID]=$CLONE_CONTAINERID;
            $oldrefobject[$CONTAINERID]=$REFOBJECTID;
        }                        
        
        // STABILISCO QUALI CONTENUTI CLONARE
        $contents=array();
        
        // LA PAGINA DI DEFAULT
        $contents[$DEFAULTID]="";
        
        // LE PAGINE DI CONTENITORI
        maestro_query($maestro, "SELECT SYSID FROM QW_WEBCONTENTS WHERE SITEID='$SITEID' AND CONTENTTYPE='frames'", $r);
        for($i=0; $i<count($r); $i++){
            $contents[$r[$i]["SYSID"]]="";
        }
        
        // LE PAGINE LEGATE A CONTENITORI DEL SITO
        foreach($oldcontents as $CLONE_CONTAINERID => $CONTENTID){
            if($CONTENTID!=""){
                $contents[$CONTENTID]="";
            }
        }
        
        // ISTRUZIONI DI CLONAZIONE CONTENUTI
        $newcontents=array();   // Corrispondenza vecchio contenuto => nuovo contenuto
        foreach($contents as $CONTENTID => $DUMMY){
            // LEGGO GLI ATTRIBUTI DEL CONTENUTO
            maestro_query($maestro, "SELECT SITEID,SETFRAMES FROM QW_WEBCONTENTS WHERE SYSID='$CONTENTID'", $r);
            if(count($r)==1){
                $CONTENTSITE=$r[0]["SITEID"];
                if($CONTENTSITE!=""){
                    $SETFRAMES=$r[0]["SETFRAMES"];
                    
                    // CLONAZIONE CONTENUTO
                    $datax=array();
                    $datax["SYSID"]=$CONTENTID;
                    $jret=qv_arrows_clone($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $CLONE_CONTENTID=$jret["SYSID"];
                    
                    $newcontents[$CONTENTID]=$CLONE_CONTENTID;
                    
                    $CLONE_SETFRAMES=qv_createsysid($maestro);
                    $CLONE_SETSETRELATED=qv_createsysid($maestro);
                    
                    // AGGIUSTAMENTO CLONE CONTENUTO
                    $datax=array();
                    $datax["SYSID"]=$CLONE_CONTENTID;
                    $datax["SETFRAMES"]=$CLONE_SETFRAMES;
                    $datax["SETRELATED"]=$CLONE_SETSETRELATED;
                    $datax["SITEID"]=$CLONE_SITEID;
                    $jret=qv_arrows_update($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        return $jret;
                    }

                    // CLONAZIONE DELLA SELEZIONE SETFRAMES
                    maestro_query($maestro, "SELECT * FROM QVSELECTIONS WHERE PARENTID='$SETFRAMES' ORDER BY SORTER", $s);
                    for($j=0; $j<count($s); $j++){
                        $CONTAINERID=$s[$j]["SELECTEDID"];
                        if(isset($newcontainers[$CONTAINERID])){
                            $CLONE_CONTAINERID=$newcontainers[$CONTAINERID];
                            $datax=array();
                            $datax["PARENTID"]=$CLONE_SETFRAMES;
                            $datax["PARENTTABLE"]="QW_WEBCONTENTS";
                            $datax["PARENTFIELD"]="SETFRAMES";
                            $datax["SELECTEDTABLE"]="QVOBJECTS";
                            $datax["SELECTION"]=$CLONE_CONTAINERID;
                            $jret=qv_selections_add($maestro, $datax);
                            unset($datax);
                            if(!$jret["success"]){
                                return $jret;
                            }
                        }
                    }
                }
                else{
                    $newcontents[$CONTENTID]=$CONTENTID;
                }
            }
        }

        // ISTRUZIONE DI AGGIUSTAMENTO OBJECT CONTENITORI
        foreach($newcontainers as $CONTAINERID => $CLONE_CONTAINERID){
            // NUOVO CONTENUTO
            $CLONE_CONTENTID="";
            $CONTENTID=$oldcontents[$CLONE_CONTAINERID];
            if(isset($newcontents[$CONTENTID])){
                $CLONE_CONTENTID=$newcontents[$CONTENTID];
            }
            // NUOVO GENITORE
            $CLONE_REFOBJECTID="";
            if(isset($oldrefobject[$CONTAINERID])){
                $REFOBJECTID=$oldrefobject[$CONTAINERID];
                if(isset($newcontainers[$REFOBJECTID])){
                    $CLONE_REFOBJECTID=$newcontainers[$REFOBJECTID];
                }
            }
            // ISTRUZIONE DI AGGIUSTAMENTO OBJECT CONTENITORE
            $datax=array();
            $datax["SYSID"]=$CLONE_CONTAINERID;
            $datax["REFOBJECTID"]=$CLONE_REFOBJECTID;
            $datax["SITEID"]=$CLONE_SITEID;
            $datax["CONTENTID"]=$CLONE_CONTENTID;
            $jret=qv_objects_update($maestro, $datax);
            unset($datax);
            if(!$jret["success"]){
                return $jret;
            }
        }
        
        // ISTRUZIONE DI AGGIUSTAMENTO OBJECT SITO
        $datax=array();
        $datax["SYSID"]=$CLONE_SITEID;
        
        // NUOVO CONTENITORE DI DEFAULT
        if(isset($newcontainers[$HOMEPAGEID]))
            $datax["HOMEPAGEID"]=$newcontainers[$HOMEPAGEID];
        else
            $datax["HOMEPAGEID"]="";
        
        // NUOVO CONTENUTO DI DEFAULT
        if(isset($newcontents[$DEFAULTID]))
            $datax["DEFAULTID"]=$newcontents[$DEFAULTID];
        else
            $datax["DEFAULTID"]="";

        $jret=qv_objects_update($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        
        // VARIABILI DI RITORNO
        $babelparams["SITEID"]=$CLONE_SITEID;
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