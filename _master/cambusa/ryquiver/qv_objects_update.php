<?php 
/****************************************************************************
* Name:            qv_objects_update.php                                    *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverinf.php";
include_once "quiverobj.php";
include_once "quiverval.php";
include_once "quiverext.php";
include_once "quivertrg.php";
include_once "../rymaestro/maestro_querylib.php";
function qv_objects_update($maestro, $data){
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
        $record=qv_solverecord($maestro, $data, "QVOBJECTS", "SYSID", "NAME", $SYSID, "NAME,TYPOLOGYID,REFGENREID");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $NAME=$record["NAME"];
        $TYPOLOGYID=$record["TYPOLOGYID"];
        $REG_REFGENREID=$record["REFGENREID"];
        qv_solvetimeunit($maestro, $TYPOLOGYID, $unit);
        
        // INDIVIDUAZIONE ARCO DI VITA
        // Mi serve per avere le date registrate in precedenza
        qv_solvelife($maestro, "QVOBJECTS", $SYSID, $BEGINTIME, $ENDTIME);
        
        // VALIDAZIONE PERSONALIZZATA
        qv_validateobject($maestro, $data, $SYSID, $TYPOLOGYID, 1);
        
        // SE NAME E' IN MODIFICA LO VALIDO
        if(isset($data["SYSID"]) && isset($data["NAME"])){
            $NAME=$data["NAME"];
            qv_checkname($maestro, "QVOBJECTS", $SYSID, $NAME);
            qv_appendcomma($sets, "NAME='$NAME'");
        }
        
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

        // DETERMINO REFGENREID
        $fields=qv_solverecord($maestro, $data, "QVGENRES", "REFGENREID", "REFGENRENAME", $REFGENREID, "TYPOLOGYID,ROUNDING");
        if($REFGENREID!=""){
            $GENRETYPEID=$fields["TYPOLOGYID"];
            $ROUNDING=intval($fields["ROUNDING"]);
            // VERIFICO CHE IL GENERE SIA COMPATIBILE COL TIPO OGGETTO
            $fields=qv_getrecord($maestro, "QVOBJECTTYPES", $TYPOLOGYID, "GENRETYPEID");
            if($GENRETYPEID!=$fields["GENRETYPEID"]){
                $babelcode="QVERR_GENRECONFLICT";
                $b_params=array();
                $b_pattern="Il genere non è compatibile col tipo oggetto";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"REFGENREID='$REFGENREID'");
        }
        else{
            if($fields){
                $ROUNDING=2;
                qv_appendcomma($sets,"REFGENREID='$REFGENREID'");
            }
            else{
                // IL GENERE NON E' PASSATO: SEGNALO DI REPERIRE L'ARROTONDAMENTO
                $ROUNDING=-1;
            }
        }
        
        // DETERMINO REFOBJECTID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTS", "REFOBJECTID", "REFOBJECTNAME", $REFOBJECTID, "TYPOLOGYID");
        if($REFOBJECTID!=""){
            $OBJECTTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE L'OGGETTO SIA COMPATIBILE COL TIPO OGGETTO
            if($OBJECTTYPEID!=$TYPOLOGYID){
                $babelcode="QVERR_OBJECTCONFLICT";
                $b_params=array();
                $b_pattern="L'oggetto padre non è compatibile col tipo oggetto";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            // VERIFICO L'ASSENZA DI CICLICITA'
            qv_cyclicity($maestro, "QVOBJECTS", "REFOBJECTID", $SYSID, $REFOBJECTID);
            qv_appendcomma($sets,"REFOBJECTID='$REFOBJECTID'");
        }
        else{
            if($fields){
                qv_appendcomma($sets,"REFOBJECTID='$REFOBJECTID'");
            }
        }

        // DETERMINO REFQUIVERID
        $fields=qv_solverecord($maestro, $data, "QVQUIVERS", "REFQUIVERID", "REFQUIVERNAME", $REFQUIVERID, "TYPOLOGYID");
        if($REFQUIVERID!=""){
            $QUIVERTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE IL QUIVER SIA COMPATIBILE COL TIPO OGGETTO
            if($QUIVERTYPEID!=$TPQUIVERTYPEID){
                $babelcode="QVERR_QUIVERCONFLICT";
                $b_params=array();
                $b_pattern="Il quiver non è compatibile col tipo oggetto";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"REFQUIVERID='$REFQUIVERID'");
        }
        else{
            if($fields){
                qv_appendcomma($sets,"REFQUIVERID='$REFQUIVERID'");
            }
        }

        // DETERMINO BEGINTIME E ENDTIME
        qv_updatedatalife($maestro, $data, $sets, $BEGINTIME, $ENDTIME, $changed);
        $bo=$BEGINTIME; // Memorizzo le date normalizzate
        $eo=$ENDTIME;
        if($unit=="S"){
            $BEGINTIME="[:TIME($BEGINTIME)]";
            $ENDTIME="[:TIME($ENDTIME)]";
        }
        else{
            $BEGINTIME="[:DATE($BEGINTIME)]";
            $ENDTIME="[:DATE($ENDTIME)]";
        }
        qv_appendcomma($sets,"BEGINTIME=$BEGINTIME");
        qv_appendcomma($sets,"ENDTIME=$ENDTIME");
        
        if($changed){
            // CONTROLLO CHE IL CICLO DI VITA SIA COMPATIBILE 
            // CON QUELLO DELLE INCLUSIONI E DELLE FRECCE INTERESSATE
            $res=maestro_unbuffered($maestro, "SELECT BEGINTIME,ENDTIME FROM QVINCLUSIONS WHERE OBJECTID='$SYSID' OR PARENTID='$SYSID'");
            while( $row=maestro_fetch($maestro, $res) ){
                $bi=qv_strtime( $row["BEGINTIME"] );
                $ei=qv_strtime( $row["ENDTIME"] );
                if( ( $bi>LOWEST_TIME && $bi<$bo ) || ( $ei<HIGHEST_TIME && $ei>$eo ) ){
                    $babelcode="QVERR_LIFECONFLICT";
                    $b_params=array();
                    $b_pattern="Ciclo di vita inconsistente con le inclusioni";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
            maestro_free($maestro, $res);
            
            $res=maestro_unbuffered($maestro, "SELECT BOWTIME FROM QVARROWS WHERE BOWID='$SYSID'");
            while( $row=maestro_fetch($maestro, $res) ){
                $f=qv_strtime( $row["BOWTIME"] );
                if( $f>LOWEST_TIME && ( $f<$bo || $f>$eo ) ){
                    $babelcode="QVERR_LIFECONFLICT";
                    $b_params=array();
                    $b_pattern="Ciclo di vita inconsistente con le inclusioni";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
            maestro_free($maestro, $res);

            $res=maestro_unbuffered($maestro, "SELECT TARGETTIME FROM QVARROWS WHERE TARGETID='$SYSID'");
            while( $row=maestro_fetch($maestro, $res) ){
                $f=qv_strtime( $row["TARGETTIME"] );
                if( $f>LOWEST_TIME && ( $f<$bo || $f>$eo ) ){
                    $babelcode="QVERR_LIFECONFLICT";
                    $b_params=array();
                    $b_pattern="Ciclo di vita inconsistente con le inclusioni";
                    throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                }
            }
            maestro_free($maestro, $res);
        }

        // DETERMINO REFERENCE
        if(isset($data["REFERENCE"])){
            $REFERENCE=ryqEscapize($data["REFERENCE"], 50);
            qv_appendcomma($sets,"REFERENCE='$REFERENCE'");
        }
        
        // DETERMINO AUXTIME
        if(isset($data["AUXTIME"])){
            $AUXTIME=qv_escapizetime($data["AUXTIME"], LOWEST_TIME);
            if($unit=="S")
                $AUXTIME="[:TIME($AUXTIME)]";
            else
                $AUXTIME="[:DATE($AUXTIME)]";
            qv_appendcomma($sets,"AUXTIME=$AUXTIME");
        }
        
        // DETERMINO AUXAMOUNT
        if(isset($data["AUXAMOUNT"])){
            $AUXAMOUNT=floatval($data["AUXAMOUNT"]);
            if($AUXAMOUNT!=0){
                if($ROUNDING==-1){
                    qv_solverounding($maestro, $REG_REFGENREID, $ROUNDING);
                }
                $AUXAMOUNT=round($AUXAMOUNT, $ROUNDING);
            }
            qv_appendcomma($sets,"AUXAMOUNT=$AUXAMOUNT");
        }

        // DETERMINO MAXAMOUNT
        if(isset($data["MAXAMOUNT"])){
            $MAXAMOUNT=floatval($data["MAXAMOUNT"]);
            if($MAXAMOUNT!=0){
                if($ROUNDING==-1){
                    qv_solverounding($maestro, $REG_REFGENREID, $ROUNDING);
                }
                $MAXAMOUNT=round($MAXAMOUNT, $ROUNDING);
            }
            qv_appendcomma($sets,"MAXAMOUNT=$MAXAMOUNT");
        }

        // DETERMINO BUFFERID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTS", "BUFFERID", "BUFFERNAME", $BUFFERID, "TYPOLOGYID");
        if($BUFFERID!=""){
            $OBJECTTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE L'OGGETTO SIA COMPATIBILE COL TIPO OGGETTO
            if($OBJECTTYPEID!=$TYPOLOGYID){
                $babelcode="QVERR_OBJECTCONFLICT";
                $b_params=array();
                $b_pattern="L'oggetto tampone non è compatibile col tipo oggetto";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
            qv_appendcomma($sets,"BUFFERID='$BUFFERID'");
        }
        else{
            if($fields){
                qv_appendcomma($sets,"BUFFERID='$BUFFERID'");
            }
        }

        // DETERMINO TAG
        if(isset($data["TAG"])){
            $TAG=ryqEscapize($data["TAG"], 200);
            qv_appendcomma($sets,"TAG='$TAG'");
        }
        
        // DETERMINO CONSISTENCY
        if(isset($data["CONSISTENCY"])){
            $CONSISTENCY=intval($data["CONSISTENCY"]);
            if($CONSISTENCY>=0 && $CONSISTENCY<=3 ){
                qv_appendcomma($sets,"CONSISTENCY=$CONSISTENCY");
            }
        }
        
        // DETERMINO SCOPE
        if(isset($data["SCOPE"])){
            $SCOPE=intval($data["SCOPE"]);
            if($SCOPE>=0 && $SCOPE<=2 ){
                qv_appendcomma($sets,"SCOPE=$SCOPE");
            }
        }
        
        // DETERMINO UPDATING
        if(isset($data["UPDATING"])){
            $UPDATING=intval($data["UPDATING"]);
            if($UPDATING>=0 && $UPDATING<=2 ){
                qv_appendcomma($sets,"UPDATING=$UPDATING");
            }
        }
        
        // DETERMINO DELETING
        if(isset($data["DELETING"])){
            $DELETING=intval($data["DELETING"]);
            if($DELETING>=0 && $DELETING<=2 ){
                qv_appendcomma($sets,"DELETING=$DELETING");
            }
        }
        
        $USERUPDATEID=$global_quiveruserid;
        qv_appendcomma($sets,"USERUPDATEID='$USERUPDATEID'");
        
        $TIMEUPDATE="[:NOW()]";
        qv_appendcomma($sets,"TIMEUPDATE=$TIMEUPDATE");
        
        if($sets!=""){
            // GESTIONE DELLA STORICIZZAZIONE
            _qv_historicizing($maestro, "QVOBJECT", $SYSID, $TYPOLOGYID, 1);

            // PREDISPONGO COLONNE E VALORI DA REGISTRARE
            $sql="UPDATE QVOBJECTS SET $sets WHERE SYSID='$SYSID'";
        
            if(!maestro_execute($maestro, $sql, false, $clobs)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"] );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        // GESTIONE DEI DATI ESTESI
        qv_extension($maestro, $data, "QVOBJECT", $SYSID, $TYPOLOGYID, 1);

        // TRIGGER PERSONALIZZATO
        qv_triggerobject($maestro, $data, $SYSID, $TYPOLOGYID, 1);
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