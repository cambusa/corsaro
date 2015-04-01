<?php 
/****************************************************************************
* Name:            qv_arrows_insert.php                                     *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverinf.php";
include_once "quiverval.php";
include_once "quivertrg.php";
include_once "quiverext.php";
include_once "quiverarw.php";
include_once $path_cambusa."rymaestro/maestro_querylib.php";
function qv_arrows_insert($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // RISOLVO LE INFO DI SESSIONE
        qv_infosession($maestro);
        
        // DETERMINO UN NUOVO SYSID
        $SYSID=qv_createsysid($maestro);
        
        // DETERMINO TYPOLOGYID
        $fields=qv_solverecord($maestro, $data, "QVARROWTYPES", "TYPOLOGYID", "TYPOLOGYNAME", $TYPOLOGYID, "GENRETYPEID,MOTIVETYPEID,BOWTYPEID,TARGETTYPEID");
        if($TYPOLOGYID==""){
            $babelcode="QVERR_ARROWTYPES";
            $b_params=array();
            $b_pattern="Tipo di freccia non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
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
        
        // VALIDAZIONE PERSONALIZZATA
        qv_validatearrow($maestro, $data, $SYSID, $TYPOLOGYID, 0);

        // DETERMINO NAME
        if(isset($data["NAME"])){
            $NAME=ryqEscapize($data["NAME"], 50);
            qv_checkname($maestro, "QVARROWS", $SYSID, $NAME);
        }
        else{
            $NAME="__$SYSID";
        }
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize(qv_inputUTF8($data["DESCRIPTION"]), 200);
            if($DESCRIPTION=="")
                $DESCRIPTION=$NAME;
        }
        else{
            $DESCRIPTION=$NAME;
        }
            
        // DETERMINO REGISTRY
        $clobs=false;
        if(isset($data["REGISTRY"]))
            qv_setclob($maestro, "REGISTRY", $data["REGISTRY"], $REGISTRY, $clobs);
        else
            $REGISTRY="''";

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
        }
        else{
            $babelcode="QVERR_GENREID";
            $b_params=array();
            $b_pattern="Genere non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // DETERMINO MOTIVEID
        $fields=qv_solverecord($maestro, $data, "QVMOTIVES", "MOTIVEID", "MOTIVENAME", $MOTIVEID, "*");
        if($MOTIVEID!=""){
            $MOTIVETYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE IL MOTIVO SIA COMPATIBILE COL TIPO FRECCIA
            if($MOTIVETYPEID!=$TPMOTIVETYPEID){
                $babelcode="QVERR_MOTIVECONFLICT";
                $b_params=array();
                $b_pattern="Il motivo non è compatibile col tipo freccia";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            $MOTIVE_DIRECTION=intval($fields["DIRECTION"]);
            $MOTIVE_REFERENCEID=$fields["REFERENCEID"];
            $MOTIVE_COUNTERPARTID=$fields["COUNTERPARTID"];
            $MOTIVE_CONSISTENCY=intval($fields["CONSISTENCY"]);
            $MOTIVE_SCOPE=intval($fields["SCOPE"]);
            $MOTIVE_UPDATING=intval($fields["UPDATING"]);
            $MOTIVE_DELETING=intval($fields["DELETING"]);
            $MOTIVE_STATUS=intval($fields["STATUS"]); // Non deve essere -1
            $MOTIVE_DISCHARGE=intval($fields["DISCHARGE"]);
        }
        else{
            $babelcode="QVERR_MOTIVEID";
            $b_params=array();
            $b_pattern="Motivo non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // GESTIONE REFERENCEID
        if(isset($data["REFERENCEID"])){
            $MOTIVE_REFERENCEID=$data["REFERENCEID"];
        }
        
        // GESTIONE COUNTERPARTID
        if(isset($data["COUNTERPARTID"])){
            $MOTIVE_COUNTERPARTID=$data["COUNTERPARTID"];
        }
        
        // DETERMINO BOWID
        if(!isset($data["BOWID"]) && !isset($data["BOWNAME"])){
            // VALORE DI DEFAULT PRESO DA QVMOTIVES
            if($MOTIVE_DIRECTION==0)
                $data["BOWID"]=$MOTIVE_REFERENCEID;
            elseif($MOTIVE_DIRECTION==1)
                $data["BOWID"]=$MOTIVE_COUNTERPARTID;
        }
        $fields=qv_solverecord($maestro, $data, "QVOBJECTS", "BOWID", "BOWNAME", $BOWID, "BEGINTIME,ENDTIME,TYPOLOGYID");
        if($BOWID!=""){
            $BOWTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE L'OGGETTO SIA COMPATIBILE COL TIPO FRECCIA
            if($BOWTYPEID!=$TPBOWTYPEID){
                $babelcode="QVERR_OBJECTCONFLICT";
                $b_params=array();
                $b_pattern="L'oggetto di partenza non è compatibile col tipo freccia";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            $BOWBEGIN=qv_strtime($fields["BEGINTIME"]);
            $BOWEND=qv_strtime($fields["ENDTIME"]);
        }
        else{
            $BOWBEGIN=LOWEST_TIME;
            $BOWEND=HIGHEST_TIME;
        }
        
        // DETERMINO TARGETID
        if(!isset($data["TARGETID"]) && !isset($data["TARGETNAME"])){
            // VALORE DI DEFAULT PRESO DA QVMOTIVES
            if($MOTIVE_DIRECTION==0)
                $data["TARGETID"]=$MOTIVE_COUNTERPARTID;
            elseif($MOTIVE_DIRECTION==1)
                $data["TARGETID"]=$MOTIVE_REFERENCEID;
        }
        $fields=qv_solverecord($maestro, $data, "QVOBJECTS", "TARGETID", "TARGETNAME", $TARGETID, "BEGINTIME,ENDTIME,TYPOLOGYID");
        if($TARGETID!=""){
            $TARGETTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE L'OGGETTO SIA COMPATIBILE COL TIPO FRECCIA
            if($TARGETTYPEID!=$TPTARGETTYPEID){
                $babelcode="QVERR_OBJECTCONFLICT";
                $b_params=array();
                $b_pattern="L'oggetto di arrivo non è compatibile col tipo freccia";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            $TARGETBEGIN=qv_strtime($fields["BEGINTIME"]);
            $TARGETEND=qv_strtime($fields["ENDTIME"]);
        }
        else{
            $TARGETBEGIN=LOWEST_TIME;
            $TARGETEND=HIGHEST_TIME;
        }

        // DETERMINO BOWTIME
        if(isset($data["BOWTIME"])){
            $BOWTIME=qv_escapizetime($data["BOWTIME"], LOWEST_TIME);
            if("D$BOWTIME"<"D$BOWBEGIN" || "D$BOWEND"<"D$BOWTIME"){
                $babelcode="QVERR_BOWTIMEOUT";
                $b_params=array("BOWTIME" => $BOWTIME);
                $b_pattern="Inizio non compatibile";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $BOWTIME=date("YmdHis");
            if("D$BOWTIME"<"D$BOWBEGIN"){
                $BOWTIME=$BOWBEGIN;
            }
        }
        // MEMORIZZO BOWTIME PER LO SCARICO
        $PURETIME=$BOWTIME;
        
        // DETERMINO TARGETTIME
        if(isset($data["TARGETTIME"])){
            $TARGETTIME=qv_escapizetime($data["TARGETTIME"], HIGHEST_TIME);
            if("D$TARGETTIME"<"D$TARGETBEGIN" || "D$TARGETEND"<"D$TARGETTIME"){
                $babelcode="QVERR_TARGETTIMEOUT";
                $b_params=array("TARGETTIME" => $TARGETTIME);
                $b_pattern="Fine non compatibile";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $TARGETTIME=date("YmdHis");
            if("D$TARGETTIME"<"D$TARGETBEGIN"){
                $TARGETTIME=$TARGETBEGIN;
            }
        }

        // DETERMINO AUXTIME
        if(isset($data["AUXTIME"]))
            $AUXTIME=qv_escapizetime($data["AUXTIME"], LOWEST_TIME);
        else
            $AUXTIME=date("YmdHis");

        // DETERMINO STATUS
        if(isset($data["STATUS"])){
            $STATUS=intval($data["STATUS"]);
            if($STATUS<-1 || $STATUS>3){
                $STATUS=$MOTIVE_STATUS;
            }
        }
        else{
            $STATUS=$MOTIVE_STATUS;
        }
        
        // DETERMINO STATUSTIME
        if(isset($data["STATUSTIME"]))
            $STATUSTIME=qv_escapizetime($data["STATUSTIME"], LOWEST_TIME);
        else
            $STATUSTIME=date("YmdHis");

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
            
        // DETERMINO AMOUNT
        if(isset($data["AMOUNT"]))
            $AMOUNT=round(floatval($data["AMOUNT"]), $ROUNDING );
        else
            $AMOUNT=0;
        
        // DETERMINO STATUSRISK
        if(isset($data["STATUSRISK"]))
            $STATUSRISK=round(floatval($data["STATUSRISK"]), 4 );
        else
            $STATUSRISK=0;
        
        // DETERMINO REFERENCE
        if(isset($data["REFERENCE"]))
            $REFERENCE=ryqEscapize($data["REFERENCE"], 50);
        else
            $REFERENCE="";

        // DETERMINO REFARROWID
        $fields=qv_solverecord($maestro, $data, "QVARROWS", "REFARROWID", "REFARROWNAME", $REFARROWID, "TYPOLOGYID");
        if($REFARROWID!=""){
            // VERIFICO L'ASSENZA DI CICLICITA'
            qv_cyclicity($maestro, "QVARROWS", "REFARROWID", $SYSID, $REFARROWID);
        }
        
        // DETERMINO TAG
        if(isset($data["TAG"]))
            $TAG=ryqEscapize($data["TAG"], 200);
        else
            $TAG="";

        // DETERMINO PROVIDER
        if(isset($data["PROVIDER"]))
            $PROVIDER=ryqEscapize($data["PROVIDER"], 20);
        else
            $PROVIDER="";

        // DETERMINO PARCEL
        if(isset($data["PARCEL"]))
            $PARCEL=ryqEscapize($data["PARCEL"], 20);
        else
            $PARCEL="";

        // DETERMINO CONSISTENCY
        if(isset($data["CONSISTENCY"])){
            $CONSISTENCY=intval($data["CONSISTENCY"]);
            if($CONSISTENCY<0 || $CONSISTENCY>3 )
                $CONSISTENCY=$MOTIVE_CONSISTENCY;
        }
        else{
            $CONSISTENCY=$MOTIVE_CONSISTENCY;
        }
        
        // DETERMINO AVAILABILITY
        if(isset($data["AVAILABILITY"])){
            $AVAILABILITY=intval($data["AVAILABILITY"]);
            if($AVAILABILITY<0 || $AVAILABILITY>2 )
                $AVAILABILITY=0;
        }
        else{
            $AVAILABILITY=0;
        }
        
        // DETERMINO SCOPE
        if(isset($data["SCOPE"])){
            $SCOPE=intval($data["SCOPE"]);
            if($SCOPE<0 || $SCOPE>2 )
                $SCOPE=$MOTIVE_SCOPE;
        }
        else{
            $SCOPE=$MOTIVE_SCOPE;
        }
        
        // DETERMINO UPDATING
        if(isset($data["UPDATING"])){
            $UPDATING=intval($data["UPDATING"]);
            if($UPDATING<0 || $UPDATING>2 )
                $UPDATING=$MOTIVE_UPDATING;
        }
        else{
            $UPDATING=$MOTIVE_UPDATING;
        }
        
        // DETERMINO DELETING
        if(isset($data["DELETING"])){
            $DELETING=intval($data["DELETING"]);
            if($DELETING<0 || $DELETING>2 )
                $DELETING=$MOTIVE_DELETING;
        }
        else{
            $DELETING=$MOTIVE_DELETING;
        }
        
        $PHASE=0;
        $PHASENOTE="";
        
        // CAMPI AMMINISTRATIRVI
        $DELETED=0;
        $ROLEID=$global_quiverroleid;
        $USERINSERTID=$global_quiveruserid;
        $USERUPDATEID="";
        $USERDELETEID="";
        $TIMEINSERT="[:NOW()]";
        $TIMEUPDATE="[:DATE(" . LOWEST_DATE . ")]";
        $TIMEDELETE="[:DATE(" . LOWEST_DATE . ")]";

        // PREDISPONGO COLONNE E VALORI DA REGISTRARE
        $columns="SYSID,NAME,DESCRIPTION,REGISTRY,BOWID,BOWTIME,TARGETID,TARGETTIME,AUXTIME,STATUSTIME,TYPOLOGYID,MOTIVEID,GENREID,AMOUNT,STATUSRISK,REFERENCE,REFARROWID,TAG,CONSISTENCY,AVAILABILITY,SCOPE,UPDATING,DELETING,STATUS,PHASE,PHASENOTE,PROVIDER,PARCEL,DELETED,ROLEID,USERINSERTID,USERUPDATEID,USERDELETEID,TIMEINSERT,TIMEUPDATE,TIMEDELETE";
        $values="'$SYSID','$NAME','$DESCRIPTION',$REGISTRY,'$BOWID',$BOWTIME,'$TARGETID',$TARGETTIME,$AUXTIME,$STATUSTIME,'$TYPOLOGYID','$MOTIVEID','$GENREID',$AMOUNT,$STATUSRISK,'$REFERENCE','$REFARROWID','$TAG',$CONSISTENCY,$AVAILABILITY,$SCOPE,$UPDATING,$DELETING,$STATUS,$PHASE,'$PHASENOTE','$PROVIDER','$PARCEL',$DELETED,'$ROLEID','$USERINSERTID','$USERUPDATEID','$USERDELETEID',$TIMEINSERT,$TIMEUPDATE,$TIMEDELETE";
        $sql="INSERT INTO QVARROWS($columns) VALUES($values)";
        
        if(!maestro_execute($maestro, $sql, false, $clobs)){
            $babelcode="QVERR_EXECUTE";
            $trace=debug_backtrace();
            $b_params=array("FUNCTION" => $trace[0]["function"] );
            $b_pattern=$maestro->errdescr;
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // GESTIONE DEI DATI ESTESI
        qv_extension($maestro, $data, "QVARROW", $SYSID, $TYPOLOGYID, 0);
        
        // GESTIONE DELLO SCARICO "FORTE"
        _qv_discharge($maestro, 0, $SYSID, $TYPOLOGYID, $ROUNDING, "", 0, "", "", "", $MOTIVEID, $MOTIVE_DISCHARGE, $AMOUNT, $BOWID, $PURETIME, $GENREID);
        
        // TRIGGER PERSONALIZZATO
        qv_triggerarrow($maestro, $data, $SYSID, $TYPOLOGYID, 0);
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