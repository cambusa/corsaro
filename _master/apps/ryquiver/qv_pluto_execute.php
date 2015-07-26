<?php 
/****************************************************************************
* Name:            qv_pluto_execute.php                                     *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."rygeneral/datetime.php";
include_once $path_cambusa."rygeneral/financial.php";
include_once $path_cambusa."rymaestro/maestro_querylib.php";
include_once $path_cambusa."ryquiver/qv_arrows_insert.php";
include_once $path_cambusa."ryquiver/qv_quivers_insert.php";
include_once $path_cambusa."ryquiver/qv_quivers_add.php";
include_once $path_applications."ryquiver/qv_pluto_infoconfig.php";
include_once $path_applications."ryquiver/pluto_developer.php";
include_once $path_applications."ryquiver/pluto_preview.php";
include_once $path_applications."ryquiver/pluto_insert.php";
function qv_pluto_execute($maestro, $data){
    global $global_quiveruserid, $global_quiverroleid;
    global $babelcode, $babelparams;
    global $path_cambusa, $path_customize, $path_databases, $path_applications;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";

        // LEGGO IL PLUTO
        $pluto=qv_solverecord($maestro, $data, "QW_FINCONFIG", "PLUTOID", "", $PLUTOID, "*");
        if($PLUTOID==""){
            $babelcode="QVERR_PLUTOID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la configurazione";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $PROCESSOID=$pluto["PROCESSOID"];
        
        // LEGGO LE INFO DELLA CONFIGURAZIONE
        $datax=array();
        $datax["PLUTOID"]=$PLUTOID;
        $jret=qv_pluto_infoconfig($maestro, $datax);
        unset($datax);
        if(!$jret["success"]){
            return $jret;
        }
        $INFOPLUTO=$jret["params"];
        $STATOID=$INFOPLUTO["STATOID"];
        $ATTOREID=$INFOPLUTO["ATTOREID"];
        $CONTOID=$INFOPLUTO["CONTOID"];
        $CONTROID=$INFOPLUTO["CONTROID"];
        $GENREID=$INFOPLUTO["GENREID"];
        $SEGNO=$INFOPLUTO["SEGNO"];
        $DIVIDENDO=$INFOPLUTO["DIVIDENDO"];
        $DIVISORE=$INFOPLUTO["DIVISORE"];
        $DVDINC=$DIVIDENDO;
        $DVSINC=$DIVISORE;
        $DVDPAG=$DIVIDENDO;
        $DVSPAG=$DIVISORE;
        $PARAMETRI=$INFOPLUTO["PARAMETRI"];
        
        // LEGGO IL SCRIPTID
        $lscript=qv_solverecord($maestro, $data, "QW_FINSCRIPT", "SCRIPTID", "", $SCRIPTID, "DEVELOPER");
        if($SCRIPTID==""){
            $babelcode="QVERR_SCRIPTID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare lo script";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // RISOLVO IL PERCORSO DELLO SCRIPT
        $pathscript=$lscript["DEVELOPER"];
        if(strpos($pathscript, "@")===false && strpos($pathscript, ":")===false){
            $pathscript=$path_customize."_pluto/".$pathscript;
        }
        else{
            $pathscript=str_replace("@customize/", $path_customize, $pathscript);
            $pathscript=str_replace("@cambusa/", $path_cambusa, $pathscript);
            $pathscript=str_replace("@databases/", $path_databases, $pathscript);
            $pathscript=str_replace("@apps/", $path_applications, $pathscript);
        }
        if(!is_file($pathscript)){
            $babelcode="QVERR_NOSCRIPTFILE";
            $b_params=array();
            $b_pattern="Script inesistente";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"]))
            $PRATICADESCR=$data["DESCRIPTION"];
        else
            $PRATICADESCR="FIN. [!SYSID]";
        
        // DETERMINO RICHIEDENTEID
        $richiedente=qv_solverecord($maestro, $data, "QW_ATTORI", "RICHIEDENTEID", "", $RICHIEDENTEID, "DESCRIPTION");
        if($RICHIEDENTEID!=""){
            $RICHIEDENTEDESCR=$richiedente["DESCRIPTION"];
            $PRATICADESCR=str_replace("[!RICHIEDENTE]", $RICHIEDENTEDESCR, $PRATICADESCR);
        }
        
        // ISTANZIO UN DEVELOPER
        $DEVELOPER=new ryDeveloper();
        $DEVELOPER->plutoid=$PLUTOID;
        $DEVELOPER->processoid=$PROCESSOID;
        $DEVELOPER->statoid=$STATOID;
        $DEVELOPER->attoreid=$ATTOREID;
        $DEVELOPER->contoid=$CONTOID;
        $DEVELOPER->controid=$CONTROID;
        $DEVELOPER->genreid=$GENREID;
        $DEVELOPER->richiedenteid=$RICHIEDENTEID;
        $DEVELOPER->segno=$SEGNO;
        $DEVELOPER->dividendo=$DIVIDENDO;
        $DEVELOPER->divisore=$DIVISORE;
        $DEVELOPER->dvdinc=$DVDINC;
        $DEVELOPER->dvsinc=$DVSINC;
        $DEVELOPER->dvdpag=$DVDPAG;
        $DEVELOPER->dvspag=$DVSPAG;
        $DEVELOPER->maestro=&$maestro;
        
        // DETERMINO PARAMETRI+OPZIONI
        $DEVELOPER->parametri=$PARAMETRI;
        if(isset($data["OPZIONI"])){
            $DEVELOPER->parametri=array_merge($DEVELOPER->parametri, $data["OPZIONI"]);
            if(isset($DEVELOPER->parametri["DIVIDENDO"])){
                $DEVELOPER->dividendo=intval($DEVELOPER->parametri["DIVIDENDO"]);
            }
            if(isset($DEVELOPER->parametri["DIVISORE"])){
                $DEVELOPER->divisore=intval($DEVELOPER->parametri["DIVISORE"]);
            }
            if(isset($DEVELOPER->parametri["DVDINC"])){
                $DEVELOPER->dvdinc=intval($DEVELOPER->parametri["DVDINC"]);
            }
            if(isset($DEVELOPER->parametri["DVSINC"])){
                $DEVELOPER->dvsinc=intval($DEVELOPER->parametri["DVSINC"]);
            }
            if(isset($DEVELOPER->parametri["DVDPAG"])){
                $DEVELOPER->dvdpag=intval($DEVELOPER->parametri["DVDPAG"]);
            }
            if(isset($DEVELOPER->parametri["DVSPAG"])){
                $DEVELOPER->dvspag=intval($DEVELOPER->parametri["DVSPAG"]);
            }
        }
        // ARRICCHISCO I PARAMETRI CON VOCI AD USO INTERNO
        $DEVELOPER->parametri["_CONTROID"]=$CONTROID;
        $DEVELOPER->parametri["_GENREID"]=$GENREID;
        $DEVELOPER->parametri["_SEGNO"]=$SEGNO;
        $DEVELOPER->parametri["_DIVIDENDO"]=$DEVELOPER->dividendo;
        $DEVELOPER->parametri["_DIVISORE"]=$DEVELOPER->divisore;
        $DEVELOPER->parametri["_DVDINC"]=$DEVELOPER->dvdinc;
        $DEVELOPER->parametri["_DVSINC"]=$DEVELOPER->dvsinc;
        $DEVELOPER->parametri["_DVDPAG"]=$DEVELOPER->dvdpag;
        $DEVELOPER->parametri["_DVSPAG"]=$DEVELOPER->dvspag;

        // DETERMINO SE GENERARE EFFETTIVAMENTE IL FINANZIAMENTO
        if(isset($data["EFFETTIVO"]))
            $EFFETTIVO=intval($data["EFFETTIVO"]);
        else
            $EFFETTIVO=0;
        
        // INIZIALIZZO IL PROSPETTO DELLO SVILUPPO 
        $SVILUPPO="";

        // CARICO LO SCRIPT
        include_once $pathscript;
        
        if(function_exists("plutoMain")){
            // CHIAMO LA MAIN
            if(plutoMain($DEVELOPER)!==false){
                $DEVELOPER->normalizza(true);
                $DEVELOPER->calcolainteressi();
                $SVILUPPO=pluto_preview($DEVELOPER);
            }
            else{
                $babelcode="QVERR_SCRIPTERR";
                $b_params=array("ERRNUMBER" => $DEVELOPER->lasterrnumber);
                $b_pattern=$DEVELOPER->lasterrdescription;
                $SVILUPPO=$b_pattern;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $babelcode="QVERR_NOFUNCTION";
            $b_params=array();
            $b_pattern="La funzione plutoMain(\$DEVELOPER) non è definita";
            $SVILUPPO=$b_pattern;
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        if($EFFETTIVO){
            // ESEGUO EFFETTIVAMENTE LO SVILUPPO RICHIESTO
            if($DEVELOPER->swap){
                $DEVELOPER->parametri["_SWAP"]=1;
                $jret=pluto_generaswap($maestro, $DEVELOPER, $PRATICADESCR);
            }
            else{
                $DEVELOPER->parametri["_SWAP"]=0;
                $jret=pluto_generafin($maestro, $DEVELOPER, $PRATICADESCR);
            }
            if(!$jret["success"]){
                return $jret;
            }
        }
        // VARIABILI DI RITORNO
        $babelparams["SVILUPPO"]=$SVILUPPO;
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
function pluto_generafin($maestro, $DEVELOPER, $PRATICADESCR){
    // INSERIMENTO QUIVER
    $datax=array();
    $datax["DESCRIPTION"]=$PRATICADESCR;
    $datax["TYPOLOGYID"]=qv_actualid($maestro, "0PRATICHE000");
    $datax["PROCESSOID"]=$DEVELOPER->processoid;
    $datax["STATOID"]=$DEVELOPER->statoid;
    $datax["CONTOID"]=$DEVELOPER->contoid;
    $datax["DATAINIZIO"]=date("Ymd");
    $datax["RICHIEDENTEID"]=$DEVELOPER->richiedenteid;
    $datax["MOREDATA"]=json_encode($DEVELOPER->parametri);
    $jret=qv_quivers_insert($maestro, $datax);
    unset($datax);
    if(!$jret["success"]){
        return $jret;
    }
    $PRATICAID=$jret["SYSID"];

    // SCANDISCO LE FRECCE DA AGGANCIARE ALLA PRATICA
    foreach($DEVELOPER->sviluppo as $flusso){
        $DATA=$flusso["DATA"];
        
        if($flusso["_CAPITALE"]){
            $CAPITALE=$flusso["CAPITALE"];
            $AMOUNT=abs($CAPITALE);
            if($CAPITALE<0)
                $DESCRIPTION="Erogazione $PRATICAID";
            else
                $DESCRIPTION="Rimborso $PRATICAID";
            if( ($DEVELOPER->segno==1 && $CAPITALE<0) || ($DEVELOPER->segno==-1 && $CAPITALE>0) ){
                $MOTIVEID=qv_actualid($maestro, "0CAUSCAPPAG0");
                $BOWID=$DEVELOPER->contoid;
                $TARGETID=$DEVELOPER->controid;
            }
            else{
                $MOTIVEID=qv_actualid($maestro, "0CAUSCAPINC0");
                $BOWID=$DEVELOPER->controid;
                $TARGETID=$DEVELOPER->contoid;
            }
            $jret=pluto_generamov($maestro, $PRATICAID, $DEVELOPER->genreid, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATA, $DEVELOPER->statoid);
            if(!$jret["success"]){
                return $jret;
            }
        }
        if($flusso["_INTERESSI"]){
            $AMOUNT=abs($flusso["INTERESSI"]);
            $DESCRIPTION="Interessi $PRATICAID";
            if($DEVELOPER->segno==1){
                $MOTIVEID=qv_actualid($maestro, "0CAUSINTINC0");
                $BOWID=$DEVELOPER->controid;
                $TARGETID=$DEVELOPER->contoid;
            }
            else{
                $MOTIVEID=qv_actualid($maestro, "0CAUSINTPAG0");
                $BOWID=$DEVELOPER->contoid;
                $TARGETID=$DEVELOPER->controid;
            }
            $jret=pluto_generamov($maestro, $PRATICAID, $DEVELOPER->genreid, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATA, $DEVELOPER->statoid);
            if(!$jret["success"]){
                return $jret;
            }
        }
        if($flusso["_COMMISSIONI"]){
            $AMOUNT=abs($flusso["COMMISSIONI"]);
            $DESCRIPTION="Commissioni $PRATICAID";
            if($DEVELOPER->segno==1){
                $MOTIVEID=qv_actualid($maestro, "0CAUSCOMMINC");
                $BOWID=$DEVELOPER->controid;
                $TARGETID=$DEVELOPER->contoid;
            }
            else{
                $MOTIVEID=qv_actualid($maestro, "0CAUSCOMMPAG");
                $BOWID=$DEVELOPER->contoid;
                $TARGETID=$DEVELOPER->controid;
            }
            $jret=pluto_generamov($maestro, $PRATICAID, $DEVELOPER->genreid, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATA, $DEVELOPER->statoid);
            if(!$jret["success"]){
                return $jret;
            }
        }
        if($flusso["_TASSO"]){
            $AMOUNT=abs($flusso["TASSO"]);
            $GENREID=qv_actualid($maestro, "0TASSOANNUO0");
            $DESCRIPTION="Tasso $PRATICAID";
            if($DEVELOPER->segno==1){
                $MOTIVEID=qv_actualid($maestro, "0MOTFININC00");
                $BOWID="";
                $TARGETID=$DEVELOPER->contoid;
            }
            else{
                $MOTIVEID=qv_actualid($maestro, "0MOTFINPAG00");
                $BOWID=$DEVELOPER->contoid;
                $TARGETID="";
            }
            $jret=pluto_generaevento($maestro, $PRATICAID, $GENREID, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATA, $DEVELOPER->statoid, $DEVELOPER->dividendo, $DEVELOPER->divisore);
            if(!$jret["success"]){
                return $jret;
            }
        }
        if($flusso["_SPREAD"]){
            $AMOUNT=abs($flusso["SPREAD"]);
            $GENREID=qv_actualid($maestro, "0SPREADANNUO");
            $DESCRIPTION="Spread $PRATICAID";
            if( ($DEVELOPER->segno==1 && $flusso["SPREAD"]>0) || ($DEVELOPER->segno==-1 && $flusso["SPREAD"]<0) ){
                $MOTIVEID=qv_actualid($maestro, "0MOTFININC00");
                $BOWID="";
                $TARGETID=$DEVELOPER->contoid;
            }
            else{
                $MOTIVEID=qv_actualid($maestro, "0MOTFINPAG00");
                $BOWID=$DEVELOPER->contoid;
                $TARGETID="";
            }
            $jret=pluto_generaevento($maestro, $PRATICAID, $GENREID, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATA, $DEVELOPER->statoid, $DEVELOPER->dividendo, $DEVELOPER->divisore);
            if(!$jret["success"]){
                return $jret;
            }
        }
    }
    $j=array();
    $j["success"]=1;
    $j["code"]="";
    $j["params"]="";
    $j["message"]="";
    $j["SYSID"]=$PRATICAID;
    return $j; //ritorno standard
}
function pluto_generaswap($maestro, $DEVELOPER, $PRATICADESCR){
    // INSERIMENTO QUIVER
    $datax=array();
    $datax["DESCRIPTION"]=$PRATICADESCR;
    $datax["TYPOLOGYID"]=qv_actualid($maestro, "0PRATICHE000");
    $datax["PROCESSOID"]=$DEVELOPER->processoid;
    $datax["STATOID"]=$DEVELOPER->statoid;
    $datax["CONTOID"]=$DEVELOPER->contoid;
    $datax["DATAINIZIO"]=date("Ymd");
    $datax["RICHIEDENTEID"]=$DEVELOPER->richiedenteid;
    $datax["MOREDATA"]=json_encode($DEVELOPER->parametri);
    $jret=qv_quivers_insert($maestro, $datax);
    unset($datax);
    if(!$jret["success"]){
        return $jret;
    }
    $PRATICAID=$jret["SYSID"];

    // SCANDISCO LE FRECCE DA AGGANCIARE ALLA PRATICA
    foreach($DEVELOPER->sviluppo as $flusso){
        $DATA=$flusso["DATA"];
        if($flusso["_NOMINALE"]){
            $CAPITALE=$flusso["NOMINALE"];
            $AMOUNT=abs($CAPITALE);
            if($CAPITALE>0){
                $DESCRIPTION="Nominale $PRATICAID";
                $MOTIVEID=qv_actualid($maestro, "0CAUSCAPINC0");
                $BOWID="";
                $TARGETID=$DEVELOPER->contoid;
            }
            else{
                $DESCRIPTION="Variazione $PRATICAID";
                $MOTIVEID=qv_actualid($maestro, "0CAUSCAPPAG0");
                $BOWID=$DEVELOPER->contoid;
                $TARGETID="";
            }
            $jret=pluto_generamov($maestro, $PRATICAID, $DEVELOPER->genreid, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATA, $DEVELOPER->statoid, 2);
            if(!$jret["success"]){
                return $jret;
            }
        }
        if($flusso["_INTINC"]){
            $AMOUNT=abs($flusso["INTINC"]);
            $MOTIVEID=qv_actualid($maestro, "0CAUSINTINC0");
            $DESCRIPTION="Interessi incassati $PRATICAID";
            $BOWID=$DEVELOPER->controid;
            $TARGETID=$DEVELOPER->contoid;
            $jret=pluto_generamov($maestro, $PRATICAID, $DEVELOPER->genreid, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATA, $DEVELOPER->statoid);
            if(!$jret["success"]){
                return $jret;
            }
        }
        if($flusso["_INTPAG"]){
            $AMOUNT=abs($flusso["INTPAG"]);
            $MOTIVEID=qv_actualid($maestro, "0CAUSINTPAG0");
            $DESCRIPTION="Interessi pagati $PRATICAID";
            $BOWID=$DEVELOPER->contoid;
            $TARGETID=$DEVELOPER->controid;
            $jret=pluto_generamov($maestro, $PRATICAID, $DEVELOPER->genreid, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATA, $DEVELOPER->statoid);
            if(!$jret["success"]){
                return $jret;
            }
        }
        if($flusso["_COMMINC"]){
            $AMOUNT=abs($flusso["COMMINC"]);
            $MOTIVEID=qv_actualid($maestro, "0CAUSCOMMINC");
            $DESCRIPTION="Commissioni incassate $PRATICAID";
            $BOWID=$DEVELOPER->controid;
            $TARGETID=$DEVELOPER->contoid;
            $jret=pluto_generamov($maestro, $PRATICAID, $DEVELOPER->genreid, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATA, $DEVELOPER->statoid);
            if(!$jret["success"]){
                return $jret;
            }
        }
        if($flusso["_COMMPAG"]){
            $AMOUNT=abs($flusso["COMMPAG"]);
            $MOTIVEID=qv_actualid($maestro, "0CAUSCOMMPAG");
            $DESCRIPTION="Commissioni pagate $PRATICAID";
            $BOWID=$DEVELOPER->contoid;
            $TARGETID=$DEVELOPER->controid;
            $jret=pluto_generamov($maestro, $PRATICAID, $DEVELOPER->genreid, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATA, $DEVELOPER->statoid);
            if(!$jret["success"]){
                return $jret;
            }
        }
        if($flusso["_TASSOINC"]){
            $AMOUNT=abs($flusso["TASSOINC"]);
            $GENREID=qv_actualid($maestro, "0TASSOANNUO0");
            $DESCRIPTION="Tasso incassato $PRATICAID";
            $MOTIVEID=qv_actualid($maestro, "0MOTFININC00");
            $BOWID="";
            $TARGETID=$DEVELOPER->contoid;
            $jret=pluto_generaevento($maestro, $PRATICAID, $GENREID, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATA, $DEVELOPER->statoid, $DEVELOPER->dividendo, $DEVELOPER->divisore);
            if(!$jret["success"]){
                return $jret;
            }
        }
        if($flusso["_TASSOPAG"]){
            $AMOUNT=abs($flusso["TASSOPAG"]);
            $GENREID=qv_actualid($maestro, "0TASSOANNUO0");
            $DESCRIPTION="Tasso pagato $PRATICAID";
            $MOTIVEID=qv_actualid($maestro, "0MOTFINPAG00");
            $BOWID=$DEVELOPER->contoid;
            $TARGETID="";
            $jret=pluto_generaevento($maestro, $PRATICAID, $GENREID, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATA, $DEVELOPER->statoid, $DEVELOPER->dividendo, $DEVELOPER->divisore);
            if(!$jret["success"]){
                return $jret;
            }
        }
        if($flusso["_SPREADINC"]){
            $AMOUNT=$flusso["SPREADINC"];
            $GENREID=qv_actualid($maestro, "0SPREADANNUO");
            $DESCRIPTION="Spread incassato $PRATICAID";
            $MOTIVEID=qv_actualid($maestro, "0MOTFININC00");
            $BOWID="";
            $TARGETID=$DEVELOPER->contoid;
            $jret=pluto_generaevento($maestro, $PRATICAID, $GENREID, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATA, $DEVELOPER->statoid, $DEVELOPER->dividendo, $DEVELOPER->divisore);
            if(!$jret["success"]){
                return $jret;
            }
        }
        if($flusso["_SPREADPAG"]){
            $AMOUNT=$flusso["SPREADPAG"];
            $GENREID=qv_actualid($maestro, "0SPREADANNUO");
            $DESCRIPTION="Spread pagato $PRATICAID";
            $MOTIVEID=qv_actualid($maestro, "0MOTFINPAG00");
            $BOWID=$DEVELOPER->contoid;
            $TARGETID="";
            $jret=pluto_generaevento($maestro, $PRATICAID, $GENREID, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATA, $DEVELOPER->statoid, $DEVELOPER->dividendo, $DEVELOPER->divisore);
            if(!$jret["success"]){
                return $jret;
            }
        }
    }
    $j=array();
    $j["success"]=1;
    $j["code"]="";
    $j["params"]="";
    $j["message"]="";
    $j["SYSID"]=$PRATICAID;
    return $j; //ritorno standard
}
?>