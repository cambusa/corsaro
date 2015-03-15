<?php 
/****************************************************************************
* Name:            qv_genres_update.php                                     *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverinf.php";
include_once "quiverval.php";
include_once "quiverext.php";
include_once "quivertrg.php";
function qv_genres_update($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // RISOLVO LE INFO DI SESSIONE
        qv_infosession($maestro);
        
        // INDIVIDUAZIONE RECORD
        $sets="";
        $record=qv_solverecord($maestro, $data, "QVGENRES", "SYSID", "NAME", $SYSID, "TYPOLOGYID");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $TYPOLOGYID=$record["TYPOLOGYID"];
        
        // VALIDAZIONE PERSONALIZZATA
        qv_validategenre($maestro, $data, $SYSID, $TYPOLOGYID, 1);

        // SE NAME E' IN MODIFICA LO VALIDO
        if(isset($data["SYSID"]) && isset($data["NAME"])){
            $NAME=$data["NAME"];
            qv_checkname($maestro, "QVGENRES", $SYSID, $NAME);
            qv_appendcomma($sets, "NAME='$NAME'");
        }
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize(qv_inputUTF8($data["DESCRIPTION"]), 200);
            if($DESCRIPTION=="")
                $DESCRIPTION=$NAME;
            qv_appendcomma($sets,"DESCRIPTION='$DESCRIPTION'");
        }

        // DETERMINO BREVITY
        if(isset($data["BREVITY"])){
            $BREVITY=ryqEscapize($data["BREVITY"]);
            qv_appendcomma($sets,"BREVITY='$BREVITY'");
        }

        // DETERMINO REGISTRY
        $clobs=false;
        if(isset($data["REGISTRY"])){
            qv_setclob($maestro, "REGISTRY", $data["REGISTRY"], $REGISTRY, $clobs);
            qv_appendcomma($sets,"REGISTRY=$REGISTRY");
        }

        // DETERMINO ROUNDING
        if(isset($data["ROUNDING"])){
            $ROUNDING=intval($data["ROUNDING"]);
            qv_appendcomma($sets,"ROUNDING=$ROUNDING");
        }

        // DETERMINO TAG
        if(isset($data["TAG"])){
            $TAG=ryqEscapize($data["TAG"], 200);
            qv_appendcomma($sets,"TAG='$TAG'");
        }
            
        $USERUPDATEID=$global_quiveruserid;
        qv_appendcomma($sets,"USERUPDATEID='$USERUPDATEID'");
        
        $TIMEUPDATE="[:NOW()]";
        qv_appendcomma($sets,"TIMEUPDATE=$TIMEUPDATE");
        
        if($sets!=""){
            // GESTIONE DELLA STORICIZZAZIONE
            _qv_historicizing($maestro, "QVGENRE", $SYSID, $TYPOLOGYID, 1);

            // PREDISPONGO COLONNE E VALORI DA REGISTRARE
            $sql="UPDATE QVGENRES SET $sets WHERE SYSID='$SYSID'";
            if(!maestro_execute($maestro, $sql, false, $clobs)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"] );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        // GESTIONE DEI DATI ESTESI
        qv_extension($maestro, $data, "QVGENRE", $SYSID, $TYPOLOGYID, 1);

        // TRIGGER PERSONALIZZATO
        qv_triggergenre($maestro, $data, $SYSID, $TYPOLOGYID, 1);
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