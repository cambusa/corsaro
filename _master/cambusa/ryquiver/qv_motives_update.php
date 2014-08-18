<?php 
/****************************************************************************
* Name:            qv_motives_update.php                                    *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverinf.php";
include_once "quiverval.php";
include_once "quiverext.php";
include_once "quivertrg.php";
function qv_motives_update($maestro, $data){
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
        $record=qv_solverecord($maestro, $data, "QVMOTIVES", "SYSID", "NAME", $SYSID, "TYPOLOGYID");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $TYPOLOGYID=$record["TYPOLOGYID"];
        
        // VALIDAZIONE PERSONALIZZATA
        qv_validatemotive($maestro, $data, $SYSID, $TYPOLOGYID, 1);
        
        // SE NAME E' IN MODIFICA LO VALIDO
        if(isset($data["SYSID"]) && isset($data["NAME"])){
            $NAME=$data["NAME"];
            qv_checkname($maestro, "QVMOTIVES", $SYSID, $NAME);
            qv_appendcomma($sets, "NAME='$NAME'");
        }
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize(qv_inputUTF8($data["DESCRIPTION"]), 100);
            if($DESCRIPTION=="")
                $DESCRIPTION=$NAME;
            qv_appendcomma($sets,"DESCRIPTION='$DESCRIPTION'");
        }

        // DETERMINO DIRECTION
        if(isset($data["DIRECTION"])){
            $DIRECTION=intval($data["DIRECTION"]);
            if($DIRECTION<0)
                $DIRECTION=0;
            elseif($DIRECTION>1 )
                $DIRECTION=1;
            qv_appendcomma($sets,"DIRECTION=$DIRECTION");
        }
        
        // DETERMINO REGISTRY
        $clobs=false;
        if(isset($data["REGISTRY"])){
            qv_setclob($maestro, "REGISTRY", $data["REGISTRY"], $REGISTRY, $clobs);
            qv_appendcomma($sets,"REGISTRY=$REGISTRY");
        }

        // RISOLVO I TIPI GESTITI NEL TIPO MOTIVE
        $fields=qv_getrecord($maestro, "QVMOTIVETYPES", $TYPOLOGYID, "OBJECTTYPEID");
        $TPOBJECTTYPEID=$fields["OBJECTTYPEID"];

        // DETERMINO REFERENCEID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTS", "REFERENCEID", "REFERENCENAME", $REFERENCEID, "TYPOLOGYID");
        if($REFERENCEID!=""){
            $OBJECTTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE L'OGGETTO SIA COMPATIBILE COL TIPO MOTIVE
            if($OBJECTTYPEID!=$TPOBJECTTYPEID){
                $babelcode="QVERR_OBJECTCONFLICT";
                $b_params=array();
                $b_pattern="L'oggetto non è compatibile col tipo oggetto del motivo";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"REFERENCEID='$REFERENCEID'");
        }
        else{
            if($fields){
                qv_appendcomma($sets,"REFERENCEID='$REFERENCEID'");
            }
        }
        
        // DETERMINO COUNTERPARTID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTS", "COUNTERPARTID", "COUNTERPARTNAME", $COUNTERPARTID, "TYPOLOGYID");
        if($COUNTERPARTID!=""){
            $OBJECTTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE L'OGGETTO SIA COMPATIBILE COL TIPO MOTIVE
            if($OBJECTTYPEID!=$TPOBJECTTYPEID){
                $babelcode="QVERR_OBJECTCONFLICT";
                $b_params=array();
                $b_pattern="L'oggetto non è compatibile col tipo oggetto del motivo";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"COUNTERPARTID='$COUNTERPARTID'");
        }
        else{
            if($fields){
                qv_appendcomma($sets,"COUNTERPARTID='$COUNTERPARTID'");
            }
        }
        
        // DETERMINO CONSISTENCY
        if(isset($data["CONSISTENCY"])){
            $CONSISTENCY=intval($data["CONSISTENCY"]);
            if($CONSISTENCY<0 || $CONSISTENCY>3 )
                $CONSISTENCY=0;
            qv_appendcomma($sets,"CONSISTENCY=$CONSISTENCY");
        }
        
        // DETERMINO SCOPE
        if(isset($data["SCOPE"])){
            $SCOPE=intval($data["SCOPE"]);
            if($SCOPE<0 || $SCOPE>2 )
                $SCOPE=0;
            qv_appendcomma($sets,"SCOPE=$SCOPE");
        }
        
        // DETERMINO UPDATING
        if(isset($data["UPDATING"])){
            $UPDATING=intval($data["UPDATING"]);
            if($UPDATING<0 || $UPDATING>2 )
                $UPDATING=0;
            qv_appendcomma($sets,"UPDATING=$UPDATING");
        }
        
        // DETERMINO DELETING
        if(isset($data["DELETING"])){
            $DELETING=intval($data["DELETING"]);
            if($DELETING<0 || $DELETING>2 )
                $DELETING=0;
            qv_appendcomma($sets,"DELETING=$DELETING");
        }
        
        // DETERMINO STATUS
        if(isset($data["STATUS"])){
            $STATUS=intval($data["STATUS"]);
            if($STATUS<0 || $STATUS>3 )
                $STATUS=0;
            qv_appendcomma($sets,"STATUS=$STATUS");
        }
        
        // DETERMINO DISCHARGE
        if(isset($data["DISCHARGE"])){
            $DISCHARGE=intval($data["DISCHARGE"]);
            if($DISCHARGE<0 || $DISCHARGE>3 )
                $DISCHARGE=0;
            qv_appendcomma($sets,"DISCHARGE=$DISCHARGE");
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
            _qv_historicizing($maestro, "QVMOTIVE", $SYSID, $TYPOLOGYID, 1);

            // PREDISPONGO COLONNE E VALORI DA REGISTRARE
            $sql="UPDATE QVMOTIVES SET $sets WHERE SYSID='$SYSID'";
            if(!maestro_execute($maestro, $sql, false, $clobs)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"] );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        // GESTIONE DEI DATI ESTESI
        qv_extension($maestro, $data, "QVMOTIVE", $SYSID, $TYPOLOGYID, 1);

        // TRIGGER PERSONALIZZATO
        qv_triggermotive($maestro, $data, $SYSID, $TYPOLOGYID, 1);
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