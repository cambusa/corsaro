<?php 
/****************************************************************************
* Name:            qv_motives_insert.php                                    *
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
function qv_motives_insert($maestro, $data){
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
        $fields=qv_solverecord($maestro, $data, "QVMOTIVETYPES", "TYPOLOGYID", "TYPOLOGYNAME", $TYPOLOGYID, "OBJECTTYPEID");
        if($TYPOLOGYID==""){
            $babelcode="QVERR_MOTIVETYPES";
            $b_params=array();
            $b_pattern="Tipo di motivo non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $TPOBJECTTYPEID=$fields["OBJECTTYPEID"];
        
        // VALIDAZIONE PERSONALIZZATA
        qv_validatemotive($maestro, $data, $SYSID, $TYPOLOGYID, 0);

        // DETERMINO NAME
        if(isset($data["NAME"])){
            $NAME=ryqEscapize($data["NAME"], 50);
            qv_checkname($maestro, "QVMOTIVES", $SYSID, $NAME);
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

        // DETERMINO DIRECTION
        if(isset($data["DIRECTION"])){
            $DIRECTION=intval($data["DIRECTION"]);
            if($DIRECTION<0)
                $DIRECTION=0;
            elseif($DIRECTION>1 )
                $DIRECTION=1;
        }
        else{
            $DIRECTION=0;
        }
            
        // DETERMINO REFERENCEID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTS", "REFERENCEID", "REFERENCENAME", $REFERENCEID, "TYPOLOGYID");
        if($REFERENCEID!=""){
            $OBJECTTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE L'OGGETTO SIA COMPATIBILE COL TIPO OGGETTO DI MOTIVE
            if($OBJECTTYPEID!=$TPOBJECTTYPEID){
                $babelcode="QVERR_OBJECTCONFLICT";
                $b_params=array();
                $b_pattern="L'oggetto non è compatibile col tipo oggetto del motivo";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        
        // DETERMINO COUNTERPARTID
        $fields=qv_solverecord($maestro, $data, "QVOBJECTS", "COUNTERPARTID", "COUNTERPARTNAME", $COUNTERPARTID, "TYPOLOGYID");
        if($COUNTERPARTID!=""){
            $OBJECTTYPEID=$fields["TYPOLOGYID"];
            // VERIFICO CHE L'OGGETTO SIA COMPATIBILE COL TIPO OGGETTO DI MOTIVE
            if($OBJECTTYPEID!=$TPOBJECTTYPEID){
                $babelcode="QVERR_OBJECTCONFLICT";
                $b_params=array();
                $b_pattern="L'oggetto non è compatibile col tipo oggetto del motivo";
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
        
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
                $DELETING=2;
        }
        else{
            $DELETING=2;
        }
        
        // DETERMINO STATUS
        if(isset($data["STATUS"])){
            $STATUS=intval($data["STATUS"]);
            if($STATUS<0 || $STATUS>3 ){
                $STATUS=0;
            }
        }
        else{
            $STATUS=0;
        }
        
        // DETERMINO DISCHARGE
        if(isset($data["DISCHARGE"])){
            $DISCHARGE=intval($data["DISCHARGE"]);
            if($DISCHARGE<0 || $DISCHARGE>3 ){
                $DISCHARGE=0;
            }
        }
        else{
            $DISCHARGE=0;
        }

        // DETERMINO TAG
        if(isset($data["TAG"]))
            $TAG=ryqEscapize($data["TAG"], 200);
        else
            $TAG="";

        $DELETED=0;
        $ROLEID=$global_quiverroleid;
        $USERINSERTID=$global_quiveruserid;
        $USERUPDATEID="";
        $USERDELETEID="";
        $TIMEINSERT="[:NOW()]";
        $TIMEUPDATE="[:DATE(" . LOWEST_DATE . ")]";
        $TIMEDELETE="[:DATE(" . LOWEST_DATE . ")]";
        
      // PREDISPONGO COLONNE E VALORI DA REGISTRARE
        $columns="SYSID,NAME,DESCRIPTION,REGISTRY,TYPOLOGYID,DIRECTION,REFERENCEID,COUNTERPARTID,CONSISTENCY,SCOPE,UPDATING,DELETING,STATUS,DISCHARGE,TAG,DELETED,ROLEID,USERINSERTID,USERUPDATEID,USERDELETEID,TIMEINSERT,TIMEUPDATE,TIMEDELETE";
        $values="'$SYSID','$NAME','$DESCRIPTION',$REGISTRY,'$TYPOLOGYID',$DIRECTION,'$REFERENCEID','$COUNTERPARTID',$CONSISTENCY,$SCOPE,$UPDATING,$DELETING,$STATUS,$DISCHARGE,'$TAG',$DELETED,'$ROLEID','$USERINSERTID','$USERUPDATEID','$USERDELETEID',$TIMEINSERT,$TIMEUPDATE,$TIMEDELETE";
        $sql="INSERT INTO QVMOTIVES($columns) VALUES($values)";
        
        if(!maestro_execute($maestro, $sql, false, $clobs)){
            $babelcode="QVERR_EXECUTE";
            $trace=debug_backtrace();
            $b_params=array("FUNCTION" => $trace[0]["function"] );
            $b_pattern=$maestro->errdescr;
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        // GESTIONE DEI DATI ESTESI
        qv_extension($maestro, $data, "QVMOTIVE", $SYSID, $TYPOLOGYID, 0);

        // TRIGGER PERSONALIZZATO
        qv_triggermotive($maestro, $data, $SYSID, $TYPOLOGYID, 0);
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