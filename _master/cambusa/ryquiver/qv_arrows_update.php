<?php 
/****************************************************************************
* Name:            qv_arrows_update.php                                    *
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
function qv_arrows_update($maestro, $data){
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
        $record=qv_solverecord($maestro, $data, "QVARROWS", "SYSID", "NAME", $SYSID, "NAME,TYPOLOGYID,BOWID,BOWTIME,TARGETID,TARGETTIME,AUXTIME,STATUSTIME,MOTIVEID,GENREID,CONSISTENCY,AVAILABILITY,SCOPE,UPDATING,DELETING,STATUS,PHASE");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $NAME=$record["NAME"];
        $TYPOLOGYID=$record["TYPOLOGYID"];
        $REG_BOWID=$record["BOWID"];
        $REG_BOWTIME=qv_strtime($record["BOWTIME"]);
        $REG_TARGETID=$record["TARGETID"];
        $REG_TARGETTIME=qv_strtime($record["TARGETTIME"]);
        $REG_AUXTIME=qv_strtime($record["AUXTIME"]);
        $REG_STATUSTIME=qv_strtime($record["STATUSTIME"]);
        $REG_MOTIVEID=$record["MOTIVEID"];
        $REG_GENREID=$record["GENREID"];
        $REG_CONSISTENCY=intval($record["CONSISTENCY"]);
        $REG_AVAILABILITY=intval($record["AVAILABILITY"]);
        $REG_SCOPE=intval($record["SCOPE"]);
        $REG_UPDATING=intval($record["UPDATING"]);
        $REG_DELETING=intval($record["DELETING"]);
        $REG_STATUS=intval($record["STATUS"]);
        $REG_PHASE=intval($record["PHASE"]);
        
        // SE NAME E' IN MODIFICA LO VALIDO
        if(isset($data["SYSID"]) && isset($data["NAME"])){
            $NAME=$data["NAME"];
            qv_checkname($maestro, "QVARROWS", $SYSID, $NAME);
            qv_appendcomma($sets, "NAME='$NAME'");
        }
        
        // VALIDAZIONE PERSONALIZZATA
        qv_validatearrow($maestro, $data, $SYSID, $TYPOLOGYID, 1);
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize(qv_inputUTF8($data["DESCRIPTION"]), 100);
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
        
        // RISOLVO I TIPI GESTITI NEL TIPO FRECCIA
        $fields=qv_getrecord($maestro, "QVARROWTYPES", $TYPOLOGYID, "GENRETYPEID,MOTIVETYPEID,BOWTYPEID,TARGETTYPEID");
        $TPGENRETYPEID=$fields["GENRETYPEID"];
        $TPMOTIVETYPEID=$fields["MOTIVETYPEID"];
        $TPBOWTYPEID=$fields["BOWTYPEID"];
        $TPTARGETTYPEID=$fields["TARGETTYPEID"];
        
        // GESTIONE TIME UNIT
        $BOWUNIT="";
        $TARGETUNIT="";
        if($TPBOWTYPEID==$TPTARGETTYPEID){
            maestro_query($maestro,"SELECT TIMEUNIT FROM QVOBJECTTYPES WHERE SYSID='$TPBOWTYPEID'", $r);
            if(count($r)==1){
                $BOWUNIT=$r[0]["TIMEUNIT"];
                $TARGETUNIT=$r[0]["TIMEUNIT"];
            }
        }
        else{
            maestro_query($maestro,"SELECT TIMEUNIT FROM QVOBJECTTYPES WHERE SYSID='$TPBOWTYPEID'", $r);
            if(count($r)==1){
                $BOWUNIT=$r[0]["TIMEUNIT"];
            }
            maestro_query($maestro,"SELECT TIMEUNIT FROM QVOBJECTTYPES WHERE SYSID='$TPTARGETTYPEID'", $r);
            if(count($r)==1){
                $TARGETUNIT=$r[0]["TIMEUNIT"];
            }
        }
        
        if($BOWUNIT==""){
            $babelcode="QVERR_NOTYPOLOGY";
            $b_params=array("SYSID" => $TPBOWTYPEID, "table" => "QVOBJECTTYPES");
            $b_pattern="Tipologia [{1}] non trovata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        elseif($TARGETUNIT==""){
            $babelcode="QVERR_NOTYPOLOGY";
            $b_params=array("SYSID" => $TPTARGETTYPEID, "table" => "QVOBJECTTYPES");
            $b_pattern="Tipologia [{1}] non trovata";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO GENREID
        $fields=qv_solverecord($maestro, $data, "QVGENRES", "GENREID", "GENRENAME", $GENREID, "TYPOLOGYID,ROUNDING");
        if($GENREID!=""){
            $GENRETYPEID=$fields["TYPOLOGYID"];
            $ROUNDING=intval($fields["ROUNDING"]);
            // VERIFICO CHE IL GENERE SIA COMPATIBILE COL TIPO FRECCIA
            if($GENRETYPEID!=$TPGENRETYPEID){
                $babelcode="QVERR_GENRECONFLICT";
                $b_params=array();
                $b_pattern="Il genere non è compatibile col tipo freccia";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"GENREID='$GENREID'");
        }
        else{
            if($fields){
                $babelcode="QVERR_GENREID";
                $b_params=array();
                $b_pattern="Genere non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            else{
                qv_solverounding($maestro, $REG_GENREID, $ROUNDING);
            }
        }

        // DETERMINO MOTIVEID
        if(!isset($data["MOTIVEID"]) && !isset($data["MOTIVENAME"])){
            $data["MOTIVEID"]=$REG_MOTIVEID;
        }
        $fields=qv_solverecord($maestro, $data, "QVMOTIVES", "MOTIVEID", "MOTIVENAME", $MOTIVEID, "TYPOLOGYID,DIRECTION,STATUS,CONSISTENCY");
        if($MOTIVEID!=""){
            $MOTIVETYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE IL MOTIVO SIA COMPATIBILE COL TIPO FRECCIA
            if($MOTIVETYPEID!=$TPMOTIVETYPEID){
                $babelcode="QVERR_MOTIVECONFLICT";
                $b_params=array();
                $b_pattern="Il motivo non è compatibile col tipo freccia";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"MOTIVEID='$MOTIVEID'");
            $MOTIVE_DIRECTION=intval($fields["DIRECTION"]);;
            $MOTIVE_STATUS=intval($fields["STATUS"]);    // Non deve essere -1
            $MOTIVE_CONSISTENCY=intval($fields["CONSISTENCY"]);;
        }
        else{
            if($fields){
                $babelcode="QVERR_MOTIVEID";
                $b_params=array();
                $b_pattern="Motivo non specificato";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            else{
                // DOVREBBE ESSERE IMPOSSIBILE PASSARE DI QUI
                $MOTIVEID=$REG_MOTIVEID;
                $MOTIVE_DIRECTION=0;
                $MOTIVE_STATUS=0;
                $MOTIVE_CONSISTENCY=-1;
            }
        }

        // GESTIONE REFERENCEID
        if(isset($data["REFERENCEID"])){
            $REFERENCEID=$data["REFERENCEID"];
            if($MOTIVE_DIRECTION==0)
                $data["BOWID"]=$REFERENCEID;
            elseif($MOTIVE_DIRECTION==1)
                $data["TARGETID"]=$REFERENCEID;
        }
        
        // GESTIONE COUNTERPARTID
        if(isset($data["COUNTERPARTID"])){
            $COUNTERPARTID=$data["COUNTERPARTID"];
            if($MOTIVE_DIRECTION==0)
                $data["TARGETID"]=$COUNTERPARTID;
            elseif($MOTIVE_DIRECTION==1)
                $data["BOWID"]=$COUNTERPARTID;
        }
        
        // DETERMINO BOWID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTS", "BOWID", "BOWNAME", $BOWID, "BEGINTIME,ENDTIME,TYPOLOGYID");
        if($BOWID!=""){
            $BOWTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE L'OGGETTO SIA COMPATIBILE COL TIPO FRECCIA
            if($BOWTYPEID!=$TPBOWTYPEID){
                $babelcode="QVERR_BOWCONFLICT";
                $b_params=array();
                $b_pattern="L'oggetto di partenza non è compatibile col tipo freccia";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            $BOWBEGIN=qv_strtime($fields["BEGINTIME"]);
            $BOWEND=qv_strtime($fields["ENDTIME"]);
            qv_appendcomma($sets,"BOWID='$BOWID'");
        }
        else{
            if($fields){    // $BOWID E' PASSATO VUOTO
                $BOWBEGIN=LOWEST_TIME;
                $BOWEND=HIGHEST_TIME;
                qv_appendcomma($sets,"BOWID='$BOWID'");
            }
            elseif($REG_BOWID!=""){
                $BOWID=$REG_BOWID;
                $fields=qv_getrecord($maestro, "QVOBJECTS", $BOWID, "BEGINTIME,ENDTIME,TYPOLOGYID");
                $BOWBEGIN=qv_strtime($fields["BEGINTIME"]);
                $BOWEND=qv_strtime($fields["ENDTIME"]);
            }
            else{
                $BOWBEGIN=LOWEST_TIME;
                $BOWEND=HIGHEST_TIME;
            }
        }
        
        // DETERMINO TARGETID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTS", "TARGETID", "TARGETNAME", $TARGETID, "BEGINTIME,ENDTIME,TYPOLOGYID");
        if($TARGETID!=""){
            $TARGETTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE L'OGGETTO SIA COMPATIBILE COL TIPO FRECCIA
            if($TARGETTYPEID!=$TPTARGETTYPEID){
                $babelcode="QVERR_TARGETCONFLICT";
                $b_params=array();
                $b_pattern="L'oggetto di arrivo non è compatibile col tipo freccia";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            $TARGETBEGIN=qv_strtime($fields["BEGINTIME"]);
            $TARGETEND=qv_strtime($fields["ENDTIME"]);
            qv_appendcomma($sets,"TARGETID='$TARGETID'");
        }
        else{
            if($fields){    // $TARGETID E' PASSATO VUOTO
                $TARGETBEGIN=LOWEST_TIME;
                $TARGETEND=HIGHEST_TIME;
                qv_appendcomma($sets,"TARGETID='$TARGETID'");
            }
            elseif($REG_TARGETID!=""){
                $TARGETID=$REG_TARGETID;
                $fields=qv_getrecord($maestro, "QVOBJECTS", $TARGETID, "BEGINTIME,ENDTIME,TYPOLOGYID");
                $TARGETBEGIN=qv_strtime($fields["BEGINTIME"]);
                $TARGETEND=qv_strtime($fields["ENDTIME"]);
            }
            else{
                $TARGETBEGIN=LOWEST_TIME;
                $TARGETEND=HIGHEST_TIME;
            }
        }

        // DETERMINO BOWTIME
        if(isset($data["BOWTIME"]))
            $BOWTIME=qv_escapizetime($data["BOWTIME"], LOWEST_TIME);
        else
            $BOWTIME=$REG_BOWTIME;
        if("D$BOWTIME"<"D$BOWBEGIN" || "D$BOWEND"<"D$BOWTIME"){
            $babelcode="QVERR_BOWTIMEOUT";
            $b_params=array("BOWTIME" => $BOWTIME);
            $b_pattern="Inizio non compatibile";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO TARGETTIME
        if(isset($data["TARGETTIME"]))
            $TARGETTIME=qv_escapizetime($data["TARGETTIME"], HIGHEST_TIME);
        else
            $TARGETTIME=$REG_TARGETTIME;
        if("D$TARGETTIME"<"D$TARGETBEGIN" || "D$TARGETEND"<"D$TARGETTIME"){
            $babelcode="QVERR_TARGETTIMEOUT";
            $b_params=array("TARGETTIME" => $TARGETTIME);
            $b_pattern="Fine non compatibile";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // DETERMINO AUXTIME
        if(isset($data["AUXTIME"]))
            $AUXTIME=qv_escapizetime($data["AUXTIME"], LOWEST_TIME);
        else
            $AUXTIME=$REG_AUXTIME;
        
        // DETERMINO STATUS
        if(isset($data["STATUS"])){
            $STATUS=intval($data["STATUS"]);
            if($STATUS<0 || $STATUS>3 ){
                $STATUS=$REG_STATUS;
            }
        }
        else{
            $STATUS=$REG_STATUS;
        }
        if($STATUS==-1){
            $STATUS=$MOTIVE_STATUS;
            $babelparams["STATUS"]=$STATUS;
        }
        if($STATUS!=$REG_STATUS){
            qv_appendcomma($sets,"STATUS=$STATUS");
        }
        
        // DETERMINO STATUSTIME
        if(isset($data["STATUSTIME"])){
            $STATUSTIME=qv_escapizetime($data["STATUSTIME"], LOWEST_TIME);
        }
        else{
            if($STATUS!=$REG_STATUS && $REG_STATUS<=0){
                $STATUSTIME=date("YmdHis");
                $babelparams["STATUSTIME"]=$STATUSTIME;
                // SEGNALO DI ANNOVERARLO NEI CAMPI DA SETTARE
                $data["STATUSTIME"]=$STATUSTIME;
            }
            else{
                $STATUSTIME=$REG_STATUSTIME;
            }
        }

        // COMPLETO PER SQL
        if($BOWUNIT=="S" || $TARGETUNIT=="S"){
            $BOWTIME="[:TIME($BOWTIME)]";
            $TARGETTIME="[:TIME($TARGETTIME)]";
            $AUXTIME="[:TIME($AUXTIME)]";
            $STATUSTIME="[:TIME($STATUSTIME)]";
        }
        else{
            $BOWTIME="[:DATE($BOWTIME)]";
            $TARGETTIME="[:DATE($TARGETTIME)]";
            $AUXTIME="[:DATE($AUXTIME)]";
            $STATUSTIME="[:DATE($STATUSTIME)]";
        }
        if(isset($data["BOWTIME"]))
            qv_appendcomma($sets,"BOWTIME=$BOWTIME");
        if(isset($data["TARGETTIME"]))
            qv_appendcomma($sets,"TARGETTIME=$TARGETTIME");
        if(isset($data["AUXTIME"]))
            qv_appendcomma($sets,"AUXTIME=$AUXTIME");
        if(isset($data["STATUSTIME"]))
            qv_appendcomma($sets,"STATUSTIME=$STATUSTIME");

        // DETERMINO AMOUNT
        if(isset($data["AMOUNT"])){
            $AMOUNT=round(floatval($data["AMOUNT"]), $ROUNDING );
            qv_appendcomma($sets,"AMOUNT=$AMOUNT");
        }
        
        // DETERMINO STATUSRISK
        if(isset($data["STATUSRISK"])){
            $STATUSRISK=round(floatval($data["STATUSRISK"]), 4 );
            qv_appendcomma($sets,"STATUSRISK=$STATUSRISK");
        }
        
        // DETERMINO REFERENCE
        if(isset($data["REFERENCE"])){
            $REFERENCE=ryqEscapize($data["REFERENCE"], 50);
            qv_appendcomma($sets,"REFERENCE='$REFERENCE'");
        }
        
        // DETERMINO REFARROWID
        $fields=qv_solverecord($maestro, $data, "QVARROWS", "REFARROWID", "REFARROWNAME", $REFARROWID, "TYPOLOGYID");
        if($REFARROWID!=""){
            // VERIFICO L'ASSENZA DI CICLICITA'
            qv_cyclicity($maestro, "QVARROWS", "REFARROWID", $SYSID, $REFARROWID);
            qv_appendcomma($sets,"REFARROWID='$REFARROWID'");
        }
        else{
            if($fields){
                qv_appendcomma($sets,"REFARROWID='$REFARROWID'");
            }
        }
        
        // DETERMINO TAG
        if(isset($data["TAG"])){
            $TAG=ryqEscapize($data["TAG"], 200);
            qv_appendcomma($sets,"TAG='$TAG'");
        }

        // DETERMINO PROVIDER
        if(isset($data["PROVIDER"])){
            $PROVIDER=ryqEscapize($data["PROVIDER"], 20);
            qv_appendcomma($sets,"PROVIDER='$PROVIDER'");
        }

        // DETERMINO PARCEL
        if(isset($data["PARCEL"])){
            $PARCEL=ryqEscapize($data["PARCEL"], 20);
            qv_appendcomma($sets,"PARCEL='$PARCEL'");
        }
        
        // DETERMINO CONSISTENCY
        if(isset($data["CONSISTENCY"])){
            $CONSISTENCY=intval($data["CONSISTENCY"]);
            if($CONSISTENCY<0 || $CONSISTENCY>3 )
                $CONSISTENCY=$REG_CONSISTENCY;
            qv_appendcomma($sets,"CONSISTENCY=$CONSISTENCY");
        }
        else{
            // SE HO CAMBIATO SOLTANTO MOTIVEID, LE CONSISTENCY DEVONO ESSERE UGUALI
            if($MOTIVEID!=$REG_MOTIVEID){
                if($REG_CONSISTENCY!=$MOTIVE_CONSISTENCY){
                    $babelcode="QVERR_CONSISTENCY";
                    $b_params=array("SYSID" => $SYSID, "CONSISTENCY" => $REG_CONSISTENCY);
                    $b_pattern="Il nuovo motivo ha una 'concretezza' diversa dalla freccia";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
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
                    $b_pattern="La freccia [{1}] è bloccata e il campo [{2}] non è modificabile";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
        }
        
        // DETERMINO SCOPE
        if(isset($data["SCOPE"])){
            $SCOPE=intval($data["SCOPE"]);
            if($SCOPE<0 || $SCOPE>2 )
                $SCOPE=$REG_SCOPE;
            qv_appendcomma($sets,"SCOPE=$SCOPE");
        }
        
        // DETERMINO UPDATING
        if(isset($data["UPDATING"])){
            $UPDATING=intval($data["UPDATING"]);
            if($UPDATING<0 || $UPDATING>2 )
                $UPDATING=$REG_UPDATING;
            qv_appendcomma($sets,"UPDATING=$UPDATING");
        }
        
        // DETERMINO DELETING
        if(isset($data["DELETING"])){
            $DELETING=intval($data["DELETING"]);
            if($DELETING<0 || $DELETING>2 )
                $DELETING=$REG_DELETING;
            qv_appendcomma($sets,"DELETING=$DELETING");
        }
        
        // DETERMINO PHASE
        if(isset($data["PHASE"])){
            $PHASE=intval($data["PHASE"]);
            if($PHASE<0 || $PHASE>3 )
                $PHASE=$REG_PHASE;
            qv_appendcomma($sets,"PHASE=$PHASE");
        }
        
        // DETERMINO PHASENOTE
        if(isset($data["PHASENOTE"])){
            $PHASENOTE=ryqEscapize($data["PHASENOTE"], 100);
            qv_appendcomma($sets,"PHASENOTE='$PHASENOTE'");
        }
        
        $USERUPDATEID=$global_quiveruserid;
        qv_appendcomma($sets, "USERUPDATEID='$USERUPDATEID'");
        
        $TIMEUPDATE="[:NOW()]";
        qv_appendcomma($sets, "TIMEUPDATE=$TIMEUPDATE");
        
        if($sets!=""){
            // GESTIONE DELLA STORICIZZAZIONE
            _qv_historicizing($maestro, "QVARROW", $SYSID, $TYPOLOGYID, 1);

            // PREDISPONGO COLONNE E VALORI DA REGISTRARE
            $sql="UPDATE QVARROWS SET $sets WHERE SYSID='$SYSID'";
        
            if(!maestro_execute($maestro, $sql, false, $clobs)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"], "STATEMENT" => "Updating" );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        // GESTIONE DEI DATI ESTESI
        qv_extension($maestro, $data, "QVARROW", $SYSID, $TYPOLOGYID, 1);
        
        // TRIGGER PERSONALIZZATO
        qv_triggerarrow($maestro, $data, $SYSID, $TYPOLOGYID, 1);
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