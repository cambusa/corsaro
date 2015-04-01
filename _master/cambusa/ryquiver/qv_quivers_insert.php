<?php 
/****************************************************************************
* Name:            qv_quivers_insert.php                                    *
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
include_once "quiverext.php";
include_once "quivertrg.php";
function qv_quivers_insert($maestro, $data){
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
        $fields=qv_solverecord($maestro, $data, "QVQUIVERTYPES", "TYPOLOGYID", "TYPOLOGYNAME", $TYPOLOGYID, "GENRETYPEID,OBJECTTYPEID,MOTIVETYPEID,ARROWTYPEID,QUIVERTYPEID");
        if($TYPOLOGYID==""){
            $babelcode="QVERR_QUIVERTYPES";
            $b_params=array();
            $b_pattern="Tipo di quiver non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $TPGENRETYPEID=$fields["GENRETYPEID"];
        $TPOBJECTTYPEID=$fields["OBJECTTYPEID"];
        $TPMOTIVETYPEID=$fields["MOTIVETYPEID"];
        $TPARROWTYPEID=$fields["ARROWTYPEID"];
        $TPQUIVERTYPEID=$fields["QUIVERTYPEID"];
        
        // VALIDAZIONE PERSONALIZZATA
        qv_validatequiver($maestro, $data, $SYSID, $TYPOLOGYID, 0);

        // DETERMINO NAME
        if(isset($data["NAME"])){
            $NAME=ryqEscapize($data["NAME"], 50);
            qv_checkname($maestro, "QVQUIVERS", $SYSID, $NAME);
        }
        else{
            $NAME="__$SYSID";
        }
        
        // DETERMINO DESCRIPTION
        if(isset($data["DESCRIPTION"])){
            $DESCRIPTION=ryqEscapize(qv_inputUTF8($data["DESCRIPTION"]), 200);
            $DESCRIPTION=str_replace("[!SYSID]", $SYSID, $DESCRIPTION);
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

        // DETERMINO AUXTIME
        if(isset($data["AUXTIME"]))
            $AUXTIME=qv_escapizetime($data["AUXTIME"], LOWEST_TIME);
        else
            $AUXTIME=date("YmdHis");
            
        // COMPLETO PER SQL
        $AUXTIME="[:TIME($AUXTIME)]";

        // DETERMINO AUXAMOUNT
        if(isset($data["AUXAMOUNT"]))
            $AUXAMOUNT=floatval($data["AUXAMOUNT"]);
        else
            $AUXAMOUNT=0;

        // DETERMINO STATUS
        if(isset($data["STATUS"])){
            $STATUS=intval($data["STATUS"]);
            if($STATUS<0 || $STATUS>3 )
                $STATUS=0;
        }
        else{
            $STATUS=0;
        }
        
        // DETERMINO STATUSTIME
        if(isset($data["STATUSTIME"]))
            $STATUSTIME=qv_escapizetime($data["STATUSTIME"], LOWEST_TIME);
        else
            $STATUSTIME=date("YmdHis");

        // COMPLETO PER SQL
        $STATUSTIME="[:TIME($STATUSTIME)]";

        // DETERMINO REFGENREID
        $fields=qv_solverecord($maestro, $data, "QVGENRES", "REFGENREID", "REFGENRENAME", $REFGENREID, "TYPOLOGYID");
        if($REFGENREID!=""){
            $GENRETYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE IL GENERE SIA COMPATIBILE COL TIPO GENERE DEL QUIVER
            if($GENRETYPEID!=$TPGENRETYPEID){
                $babelcode="QVERR_GENRECONFLICT";
                $b_params=array();
                $b_pattern="Il genere non è compatibile col tipo genere quiver";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        
        // DETERMINO REFOBJECTID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTS", "REFOBJECTID", "REFOBJECTNAME", $REFOBJECTID, "TYPOLOGYID");
        if($REFOBJECTID!=""){
            $OBJECTTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE L'OGGETTO SIA COMPATIBILE COL TIPO OGGETTO DEL QUIVER
            if($OBJECTTYPEID!=$TPOBJECTTYPEID){
                $babelcode="QVERR_OBJECTCONFLICT";
                $b_params=array();
                $b_pattern="L'oggetto non è compatibile col tipo oggetto quiver";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        
        // DETERMINO REFMOTIVEID
        $fields=qv_solverecord($maestro, $data, "QVMOTIVES", "REFMOTIVEID", "REFMOTIVENAME", $REFMOTIVEID, "TYPOLOGYID");
        if($REFMOTIVEID!=""){
            $MOTIVETYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE IL MOTIVE SIA COMPATIBILE COL TIPO MOTIVE DEL QUIVER
            if($MOTIVETYPEID!=$TPMOTIVETYPEID){
                $babelcode="QVERR_MOTIVECONFLICT";
                $b_params=array();
                $b_pattern="Il motivo non è compatibile col tipo motivo quiver";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        
        // DETERMINO REFARROWID
        $fields=qv_solverecord($maestro, $data, "QVARROWS", "REFARROWID", "REFARROWNAME", $REFARROWID, "TYPOLOGYID");
        if($REFARROWID!=""){
            $ARROWTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE LA FRECCIA SIA COMPATIBILE COL TIPO FRECCIA DEL QUIVER
            if($ARROWTYPEID!=$TPARROWTYPEID){
                $babelcode="QVERR_ARROWCONFLICT";
                $b_params=array();
                $b_pattern="La freccia non è compatibile col tipo freccia quiver";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
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
        }
        
        // DETERMINO REFERENCE
        if(isset($data["REFERENCE"]))
            $REFERENCE=ryqEscapize($data["REFERENCE"], 50);
        else
            $REFERENCE="";
        
        // DETERMINO TAG
        if(isset($data["TAG"]))
            $TAG=ryqEscapize($data["TAG"], 200);
        else
            $TAG="";
        
        // DETERMINO CONSISTENCY
        if(isset($data["CONSISTENCY"])){
            $CONSISTENCY=intval($data["CONSISTENCY"]);
            if($CONSISTENCY<0 || $CONSISTENCY>3 )
                $CONSISTENCY=0;
        }
        else{
            $CONSISTENCY=0;
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
                $SCOPE=0;
        }
        else{
            $SCOPE=0;
        }
        
        // DETERMINO UPDATING
        if(isset($data["UPDATING"])){
            $UPDATING=intval($data["UPDATING"]);
            if($UPDATING<0 || $UPDATING>2 )
                $UPDATING=0;
        }
        else{
            $UPDATING=0;
        }
        
        // DETERMINO DELETING
        if(isset($data["DELETING"])){
            $DELETING=intval($data["DELETING"]);
            if($DELETING<0 || $DELETING>2 )
                $DELETING=0;
        }
        else{
            $DELETING=0;
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
        }
        else{
            $MOREDATA="";
        }
        
        $PHASE=0;
        $PHASENOTE="";
        $DELETED=0;
        $ROLEID=$global_quiverroleid;
        $USERINSERTID=$global_quiveruserid;
        $USERUPDATEID="";
        $USERDELETEID="";
        $TIMEINSERT="[:NOW()]";
        $TIMEUPDATE="[:DATE(" . LOWEST_DATE . ")]";
        $TIMEDELETE="[:DATE(" . LOWEST_DATE . ")]";

        // PREDISPONGO COLONNE E VALORI DA REGISTRARE
        $columns="SYSID,NAME,DESCRIPTION,REGISTRY,AUXTIME,STATUSTIME,AUXAMOUNT,TYPOLOGYID,REFGENREID,REFOBJECTID,REFMOTIVEID,REFARROWID,REFQUIVERID,REFERENCE,TAG,CONSISTENCY,AVAILABILITY,SCOPE,UPDATING,DELETING,STATUS,PHASE,PHASENOTE,MOREDATA,DELETED,ROLEID,USERINSERTID,USERUPDATEID,USERDELETEID,TIMEINSERT,TIMEUPDATE,TIMEDELETE";
        $values="'$SYSID','$NAME','$DESCRIPTION',$REGISTRY,$AUXTIME,$STATUSTIME,$AUXAMOUNT,'$TYPOLOGYID','$REFGENREID','$REFOBJECTID','$REFMOTIVEID','$REFARROWID','$REFQUIVERID','$REFERENCE','$TAG',$CONSISTENCY,$AVAILABILITY,$SCOPE,$UPDATING,$DELETING,$STATUS,$PHASE,'$PHASENOTE','$MOREDATA',$DELETED,'$ROLEID','$USERINSERTID','$USERUPDATEID','$USERDELETEID',$TIMEINSERT,$TIMEUPDATE,$TIMEDELETE";
        $sql="INSERT INTO QVQUIVERS($columns) VALUES($values)";
        
        if(!maestro_execute($maestro, $sql, false, $clobs)){
            $babelcode="QVERR_EXECUTE";
            $trace=debug_backtrace();
            $b_params=array("FUNCTION" => $trace[0]["function"] );
            $b_pattern=$maestro->errdescr;
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        // GESTIONE DEI DATI ESTESI
        qv_extension($maestro, $data, "QVQUIVER", $SYSID, $TYPOLOGYID, 0);

        // TRIGGER PERSONALIZZATO
        qv_triggerquiver($maestro, $data, $SYSID, $TYPOLOGYID, 0);
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