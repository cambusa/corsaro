<?php 
/****************************************************************************
* Name:            qv_sites_export.php                                      *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_sites_export($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    global $path_customize;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $export=array();
        
        // LEGGO IL SITO
        $site=qv_solverecord($maestro, $data, "QW_WEBSITES", "SITEID", "", $SITEID, "*");
        if($SITEID==""){
            $babelcode="QVERR_SITEID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il sito";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $EXPORTNAME=$site["SYSID"];
        $EXPORTDESCR=$site["DESCRIPTION"];
        $DEFAULTID=$site["DEFAULTID"];
        
        $program=array();

        // ESPORTAZIONE SITO
        $statement=array();
        // Tabella
        $statement["table"]="QVOBJECTS";
        $statement["extension"]="OBJECTS_WEBSITES";
        // Dati
        $datax=array();
        $datax["SYSID"]="[:SYSID(".$site["SYSID"].")]";
        if(substr($site["NAME"], 0, 2)!="__"){
            $datax["NAME"]=$site["NAME"];
        }
        $datax["DESCRIPTION"]=$site["DESCRIPTION"];
        $datax["REGISTRY"]=$site["REGISTRY"];
        $datax["TAG"]=$site["TAG"];
        $datax["TYPOLOGYID"]=$site["TYPOLOGYID"];

        if($site["HOMEPAGEID"]!="")
            $datax["HOMEPAGEID"]="[>SYSID(".$site["HOMEPAGEID"].")]";
        else
            $datax["HOMEPAGEID"]="";

        if($site["DEFAULTID"]!="")
            $datax["DEFAULTID"]="[>SYSID(".$site["DEFAULTID"].")]";
        else
            $datax["DEFAULTID"]="";

        $datax["NORMALWIDTH"]=$site["NORMALWIDTH"];
        $datax["NARROWWIDTH"]=$site["NARROWWIDTH"];
        $datax["GLOBALSTYLE"]=$site["GLOBALSTYLE"];
        $datax["GLOBALSCRIPT"]=$site["GLOBALSCRIPT"];
        $datax["GLOBALHEAD"]=$site["GLOBALHEAD"];
        $datax["FAVICON"]=$site["FAVICON"];
        $datax["LOGSTATISTICS"]=$site["LOGSTATISTICS"];
        $datax["PROTECTED"]=$site["PROTECTED"];
        unset($site);
        // Aggancio all'istruzione
        $statement["data"]=$datax;
        $program[]=$statement;
        unset($datax);
        unset($statement);
        
        $contcont=array();   // Corrispondenza contenitore => contenuto
        
        // ESPORTAZIONE CONTENITORI
        maestro_query($maestro, "SELECT * FROM QW_WEBCONTAINERS WHERE SITEID='$SITEID' ORDER BY REFOBJECTID", $r);
        for($i=0; $i<count($r); $i++){
            $container=$r[$i];
            
            $contcont[$container["SYSID"]]=$container["CONTENTID"];

            $statement=array();
            // Tabella
            $statement["table"]="QVOBJECTS";
            $statement["extension"]="OBJECTS_WEBCONTAINERS";
            // Dati
            $datax=array();
            $datax["SYSID"]="[:SYSID(".$container["SYSID"].")]";
            if(substr($container["NAME"], 0, 2)!="__"){
                $datax["NAME"]=$container["NAME"];
            }
            $datax["DESCRIPTION"]=$container["DESCRIPTION"];
            $datax["REGISTRY"]=$container["REGISTRY"];
            $datax["TAG"]=$container["TAG"];
            $datax["TYPOLOGYID"]=$container["TYPOLOGYID"];

            if($container["REFOBJECTID"]!="")
                $datax["REFOBJECTID"]="[:SYSID(".$container["REFOBJECTID"].")]";
            else
                $datax["REFOBJECTID"]="";
            
            $datax["FUNCTIONNAME"]=$container["FUNCTIONNAME"];
            $datax["FRAMESTYLE"]=$container["FRAMESTYLE"];
            $datax["FRAMESCRIPT"]=$container["FRAMESCRIPT"];
            $datax["CLASSES"]=$container["CLASSES"];
            $datax["SITEID"]="[:SYSID(".$container["SITEID"].")]";
            
            if($container["CONTENTID"]!="")
                $datax["CONTENTID"]="[>SYSID(".$container["CONTENTID"].")]";
            else
                $datax["CONTENTID"]="";

            $datax["CURRENTPAGE"]=$container["CURRENTPAGE"];
            $datax["ENABLED"]=$container["ENABLED"];
            $datax["ORDINATORE"]=$container["ORDINATORE"];
            unset($container);
            // Aggancio all'istruzione
            $statement["data"]=$datax;
            $program[]=$statement;
            unset($datax);
            unset($statement);
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
        foreach($contcont as $CONTAINERID => $CONTENTID){
            if($CONTENTID!=""){
                $contents[$CONTENTID]="";
            }
        }
        
        // ESPORTAZIONE CONTENUTI
        foreach($contents as $CONTENTID => $DUMMY){
            // LEGGO GLI ATTRIBUTI DEL CONTENUTO
            maestro_query($maestro, "SELECT * FROM QW_WEBCONTENTS WHERE SYSID='$CONTENTID'", $r);
            if(count($r)==1){
                $CONTENTSITE=$r[0]["SITEID"];
                if($CONTENTSITE!=""){
                    $SETFRAMES=$r[0]["SETFRAMES"];
        
                    $content=$r[0];
                    $CONTENTID=$content["SYSID"];

                    $statement=array();
                    // Tabella
                    $statement["table"]="QVARROWS";
                    $statement["extension"]="ARROWS_WEBCONTENTS";
                    // Dati
                    $datax=array();
                    $datax["SYSID"]="[:SYSID(".$content["SYSID"].")]";
                    $datax["DESCRIPTION"]=$content["DESCRIPTION"];
                    $datax["REGISTRY"]=$content["REGISTRY"];
                    $datax["TAG"]=$content["TAG"];
                    $datax["SCOPE"]=$content["SCOPE"];
                    $datax["TYPOLOGYID"]=$content["TYPOLOGYID"];
                    $datax["GENREID"]=$content["GENREID"];
                    $datax["MOTIVEID"]=$content["MOTIVEID"];
                    $datax["TARGETTIME"]=qv_strtime($content["TARGETTIME"]);
                    $datax["AUXTIME"]=qv_strtime($content["AUXTIME"]);
                    $datax["ABSTRACT"]=$content["ABSTRACT"];
                    $datax["ICON"]=$content["ICON"];
                    $datax["SETFRAMES"]="[:SYSID(".$content["SETFRAMES"].")]";
                    $datax["SETRELATED"]="[:SYSID(".$content["SETRELATED"].")]";
                    $datax["SITEID"]="[:SYSID(".$content["SITEID"].")]";
                    $datax["SPECIALS"]=$content["SPECIALS"];
                    $datax["CONTENTTYPE"]=$content["CONTENTTYPE"];
                    $datax["CONTENTURL"]=$content["CONTENTURL"];
                    $datax["ENVIRON"]=$content["ENVIRON"];
                    $datax["EMBEDID"]=$content["EMBEDID"];
                    $datax["MARQUEETYPE"]=$content["MARQUEETYPE"];
                    $datax["EMAIL"]=$content["EMAIL"];
                    $datax["AUTHOR"]=$content["AUTHOR"];
                    $datax["PARENTID"]="";
                    $datax["INCLUDEFILE"]=$content["INCLUDEFILE"];
                    $datax["ITEMDETAILS"]=$content["ITEMDETAILS"];
                    $datax["NAVHOME"]=$content["NAVHOME"];
                    $datax["NAVPRIMARY"]=$content["NAVPRIMARY"];
                    $datax["NAVPARENTS"]=$content["NAVPARENTS"];
                    $datax["NAVSIBLINGS"]=$content["NAVSIBLINGS"];
                    $datax["NAVRELATED"]=$content["NAVRELATED"];
                    $datax["NAVTOOL"]=$content["NAVTOOL"];
                    $datax["SEARCHITEMS"]=$content["SEARCHITEMS"];
                    unset($content);
                    // Aggancio all'istruzione
                    $statement["data"]=$datax;
                    $program[]=$statement;
                    unset($datax);
                    unset($statement);
                    
                    // ESPORTAZIONE CORRELATI
                    maestro_query($maestro, "SELECT * FROM QVSELECTIONS WHERE PARENTID='$SETFRAMES'", $s);
                    for($j=0; $j<count($s); $j++){
                        $related=$s[$j];

                        $statement=array();
                        // Tabella
                        $statement["table"]="QVSELECTIONS";
                        $statement["extension"]="";
                        // Dati
                        $datax=array();
                        $datax["SYSID"]="[:SYSID(".$related["SYSID"].")]";
                        $datax["PARENTTABLE"]=$related["PARENTTABLE"];
                        $datax["PARENTFIELD"]=$related["PARENTFIELD"];
                        $datax["PARENTID"]="[:SYSID(".$related["PARENTID"].")]";
                        $datax["SELECTEDTABLE"]=$related["SELECTEDTABLE"];
                        $datax["SELECTEDID"]="[:SYSID(".$related["SELECTEDID"].")]";
                        $datax["SORTER"]=$related["SORTER"];
                        unset($related);
                        // Aggancio all'istruzione
                        $statement["data"]=$datax;
                        $program[]=$statement;
                        unset($datax);
                        unset($statement);
                    }
                }
            }
        }
        
        // Aggancio alla radice
        $export["type"]="WEBSITE";
        $export["description"]=$EXPORTDESCR;
        $export["program"]=$program;
        unset($program);
        
        // SERIALIZZAZIONE
        $buff=serialize($export);
        
        // SCRITTURA SU FILE
        $pathname=$path_customize."_export/$EXPORTNAME.QVR";
        $fp=fopen($pathname, "wb");
        fwrite($fp, $buff);
        fclose($fp);
        
        // VARIABILI DI RITORNO
        $babelparams["EXPORTED"]="_export/$EXPORTNAME.QVR";
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