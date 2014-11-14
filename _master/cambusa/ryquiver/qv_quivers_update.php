<?php 
/****************************************************************************
* Name:            qv_quivers_update.php                                    *
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
include_once "../rymaestro/maestro_querylib.php";
function qv_quivers_update($maestro, $data){
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
        $record=qv_solverecord($maestro, $data, "QVQUIVERS", "SYSID", "NAME", $SYSID, "TYPOLOGYID,STATUS,AVAILABILITY");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $TYPOLOGYID=$record["TYPOLOGYID"];
        $REG_STATUS=intval($record["STATUS"]);
        $REG_AVAILABILITY=intval($record["AVAILABILITY"]);
        
        // VALIDAZIONE PERSONALIZZATA
        qv_validatequiver($maestro, $data, $SYSID, $TYPOLOGYID, 1);
        
        // SE NAME E' IN MODIFICA LO VALIDO
        if(isset($data["SYSID"]) && isset($data["NAME"])){
            $NAME=$data["NAME"];
            qv_checkname($maestro, "QVQUIVERS", $SYSID, $NAME);
            qv_appendcomma($sets, "NAME='$NAME'");
        }
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize(qv_inputUTF8($data["DESCRIPTION"]), 200);
            if($DESCRIPTION=="")
                $DESCRIPTION=$NAME;
            qv_appendcomma($sets,"DESCRIPTION='$DESCRIPTION'");
        }
            
        // DETERMINO REGISTRY
        $clobs=false;
        if(isset($data["REGISTRY"])){
            qv_setclob($maestro, "REGISTRY", $data["REGISTRY"], $REGISTRY, $clobs);
            qv_appendcomma($sets,"REGISTRY=$REGISTRY");
        }

        // DETERMINO AUXTIME
        if(isset($data["AUXTIME"])){
            $AUXTIME=qv_escapizetime($data["AUXTIME"], LOWEST_TIME);
            $AUXTIME="[:TIME($AUXTIME)]";
            qv_appendcomma($sets,"AUXTIME=$AUXTIME");
        }

        // DETERMINO AUXAMOUNT
        if(isset($data["AUXAMOUNT"])){
            $AUXAMOUNT=floatval($data["AUXAMOUNT"]);
            qv_appendcomma($sets,"AUXAMOUNT=$AUXAMOUNT");
        }

        // DETERMINO STATUS
        if(isset($data["STATUS"])){
            $STATUS=intval($data["STATUS"]);
            if($STATUS>=0 && $STATUS<=3){
                qv_appendcomma($sets,"STATUS=$STATUS");
            }
        }
        else{
            $STATUS=$REG_STATUS;
        }
        
        // DETERMINO STATUSTIME
        if(isset($data["STATUSTIME"])){
            $STATUSTIME=qv_escapizetime($data["STATUSTIME"], LOWEST_TIME);
            $STATUSTIME="[:TIME($STATUSTIME)]";
            qv_appendcomma($sets,"STATUSTIME=$STATUSTIME");
        }
        else{
            if($STATUS!=$REG_STATUS){
                $STATUSTIME=date("YmdHis");
                $babelparams["STATUSTIME"]=$STATUSTIME;
                $STATUSTIME="[:TIME($STATUSTIME)]";
                qv_appendcomma($sets,"STATUSTIME=$STATUSTIME");
            }
        }
        
        // RISOLVO I TIPI GESTITI NEL TIPO QUIVER
        $fields=qv_getrecord($maestro, "QVQUIVERTYPES", $TYPOLOGYID, "GENRETYPEID,OBJECTTYPEID,MOTIVETYPEID,ARROWTYPEID,QUIVERTYPEID");
        $TPGENRETYPEID=$fields["GENRETYPEID"];
        $TPOBJECTTYPEID=$fields["OBJECTTYPEID"];
        $TPMOTIVETYPEID=$fields["MOTIVETYPEID"];
        $TPARROWTYPEID=$fields["ARROWTYPEID"];
        $TPQUIVERTYPEID=$fields["QUIVERTYPEID"];

        // DETERMINO REFGENREID
        $fields=qv_solverecord($maestro, $data, "QVGENRES", "REFGENREID", "REFGENRENAME", $REFGENREID, "TYPOLOGYID");
        if($REFGENREID!=""){
            $GENRETYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE IL GENERE SIA COMPATIBILE COL TIPO QUIVER
            if($GENRETYPEID!=$TPGENRETYPEID){
                $babelcode="QVERR_GENRECONFLICT";
                $b_params=array();
                $b_pattern="Il genere non è compatibile col tipo genere quiver";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"REFGENREID='$REFGENREID'");
        }
        else{
            if($fields){
                qv_appendcomma($sets,"REFGENREID='$REFGENREID'");
            }
        }

        // DETERMINO REFOBJECTID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTS", "REFOBJECTID", "REFOBJECTNAME", $REFOBJECTID, "TYPOLOGYID");
        if($REFOBJECTID!=""){
            $OBJECTTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE L'OGGETTO SIA COMPATIBILE COL TIPO QUIVER
            if($OBJECTTYPEID!=$TPOBJECTTYPEID){
                $babelcode="QVERR_OBJECTCONFLICT";
                $b_params=array();
                $b_pattern="L'oggetto non è compatibile col tipo oggetto quiver";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"REFOBJECTID='$REFOBJECTID'");
        }
        else{
            if($fields){
                qv_appendcomma($sets,"REFOBJECTID='$REFOBJECTID'");
            }
        }
        
        // DETERMINO REFMOTIVEID
        $fields=qv_solverecord($maestro, $data, "QVMOTIVES", "REFMOTIVEID", "REFMOTIVENAME", $REFMOTIVEID, "TYPOLOGYID");
        if($REFMOTIVEID!=""){
            $MOTIVETYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE IL MOTIVO SIA COMPATIBILE COL TIPO QUIVER
            if($MOTIVETYPEID!=$TPMOTIVETYPEID){
                $babelcode="QVERR_MOTIVECONFLICT";
                $b_params=array();
                $b_pattern="Il motivo non è compatibile col tipo motivo quiver";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"REFMOTIVEID='$REFMOTIVEID'");
        }
        else{
            if($fields){
                qv_appendcomma($sets,"REFMOTIVEID='$REFMOTIVEID'");
            }
        }

        // DETERMINO REFARROWID
        $fields=qv_solverecord($maestro, $data, "QVARROWS", "REFARROWID", "REFARROWNAME", $REFARROWID, "TYPOLOGYID");
        if($REFARROWID!=""){
            $ARROWTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE LA FRECCIA SIA COMPATIBILE COL TIPO QUIVER
            if($ARROWTYPEID!=$TPARROWTYPEID){
                $babelcode="QVERR_ARROWCONFLICT";
                $b_params=array();
                $b_pattern="La freccia non è compatibile col tipo freccia quiver";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"REFARROWID='$REFARROWID'");
        }
        else{
            if($fields){
                qv_appendcomma($sets,"REFARROWID='$REFARROWID'");
            }
        }

        // DETERMINO REFQUIVERID
        $fields=qv_solverecord($maestro, $data, "QVQUIVERS", "REFQUIVERID", "REFQUIVERNAME", $REFQUIVERID, "TYPOLOGYID");
        if($REFQUIVERID!=""){
            $QUIVERTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE IL QUIVER SIA COMPATIBILE COL TIPO QUIVER DEL QUIVER
            if($QUIVERTYPEID!=$TPQUIVERTYPEID){
                $babelcode="QVERR_QUIVERCONFLICT";
                $b_params=array();
                $b_pattern="Il quiver di riferimento non è compatibile col tipo quiver";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"REFQUIVERID='$REFQUIVERID'");
        }
        else{
            if($fields){
                qv_appendcomma($sets,"REFQUIVERID='$REFQUIVERID'");
            }
        }

        // DETERMINO REFERENCE
        if(isset($data["REFERENCE"])){
            $REFERENCE=ryqEscapize($data["REFERENCE"], 50);
            qv_appendcomma($sets,"REFERENCE='$REFERENCE'");
        }
        
        // DETERMINO TAG
        if(isset($data["TAG"])){
            $TAG=ryqEscapize($data["TAG"], 200);
            qv_appendcomma($sets,"TAG='$TAG'");
        }
        
        // DETERMINO CONSISTENCY
        if(isset($data["CONSISTENCY"])){
            $CONSISTENCY=intval($data["CONSISTENCY"]);
            if($CONSISTENCY>=0 && $CONSISTENCY<=3){
                qv_appendcomma($sets,"CONSISTENCY=$CONSISTENCY");
            }
        }
        
        // DETERMINO AVAILABILITY
        $locked=false;
        if(isset($data["AVAILABILITY"])){
            $AVAILABILITY=intval($data["AVAILABILITY"]);
            if($AVAILABILITY<0 || $AVAILABILITY>2 )
                $AVAILABILITY=$REG_AVAILABILITY;
            qv_appendcomma($sets,"AVAILABILITY=$AVAILABILITY");
            if($AVAILABILITY>0 && $REG_AVAILABILITY>0){
                $locked=true;
            }
        }
        else{
            $AVAILABILITY=$REG_AVAILABILITY;
            if($REG_AVAILABILITY>0){
                $locked=true;
            }
        }
        if($locked){
            // SOLO ALCUNI VALORI SONO MODIFICABILI
            // SE SONO PASSATI CAMPI NON PERMESSI DO ERRORE
            foreach($data as $key => $value){
                switch($key){
                case "SYSID":
                case "NAME":
                case "AVAILABILITY":
                case "STATUS":
                case "PHASE":
                case "PHASENOTE":
                    break;
                default:
                    $babelcode="QVERR_ARROWLOCK";
                    $b_params=array("SYSID" => $SYSID, $key => $value);
                    $b_pattern="Il quiver [{1}] è bloccata e il campo [{2}] non è modificabile";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
        }
        
        // DETERMINO SCOPE
        if(isset($data["SCOPE"])){
            $SCOPE=intval($data["SCOPE"]);
            if($SCOPE>=0 && $SCOPE<=2){
                qv_appendcomma($sets,"SCOPE=$SCOPE");
            }
        }

        // DETERMINO UPDATING
        if(isset($data["UPDATING"])){
            $UPDATING=intval($data["UPDATING"]);
            if($UPDATING>=0 && $UPDATING<=2){
                qv_appendcomma($sets,"UPDATING=$UPDATING");
            }
        }

        // DETERMINO DELETING
        if(isset($data["DELETING"])){
            $DELETING=intval($data["DELETING"]);
            if($DELETING>=0 && $DELETING<=2){
                qv_appendcomma($sets,"DELETING=$DELETING");
            }
        }

        // DETERMINO PHASE
        if(isset($data["PHASE"])){
            $PHASE=intval($data["PHASE"]);
            if($PHASE<0 || $PHASE>3 )
                $PHASE=0;
            qv_appendcomma($sets,"PHASE=$PHASE");
        }
        
        // DETERMINO PHASENOTE
        if(isset($data["PHASENOTE"])){
            $PHASENOTE=ryqEscapize($data["PHASENOTE"], 100);
            qv_appendcomma($sets,"PHASENOTE='$PHASENOTE'");
        }
        
        // DETERMINO MOREDATA
        if(isset($data["MOREDATA"])){
            $MOREDATA=ryqEscapize($data["MOREDATA"], 1000);
            if($MOREDATA!=""){
                if(!json_decode($MOREDATA)){
                    $babelcode="QVERR_MOREDATA";
                    $b_params=array();
                    $b_pattern="Documento JSON [MOREDATA] non corretto o troppo esteso";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
            qv_appendcomma($sets,"MOREDATA='$MOREDATA'");
        }
        
        $USERUPDATEID=$global_quiveruserid;
        qv_appendcomma($sets,"USERUPDATEID='$USERUPDATEID'");
        
        $TIMEUPDATE="[:NOW()]";
        qv_appendcomma($sets,"TIMEUPDATE=$TIMEUPDATE");
        
        if($sets!=""){
            // GESTIONE DELLA STORICIZZAZIONE
            _qv_historicizing($maestro, "QVQUIVER", $SYSID, $TYPOLOGYID, 1);

            // PREDISPONGO COLONNE E VALORI DA REGISTRARE
            $sql="UPDATE QVQUIVERS SET $sets WHERE SYSID='$SYSID'";
        
            if(!maestro_execute($maestro, $sql, false, $clobs)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"] );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        // GESTIONE DEI DATI ESTESI
        qv_extension($maestro, $data, "QVQUIVER", $SYSID, $TYPOLOGYID, 1);

        // TRIGGER PERSONALIZZATO
        qv_triggerquiver($maestro, $data, $SYSID, $TYPOLOGYID, 1);
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