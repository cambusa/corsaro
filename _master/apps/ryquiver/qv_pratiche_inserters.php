<?php 
/****************************************************************************
* Name:            qv_pratiche_inserters.php                                *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once $path_cambusa."ryquiver/quiverinf.php";
include_once $path_applications."ryquiver/stati_ingresso.php";
function qv_pratiche_inserters($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // RISOLVO LE INFO DI SESSIONE
        qv_infosession($maestro);
        
        // DETERMINO PROCESSOID
        qv_solverecord($maestro, $data, "QW_PROCESSI", "PROCESSOID", "", $PROCESSOID);
        if($PROCESSOID==""){
            $babelcode="QVERR_PROCESSOID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare lo stato: [PROCESSOID]";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // INIZIALIZZO L'ELENCO DEGLI UTENTI EGO CHE SIANO IN UNO STATO DI INGRESSO PER IL PROCESSO
        $INSERTERS=array();
        
        // DETERMINO IL PRIMO STATO VALIDO DEL PROCESSO
        $ATTORI="";
        $UFFICI="";
        $sql=buildfirst($maestro, $PROCESSOID, true);
        maestro_query($maestro, $sql, $r);
        for($i=0; $i<count($r); $i++){
            if($ATTORI!=""){
                $ATTORI.="','";
            }
            $ATTORI.=$r[$i]["ATTOREID"];
            $UFFICI.=$r[$i]["UFFICIOID"];
        }
        $ATTORI="'$ATTORI'";
        $UFFICI="'$UFFICI'";
        
        $sql="SELECT EGOUTENTEID FROM QW_ATTORIEGO WHERE SYSID IN ($ATTORI) OR UFFICIOID IN ($UFFICI)";
        maestro_query($maestro, $sql, $r);
        for($i=0; $i<count($r); $i++){
            $INSERTERS[]=$r[$i]["EGOUTENTEID"];
        }
        
        // VARIABILI DI RITORNO
        $babelparams["INSERTERS"]=implode("|", $INSERTERS);
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