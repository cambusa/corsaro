<?php 
/****************************************************************************
* Name:            qv_attivita_preview.php                                  *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/quiverinf.php";
function qv_attivita_preview($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        // RISOLVO LE INFO DI SESSIONE
        qv_infosession($maestro);
        
        // INIZIALIZZO L'ANTEPRIMA
        $preview="<div style='font-family:sans-serif';font-size:12px;>";
        
        // DETERMINO IL LIVELLO PRIVACY (0-proprietario, 1-interno, 2-esterno)
        if(isset($data["privacy"]))
            $livprivacy=intval($data["privacy"]);
        else
            $livprivacy=2;
        
        // DETERMINO QUIVERID
        $quiver=qv_solverecord($maestro, $data, "QW_PRATICHE", "QUIVERID", "", $QUIVERID, "*");
        if($QUIVERID==""){
            $babelcode="QVERR_QUIVERID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il quiver";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO ARROWID
        $freccia=qv_solverecord($maestro, $data, "QVARROWS", "ARROWID", "", $ARROWID, "*");
        if($ARROWID==""){
            $babelcode="QVERR_ARROWID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la feccia";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $REFARROWID=$freccia["REFARROWID"];
        $CONSISTENCY=intval($freccia["CONSISTENCY"]);

        $padding="padding:5px;";
        $quote="padding-left:30px;padding-top:15px;";
        $gray="background-color:#F0F0F0;";
        $privacy="CONSISTENCY=0 AND AVAILABILITY=0 AND DELETED=0";
        
        if($CONSISTENCY==0){
            $self=false;
            // DETERMINO ATTOREID
            maestro_query($maestro, "SELECT SYSID FROM OBJECTS_ATTORI WHERE UTENTEID='$global_quiveruserid'", $r);
            if(count($r)>0)
                $ATTOREID=$r[0]["SYSID"];
            else
                $ATTOREID="XXX";
            if($livprivacy==0){
                $privacy.=" AND (SCOPE=0 OR (SCOPE>0 AND (BOWID='$ATTOREID' OR TARGETID='$ATTOREID')))";
            }
            else{
                $privacy.=" AND (BOWID='$ATTOREID' OR TARGETID='$ATTOREID')";
            }
            if($REFARROWID!=""){
                // HA UN PARENT
                maestro_query($maestro, "SELECT * FROM QVARROWS WHERE SYSID='$REFARROWID' AND $privacy", $r);
                if(count($r)>0){
                    appendifreccia($maestro, $r[0], $preview);
                }
                maestro_query($maestro, "SELECT * FROM QVARROWS WHERE REFARROWID='$REFARROWID' AND $privacy ORDER BY SYSID", $r);
                for($i=0; $i<count($r); $i++){
                    if($r[$i]["SYSID"]==$ARROWID){
                        $self=true;
                        $preview.="<div style='$quote'>";
                        $preview.="<div style='$padding $gray'>";
                        appendifreccia($maestro, $freccia, $preview);
                        maestro_query($maestro, "SELECT * FROM QVARROWS WHERE REFARROWID='$ARROWID' AND $privacy ORDER BY SYSID", $s);
                        for($j=0; $j<count($s); $j++){
                            $preview.="<div style='$quote'>";
                            $preview.="<div style='$padding'>";
                            appendifreccia($maestro, $s[$j], $preview);
                            $preview.="</div>";
                            $preview.="</div>";
                        }
                        $preview.="</div>";
                        $preview.="</div>";
                    }
                    else{
                        $preview.="<div style='$quote'>";
                        $preview.="<div style='$padding'>";
                        appendifreccia($maestro, $r[$i], $preview);
                        $preview.="</div>";
                        $preview.="</div>";
                    }
                }
            }
            else{
                $self=true;
                // METTO FRECCIA E FIGLIE
                $preview.="<div style='$padding $gray'>";
                appendifreccia($maestro, $freccia, $preview);
                $preview.="</div>";
                maestro_query($maestro, "SELECT * FROM QVARROWS WHERE REFARROWID='$ARROWID' AND $privacy ORDER BY SYSID", $s);
                for($j=0; $j<count($s); $j++){
                    $preview.="<div style='$quote'>";
                    $preview.="<div style='$padding'>";
                    appendifreccia($maestro, $s[$j], $preview);
                    $preview.="</div>";
                    $preview.="</div>";
                }
            }
            if($self==false){
                $preview.="<div style='$padding $gray'>";
                appendifreccia($maestro, $freccia, $preview);
                $preview.="</div>";
            }
        }
        else{
            $preview.="<div style='$padding $gray'>";
            appendifreccia($maestro, $freccia, $preview);
            $preview.="</div>";
        }
        $preview.="</div>";
        
        // VARIABILI DI RITORNO
        $babelparams["PREVIEW"]=$preview;
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
function appendifreccia($maestro, $freccia, &$preview){
    $t=qv_strtime($freccia["AUXTIME"]);
    $t=substr($t,6,2)."/".substr($t,4,2)."/".substr($t,0,4)." ".substr($t,8,2).":".substr($t,10,2).":".substr($t,12,2);
    
    $BOWID=$freccia["BOWID"];
    maestro_query($maestro, "SELECT DESCRIPTION FROM QW_ATTORI WHERE SYSID='$BOWID'", $r);
    if(count($r)>0)
        $b=$r[0]["DESCRIPTION"];
    else
        $b=$BOWID;
    
    $d=$freccia["DESCRIPTION"];
    $d=utf8_decode($d);

    $reg=$freccia["REGISTRY"];
    $reg=str_replace("&", "&amp;", $reg);
    $reg=html_entity_decode($reg, ENT_QUOTES);
    $reg=preg_replace("/<big>/i", "<span style='font-size:1.2em;'>", $reg);
    $reg=preg_replace("/<\/big>/i", "</span>", $reg);
    
    $preview.="<div>[$b] <b>$d</b></div>";
    $preview.="<div style='font-size:10px;'>$t</div>";
    $preview.="<div style='height:4px;overflow:hidden;'> </div>";
    $preview.=$reg;
}
?>