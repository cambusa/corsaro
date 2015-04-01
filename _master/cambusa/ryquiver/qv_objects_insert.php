<?php 
/****************************************************************************
* Name:            qv_objects_insert.php                                    *
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
include_once "quiverobj.php";
include_once "quiverext.php";
include_once "quivertrg.php";
function qv_objects_insert($maestro, $data){
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
        $fields=qv_solverecord($maestro, $data, "QVOBJECTTYPES", "TYPOLOGYID", "TYPOLOGYNAME", $TYPOLOGYID, "GENRETYPEID,QUIVERTYPEID");
        if($TYPOLOGYID==""){
            $babelcode="QVERR_OBJECTTYPES";
            $b_params=array();
            $b_pattern="Tipo di oggetto non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $TPGENRETYPEID=$fields["GENRETYPEID"];
        $TPQUIVERTYPEID=$fields["QUIVERTYPEID"];
        
        // INDIVIDUAZIONE TIMEUNIT
        qv_solvetimeunit($maestro, $TYPOLOGYID, $unit);
        
        // VALIDAZIONE PERSONALIZZATA
        if(qv_validateobject($maestro, $data, $SYSID, $TYPOLOGYID, 0)===2){
            $success=2;
            $message="Alcune operazioni non sono state eseguite";
            // USCITA SENZA OPERAZIONE
            $j=array();
            $j["success"]=$success;
            $j["code"]=$babelcode;
            $j["params"]=$babelparams;
            $j["message"]=$message;
            $j["SYSID"]="";
            return $j; //ritorno standard
        }

        // DETERMINO NAME
        if(isset($data["NAME"])){
            $NAME=ryqEscapize($data["NAME"], 50);
            qv_checkname($maestro, "QVOBJECTS", $SYSID, $NAME);
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

        // DETERMINO REFGENREID
        $fields=qv_solverecord($maestro, $data, "QVGENRES", "REFGENREID", "REFGENRENAME", $REFGENREID, "TYPOLOGYID,ROUNDING");
        if($REFGENREID!=""){
            $GENRETYPEID=$fields["TYPOLOGYID"];
            $ROUNDING=intval($fields["ROUNDING"]);
            // VERIFICO CHE IL GENERE SIA COMPATIBILE COL TIPO OGGETTO
            if($GENRETYPEID!=$TPGENRETYPEID){
                $babelcode="QVERR_GENRECONFLICT";
                $b_params=array();
                $b_pattern="Il genere non è compatibile col tipo oggetto";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        else{
            $ROUNDING=2;
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
        }
        
        // DETERMINO BEGINDATE E ENDTIME
        qv_insertdatalife($maestro, $data, $BEGINTIME, $ENDTIME);
        if($unit=="S"){
            $BEGINTIME="[:TIME($BEGINTIME)]";
            $ENDTIME="[:TIME($ENDTIME)]";
        }
        else{
            $BEGINTIME="[:DATE($BEGINTIME)]";
            $ENDTIME="[:DATE($ENDTIME)]";
        }

        // DETERMINO REFERENCE
        if(isset($data["REFERENCE"]))
            $REFERENCE=ryqEscapize($data["REFERENCE"], 50);
        else
            $REFERENCE="";
        
        // DETERMINO AUXTIME
        if(isset($data["AUXTIME"]))
            $AUXTIME=qv_escapizetime($data["AUXTIME"], LOWEST_TIME);
        else
            $AUXTIME=LOWEST_TIME;
        if($unit=="S")
            $AUXTIME="[:TIME($AUXTIME)]";
        else
            $AUXTIME="[:DATE($AUXTIME)]";
        
        // DETERMINO AUXAMOUNT
        if(isset($data["AUXAMOUNT"]))
            $AUXAMOUNT=round(floatval($data["AUXAMOUNT"]), $ROUNDING );
        else
            $AUXAMOUNT=0;

        // DETERMINO MAXAMOUNT
        if(isset($data["MAXAMOUNT"]))
            $MAXAMOUNT=round(floatval($data["MAXAMOUNT"]), $ROUNDING );
        else
            $MAXAMOUNT=0;

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
        }
        
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
        
        $DELETED=0;
        $ROLEID=$global_quiverroleid;
        $USERINSERTID=$global_quiveruserid;
        $USERUPDATEID="";
        $USERDELETEID="";
        $TIMEINSERT="[:NOW()]";
        $TIMEUPDATE="[:DATE(" . LOWEST_DATE . ")]";
        $TIMEDELETE="[:DATE(" . LOWEST_DATE . ")]";
        
        // PREDISPONGO COLONNE E VALORI DA REGISTRARE
        $columns="SYSID,NAME,DESCRIPTION,REGISTRY,TYPOLOGYID,REFGENREID,REFOBJECTID,REFQUIVERID,BEGINTIME,ENDTIME,AUXTIME,AUXAMOUNT,MAXAMOUNT,BUFFERID,REFERENCE,TAG,CONSISTENCY,SCOPE,UPDATING,DELETING,DELETED,ROLEID,USERINSERTID,USERUPDATEID,USERDELETEID,TIMEINSERT,TIMEUPDATE,TIMEDELETE";
        $values="'$SYSID','$NAME','$DESCRIPTION',$REGISTRY,'$TYPOLOGYID','$REFGENREID','$REFOBJECTID','$REFQUIVERID',$BEGINTIME,$ENDTIME,$AUXTIME,$AUXAMOUNT,$MAXAMOUNT,'$BUFFERID','$REFERENCE','$TAG',$CONSISTENCY,$SCOPE,$UPDATING,$DELETING,$DELETED,'$ROLEID','$USERINSERTID','$USERUPDATEID','$USERDELETEID',$TIMEINSERT,$TIMEUPDATE,$TIMEDELETE";
        $sql="INSERT INTO QVOBJECTS($columns) VALUES($values)";
        
        if(!maestro_execute($maestro, $sql, false, $clobs)){
            $babelcode="QVERR_EXECUTE";
            $trace=debug_backtrace();
            $b_params=array("FUNCTION" => $trace[0]["function"] );
            $b_pattern=$maestro->errdescr;
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        // GESTIONE DEI DATI ESTESI
        qv_extension($maestro, $data, "QVOBJECT", $SYSID, $TYPOLOGYID, 0);

        // TRIGGER PERSONALIZZATO
        qv_triggerobject($maestro, $data, $SYSID, $TYPOLOGYID, 0);
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