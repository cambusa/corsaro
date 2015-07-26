<?php 
/****************************************************************************
* Name:            qv_pluto_modifica.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/qv_arrows_insert.php";
include_once $path_cambusa."ryquiver/qv_arrows_update.php";
include_once $path_cambusa."ryquiver/qv_arrows_delete.php";
include_once $path_cambusa."ryquiver/qv_quivers_add.php";
include_once $path_cambusa."ryquiver/qv_quivers_remove.php";
include_once $path_applications."ryquiver/pluto_developer.php";
include_once $path_applications."ryquiver/pluto_insert.php";
function qv_pluto_modifica($maestro, $data){
    global $global_quiveruserid, $global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // PARAMETRO DI RITORNO CHE SEGNALA DI RIEFFETTUARE LA QUERY
        $RELOAD=0;
        
        // LEGGO IL FINANZIAMENTO
        $pratica=qv_solverecord($maestro, $data, "QW_PRATICHE", "PRATICAID", "", $PRATICAID, "CONTOID,MOREDATA");
        if($PRATICAID==""){
            $babelcode="QVERR_PRATICAID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il finanziamento";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $CONTOID=$pratica["CONTOID"];
        $PARAMETRI=json_decode($pratica["MOREDATA"], true);
        
        if(isset($PARAMETRI["_CONTROID"]))
            $CONTROID=$PARAMETRI["_CONTROID"];
        else
            $CONTROID="";

        if(isset($PARAMETRI["_GENREID"]))
            $GENREID=$PARAMETRI["_GENREID"];
        else
            $GENREID=qv_actualid($maestro, "0MONEYEURO00");;

        if(isset($PARAMETRI["_SEGNO"]))
            $SEGNO=intval($PARAMETRI["_SEGNO"]);
        else
            $SEGNO=1;
        
        if(isset($PARAMETRI["_DIVIDENDO"]))
            $DIVIDENDO=intval($PARAMETRI["_DIVIDENDO"]);
        else
            $DIVIDENDO=365;
            
        if(isset($PARAMETRI["_DIVISORE"]))
            $DIVISORE=intval($PARAMETRI["_DIVISORE"]);
        else
            $DIVISORE=365;
            
        if(isset($PARAMETRI["_DVDINC"]))
            $DVDINC=intval($PARAMETRI["_DVDINC"]);
        else
            $DVDINC=$DIVIDENDO;
            
        if(isset($PARAMETRI["_DVSINC"]))
            $DVSINC=intval($PARAMETRI["_DVSINC"]);
        else
            $DVSINC=$DIVISORE;

        if(isset($PARAMETRI["_DVDPAG"]))
            $DVDPAG=intval($PARAMETRI["_DVDPAG"]);
        else
            $DVDPAG=$DIVIDENDO;
            
        if(isset($PARAMETRI["_DVSPAG"]))
            $DVSPAG=intval($PARAMETRI["_DVSPAG"]);
        else
            $DVSPAG=$DIVISORE;

        // DETERMINO FLUSSOID
        if(isset($data["FLUSSOID"]))
            $FLUSSOID=$data["FLUSSOID"];
        else
            $FLUSSOID="";
        
        if($FLUSSOID!=""){
            // DETERMINO LA DATA DEL FLUSSO
            maestro_query($maestro, "SELECT AUXTIME FROM QVARROWS WHERE SYSID='$FLUSSOID'", $r);
            if(count($r)==1){
                $DATAFLUSSO=substr(qv_strtime($r[0]["AUXTIME"]), 0, 8);
            }
            else{
                $babelcode="QVERR_NOFLUSSO";
                $b_params=array();
                $b_pattern="Flusso inesistente";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
            
        // ISTANZIO UN DEVELOPER
        $DEVELOPER=new ryDeveloper();
        $DEVELOPER->contoid=$CONTOID;
        $DEVELOPER->controid=$CONTROID;
        $DEVELOPER->genreid=$GENREID;
        $DEVELOPER->segno=$SEGNO;
        $DEVELOPER->dividendo=$DIVIDENDO;
        $DEVELOPER->divisore=$DIVISORE;
        $DEVELOPER->dvdinc=$DVDINC;
        $DEVELOPER->dvsinc=$DVSINC;
        $DEVELOPER->dvdpag=$DVDPAG;
        $DEVELOPER->dvspag=$DVSPAG;
        $DEVELOPER->maestro=&$maestro;
        
        // CARICO IL FINANZIAMENTO
        $DEVELOPER->caricafin($PRATICAID);
        
        if($FLUSSOID!=""){
            if(isset($DEVELOPER->sviluppo[$DATAFLUSSO])){
                $FLUSSO=$DEVELOPER->sviluppo[$DATAFLUSSO];
            }
            else{
                $babelcode="QVERR_NOFLUSSO";
                $b_params=array();
                $b_pattern="Data flusso [$DATAFLUSSO] inesistente";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        if(isset($data["DATA"])){
            $NUOVADATA=substr(qv_escapizetime($data["DATA"], LOWEST_DATE), 0, 8);
            // LA DATA DEVE ESSERE VALIDA
            if($NUOVADATA==LOWEST_DATE){
                $babelcode="QVERR_DATAVUOTA";
                $b_params=array();
                $b_pattern="Data flusso non impostata";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            if($FLUSSOID!=""){
                // SE LA DATA E' CAMBIATA NON DEVE ESISTERE NELLO SVILUPPO
                if($NUOVADATA!=$DATAFLUSSO){
                    if(isset($DEVELOPER->sviluppo[$NUOVADATA])){
                        $babelcode="QVERR_DATAESISTE";
                        $b_params=array();
                        $b_pattern="Data flusso già esistente";
                        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                    }
                }
            }
            else{
                // SE E' UNA NUOVA DATA NON DEVE ESISTERE NELLO SVILUPPO
                if(isset($DEVELOPER->sviluppo[$NUOVADATA])){
                    $babelcode="QVERR_DATAESISTE";
                    $b_params=array();
                    $b_pattern="Data flusso già esistente";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
            $DATAFLUSSO=$NUOVADATA;
        }
        elseif($FLUSSOID==""){
            $babelcode="QVERR_NODATAFLUSSO";
            $b_params=array();
            $b_pattern="Data flusso non specificata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        // CAPITALE
        if(isset($data["_CAPITALE"])){
            $oper=intval($data["_CAPITALE"]);
            $ARROWID="";
            if($DEVELOPER->swap){
                if(isset($FLUSSO["@NOMINALE"]))
                    $ARROWID=$FLUSSO["@NOMINALE"];
            }
            else{
                if(isset($FLUSSO["@CAPITALE"]))
                    $ARROWID=$FLUSSO["@CAPITALE"];
            }
                
            if($oper){
                // SCRITTURA
                $CAPITALE=floatval($data["CAPITALE"]);
                $AMOUNT=abs($CAPITALE);
                if($DEVELOPER->swap){
                    if($CAPITALE<0){
                        $DESCRIPTION="Nominale $PRATICAID";
                        $MOTIVEID=qv_actualid($maestro, "0CAUSCAPINC0");
                        $BOWID=$DEVELOPER->controid;
                        $TARGETID=$DEVELOPER->contoid;
                    }
                    else{
                        $DESCRIPTION="Variazione $PRATICAID";
                        $MOTIVEID=qv_actualid($maestro, "0CAUSCAPPAG0");
                        $BOWID=$DEVELOPER->contoid;
                        $TARGETID=$DEVELOPER->controid;
                    }
                    $CONSISTENCY=2;
                }
                else{
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
                    $CONSISTENCY=0;
                }
                if($ARROWID!=""){
                    $datax=array();
                    $datax["SYSID"]=$ARROWID;
                    $datax["AMOUNT"]=$AMOUNT;
                    $datax["MOTIVEID"]=$MOTIVEID;
                    $datax["BOWID"]=$BOWID;
                    $datax["TARGETID"]=$TARGETID;
                    $datax["BOWTIME"]=$DATAFLUSSO;
                    $datax["TARGETTIME"]=$DATAFLUSSO;
                    $datax["AUXTIME"]=$DATAFLUSSO;
                    $datax["CONSISTENCY"]=$CONSISTENCY;
                    $jret=qv_arrows_update($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        return $jret;
                    }
                }
                else{
                    $jret=pluto_generamov($maestro, $PRATICAID, $DEVELOPER->genreid, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATAFLUSSO, $DEVELOPER->statoid, $CONSISTENCY);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
            else{
                // CANCELLAZIONE
                if($ARROWID!=""){
                    if($DEVELOPER->swap){
                        // CONTROLLO CHE NON SIA IL NOMINALE DI ACCENSIONE
                        $keys=array_keys($DEVELOPER->sviluppo);
                        if($DATAFLUSSO==$keys[0]){
                            $babelcode="QVERR_CAPNOMINALE";
                            $b_params=array();
                            $b_pattern="Impossibile togliere il nominale di accensione";
                            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                        }
                    }
                    $jret=pluto_arrowremove($maestro, $PRATICAID, $ARROWID);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
        }
        // INTERESSI INCASSATI
        if(isset($data["_INTINC"])){
            $oper=intval($data["_INTINC"]);
            $ARROWID=pluto_solveid($DEVELOPER, $FLUSSO, "INT", "INTERESSI", 1);
            if($oper){
                // SCRITTURA
                $AMOUNT=abs(floatval($data["INTINC"]));
                $MOTIVEID=qv_actualid($maestro, "0CAUSINTINC0");
                if($DEVELOPER->swap)
                    $DESCRIPTION="Interessi incassati $PRATICAID";
                else
                    $DESCRIPTION="Interessi $PRATICAID";
                $BOWID=$DEVELOPER->controid;
                $TARGETID=$DEVELOPER->contoid;
                if($ARROWID!=""){
                    $datax=array();
                    $datax["SYSID"]=$ARROWID;
                    $datax["AMOUNT"]=$AMOUNT;
                    $datax["MOTIVEID"]=$MOTIVEID;
                    $datax["BOWID"]=$BOWID;
                    $datax["TARGETID"]=$TARGETID;
                    $datax["BOWTIME"]=$DATAFLUSSO;
                    $datax["TARGETTIME"]=$DATAFLUSSO;
                    $datax["AUXTIME"]=$DATAFLUSSO;
                    $jret=qv_arrows_update($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        return $jret;
                    }
                }
                else{
                    $jret=pluto_generamov($maestro, $PRATICAID, $DEVELOPER->genreid, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATAFLUSSO, $DEVELOPER->statoid);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
            else{
                // CANCELLAZIONE
                if($ARROWID!=""){
                    $jret=pluto_arrowremove($maestro, $PRATICAID, $ARROWID);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
        }
        // INTERESSI PAGATI
        if(isset($data["_INTPAG"])){
            $oper=intval($data["_INTPAG"]);
            $ARROWID=pluto_solveid($DEVELOPER, $FLUSSO, "INT", "INTERESSI", -1);
            if($oper){
                // SCRITTURA
                $AMOUNT=abs(floatval($data["INTPAG"]));
                $MOTIVEID=qv_actualid($maestro, "0CAUSINTPAG0");
                if($DEVELOPER->swap)
                    $DESCRIPTION="Interessi pagati $PRATICAID";
                else
                    $DESCRIPTION="Interessi $PRATICAID";
                $BOWID=$DEVELOPER->contoid;
                $TARGETID=$DEVELOPER->controid;
                if($ARROWID!=""){
                    $datax=array();
                    $datax["SYSID"]=$ARROWID;
                    $datax["AMOUNT"]=$AMOUNT;
                    $datax["MOTIVEID"]=$MOTIVEID;
                    $datax["BOWID"]=$BOWID;
                    $datax["TARGETID"]=$TARGETID;
                    $datax["BOWTIME"]=$DATAFLUSSO;
                    $datax["TARGETTIME"]=$DATAFLUSSO;
                    $datax["AUXTIME"]=$DATAFLUSSO;
                    $jret=qv_arrows_update($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        return $jret;
                    }
                }
                else{
                    $jret=pluto_generamov($maestro, $PRATICAID, $DEVELOPER->genreid, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATAFLUSSO, $DEVELOPER->statoid);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
            else{
                // CANCELLAZIONE
                if($ARROWID!=""){
                    $jret=pluto_arrowremove($maestro, $PRATICAID, $ARROWID);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
        }
        // COMMISSIONI INCASSATE
        if(isset($data["_COMMINC"])){
            $oper=intval($data["_COMMINC"]);
            $ARROWID=pluto_solveid($DEVELOPER, $FLUSSO, "COMM", "COMMISSIONI", 1);
            if($oper){
                // SCRITTURA
                $AMOUNT=abs(floatval($data["COMMINC"]));
                $MOTIVEID=qv_actualid($maestro, "0CAUSCOMMINC");
                if($DEVELOPER->swap)
                    $DESCRIPTION="Commissioni incassate $PRATICAID";
                else
                    $DESCRIPTION="Commissioni $PRATICAID";
                $BOWID=$DEVELOPER->controid;
                $TARGETID=$DEVELOPER->contoid;
                if($ARROWID!=""){
                    $datax=array();
                    $datax["SYSID"]=$ARROWID;
                    $datax["AMOUNT"]=$AMOUNT;
                    $datax["MOTIVEID"]=$MOTIVEID;
                    $datax["BOWID"]=$BOWID;
                    $datax["TARGETID"]=$TARGETID;
                    $datax["BOWTIME"]=$DATAFLUSSO;
                    $datax["TARGETTIME"]=$DATAFLUSSO;
                    $datax["AUXTIME"]=$DATAFLUSSO;
                    $jret=qv_arrows_update($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        return $jret;
                    }
                }
                else{
                    $jret=pluto_generamov($maestro, $PRATICAID, $DEVELOPER->genreid, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATAFLUSSO, $DEVELOPER->statoid);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
            else{
                // CANCELLAZIONE
                if($ARROWID!=""){
                    $jret=pluto_arrowremove($maestro, $PRATICAID, $ARROWID);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
        }
        // COMMISSIONI PAGATE
        if(isset($data["_COMMPAG"])){
            $oper=intval($data["_COMMPAG"]);
            $ARROWID=pluto_solveid($DEVELOPER, $FLUSSO, "COMM", "COMMISSIONI", -1);
            if($oper){
                // SCRITTURA
                $AMOUNT=abs(floatval($data["COMMPAG"]));
                $MOTIVEID=qv_actualid($maestro, "0CAUSCOMMPAG");
                if($DEVELOPER->swap)
                    $DESCRIPTION="Commissioni pagate $PRATICAID";
                else
                    $DESCRIPTION="Commissioni $PRATICAID";
                $BOWID=$DEVELOPER->contoid;
                $TARGETID=$DEVELOPER->controid;
                if($ARROWID!=""){
                    $datax=array();
                    $datax["SYSID"]=$ARROWID;
                    $datax["AMOUNT"]=$AMOUNT;
                    $datax["MOTIVEID"]=$MOTIVEID;
                    $datax["BOWID"]=$BOWID;
                    $datax["TARGETID"]=$TARGETID;
                    $datax["BOWTIME"]=$DATAFLUSSO;
                    $datax["TARGETTIME"]=$DATAFLUSSO;
                    $datax["AUXTIME"]=$DATAFLUSSO;
                    $jret=qv_arrows_update($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        return $jret;
                    }
                }
                else{
                    $jret=pluto_generamov($maestro, $PRATICAID, $DEVELOPER->genreid, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATAFLUSSO, $DEVELOPER->statoid);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
            else{
                // CANCELLAZIONE
                if($ARROWID!=""){
                    $jret=pluto_arrowremove($maestro, $PRATICAID, $ARROWID);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
        }
        // TASSO INCASSATO
        if(isset($data["_TASSOINC"])){
            $oper=intval($data["_TASSOINC"]);
            $ARROWID=pluto_solveid($DEVELOPER, $FLUSSO, "TASSO", "TASSO", 1);
            if($oper){
                // SCRITTURA
                $AMOUNT=abs(floatval($data["TASSOINC"]));
                $GENREID=qv_actualid($maestro, "0TASSOANNUO0");
                $MOTIVEID=qv_actualid($maestro, "0MOTFININC00");
                if($DEVELOPER->swap)
                    $DESCRIPTION="Tasso incassato $PRATICAID";
                else
                    $DESCRIPTION="Tasso $PRATICAID";
                $BOWID="";
                $TARGETID=$DEVELOPER->contoid;
                if($ARROWID!=""){
                    $datax=array();
                    $datax["SYSID"]=$ARROWID;
                    $datax["AMOUNT"]=$AMOUNT;
                    $datax["GENREID"]=$GENREID;
                    $datax["MOTIVEID"]=$MOTIVEID;
                    $datax["BOWID"]=$BOWID;
                    $datax["TARGETID"]=$TARGETID;
                    $datax["BOWTIME"]=$DATAFLUSSO;
                    $datax["TARGETTIME"]=$DATAFLUSSO;
                    $datax["AUXTIME"]=$DATAFLUSSO;
                    $jret=qv_arrows_update($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        return $jret;
                    }
                }
                else{
                    $jret=pluto_generaevento($maestro, $PRATICAID, $GENREID, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATAFLUSSO, $DEVELOPER->statoid, $DEVELOPER->dividendo, $DEVELOPER->divisore);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
            else{
                // CANCELLAZIONE
                if($ARROWID!=""){
                    $jret=pluto_arrowremove($maestro, $PRATICAID, $ARROWID);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
        }
        // TASSO PAGATO
        if(isset($data["_TASSOPAG"])){
            $oper=intval($data["_TASSOPAG"]);
            $ARROWID=pluto_solveid($DEVELOPER, $FLUSSO, "TASSO", "TASSO", -1);
            if($oper){
                // SCRITTURA
                $AMOUNT=abs(floatval($data["TASSOPAG"]));
                $GENREID=qv_actualid($maestro, "0TASSOANNUO0");
                $MOTIVEID=qv_actualid($maestro, "0MOTFINPAG00");
                if($DEVELOPER->swap)
                    $DESCRIPTION="Tasso pagato $PRATICAID";
                else
                    $DESCRIPTION="Tasso $PRATICAID";
                $BOWID=$DEVELOPER->contoid;
                $TARGETID="";
                if($ARROWID!=""){
                    $datax=array();
                    $datax["SYSID"]=$ARROWID;
                    $datax["AMOUNT"]=$AMOUNT;
                    $datax["GENREID"]=$GENREID;
                    $datax["MOTIVEID"]=$MOTIVEID;
                    $datax["BOWID"]=$BOWID;
                    $datax["TARGETID"]=$TARGETID;
                    $datax["BOWTIME"]=$DATAFLUSSO;
                    $datax["TARGETTIME"]=$DATAFLUSSO;
                    $datax["AUXTIME"]=$DATAFLUSSO;
                    $jret=qv_arrows_update($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        return $jret;
                    }
                }
                else{
                    $jret=pluto_generaevento($maestro, $PRATICAID, $GENREID, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATAFLUSSO, $DEVELOPER->statoid, $DEVELOPER->dividendo, $DEVELOPER->divisore);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
            else{
                // CANCELLAZIONE
                if($ARROWID!=""){
                    $jret=pluto_arrowremove($maestro, $PRATICAID, $ARROWID);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
        }
        // SPREAD INCASSATO
        if(isset($data["_SPREADINC"])){
            $oper=intval($data["_SPREADINC"]);
            $ARROWID=pluto_solveid($DEVELOPER, $FLUSSO, "SPREAD", "SPREAD", 1);
            if($oper){
                // SCRITTURA
                $AMOUNT=floatval($data["SPREADINC"]);
                $GENREID=qv_actualid($maestro, "0SPREADANNUO");
                $MOTIVEID=qv_actualid($maestro, "0MOTFININC00");
                if($DEVELOPER->swap)
                    $DESCRIPTION="Spread incassato $PRATICAID";
                else
                    $DESCRIPTION="Spread $PRATICAID";
                $BOWID="";
                $TARGETID=$DEVELOPER->contoid;
                if($ARROWID!=""){
                    $datax=array();
                    $datax["SYSID"]=$ARROWID;
                    $datax["AMOUNT"]=$AMOUNT;
                    $datax["GENREID"]=$GENREID;
                    $datax["MOTIVEID"]=$MOTIVEID;
                    $datax["BOWID"]=$BOWID;
                    $datax["TARGETID"]=$TARGETID;
                    $datax["BOWTIME"]=$DATAFLUSSO;
                    $datax["TARGETTIME"]=$DATAFLUSSO;
                    $datax["AUXTIME"]=$DATAFLUSSO;
                    $jret=qv_arrows_update($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        return $jret;
                    }
                }
                else{
                    $jret=pluto_generaevento($maestro, $PRATICAID, $GENREID, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATAFLUSSO, $DEVELOPER->statoid, $DEVELOPER->dividendo, $DEVELOPER->divisore);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
            else{
                // CANCELLAZIONE
                if($ARROWID!=""){
                    $jret=pluto_arrowremove($maestro, $PRATICAID, $ARROWID);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
        }
        // SPREAD PAGATO
        if(isset($data["_SPREADPAG"])){
            $oper=intval($data["_SPREADPAG"]);
            $ARROWID=pluto_solveid($DEVELOPER, $FLUSSO, "SPREAD", "SPREAD", -1);
            if($oper){
                // SCRITTURA
                $AMOUNT=floatval($data["SPREADPAG"]);
                $GENREID=qv_actualid($maestro, "0SPREADANNUO");
                $MOTIVEID=qv_actualid($maestro, "0MOTFINPAG00");
                if($DEVELOPER->swap)
                    $DESCRIPTION="Spread pagato $PRATICAID";
                else
                    $DESCRIPTION="Spread $PRATICAID";
                $BOWID=$DEVELOPER->contoid;
                $TARGETID="";
                if($ARROWID!=""){
                    $datax=array();
                    $datax["SYSID"]=$ARROWID;
                    $datax["AMOUNT"]=$AMOUNT;
                    $datax["GENREID"]=$GENREID;
                    $datax["MOTIVEID"]=$MOTIVEID;
                    $datax["BOWID"]=$BOWID;
                    $datax["TARGETID"]=$TARGETID;
                    $datax["BOWTIME"]=$DATAFLUSSO;
                    $datax["TARGETTIME"]=$DATAFLUSSO;
                    $datax["AUXTIME"]=$DATAFLUSSO;
                    $jret=qv_arrows_update($maestro, $datax);
                    unset($datax);
                    if(!$jret["success"]){
                        return $jret;
                    }
                }
                else{
                    $jret=pluto_generaevento($maestro, $PRATICAID, $GENREID, $AMOUNT, $MOTIVEID, $DESCRIPTION, $BOWID, $TARGETID, $DATAFLUSSO, $DEVELOPER->statoid, $DEVELOPER->dividendo, $DEVELOPER->divisore);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
            else{
                // CANCELLAZIONE
                if($ARROWID!=""){
                    $jret=pluto_arrowremove($maestro, $PRATICAID, $ARROWID);
                    if(!$jret["success"]){
                        return $jret;
                    }
                    $RELOAD=1;
                }
            }
        }
        // VARIABILI DI RITORNO
        $babelparams["RELOAD"]=$RELOAD;
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
function pluto_arrowremove($maestro, $PRATICAID, $ARROWID){
    $datax=array();
    $datax["QUIVERID"]=$PRATICAID;
    $datax["ARROWID"]=$ARROWID;
    $jret=qv_quivers_remove($maestro, $datax);
    unset($datax);
    if(!$jret["success"]){
        return $jret;
    }
    $datax=array();
    $datax["SYSID"]=$ARROWID;
    $jret=qv_arrows_delete($maestro, $datax);
    unset($datax);
    if(!$jret["success"]){
        return $jret;
    }
    $j=array();
    $j["success"]=1;
    $j["code"]="";
    $j["params"]="";
    $j["message"]="";
    $j["SYSID"]=$ARROWID;
    return $j; //ritorno standard
}
function pluto_solveid($DEVELOPER, &$FLUSSO, $prefisso, $nome, $segno){
    $ARROWID="";
    if($segno==1)
        $nomesegno="INC";
    elseif($segno==-1)
        $nomesegno="PAG";
    if($DEVELOPER->swap){
        if(isset($FLUSSO["@$prefisso$nomesegno"]))
            $ARROWID=$FLUSSO["@$prefisso$nomesegno"];
    }
    elseif($DEVELOPER->segno==$segno){
        if(isset($FLUSSO["@$nome"]))
            $ARROWID=$FLUSSO["@$nome"];
    }
    return $ARROWID;
}
?>