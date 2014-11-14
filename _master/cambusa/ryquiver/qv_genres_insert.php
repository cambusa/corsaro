<?php 
/****************************************************************************
* Name:            qv_genres_insert.php                                     *
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
function qv_genres_insert($maestro, $data){
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
        qv_solverecord($maestro, $data, "QVGENRETYPES", "TYPOLOGYID", "TYPOLOGYNAME", $TYPOLOGYID);
        if($TYPOLOGYID==""){
            $babelcode="QVERR_GENRETYPES";
            $b_params=array();
            $b_pattern="Tipo di genere non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // VALIDAZIONE PERSONALIZZATA
        qv_validategenre($maestro, $data, $SYSID, $TYPOLOGYID, 0);

        // DETERMINO NAME
        if(isset($data["NAME"])){
            $NAME=ryqEscapize($data["NAME"], 50);
            qv_checkname($maestro, "QVGENRES", $SYSID, $NAME);
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
            
        // DETERMINO BREVITY
        if(isset($data["BREVITY"]))
            $BREVITY=ryqEscapize($data["BREVITY"]);
        else
            $BREVITY="";

        // DETERMINO REGISTRY
        $clobs=false;
        if(isset($data["REGISTRY"]))
            qv_setclob($maestro, "REGISTRY", $data["REGISTRY"], $REGISTRY, $clobs);
        else
            $REGISTRY="''";

        // DETERMINO ROUNDING
        if(isset($data["ROUNDING"]))
            $ROUNDING=intval($data["ROUNDING"]);
        else
            $ROUNDING=0;

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
        $columns="SYSID,NAME,DESCRIPTION,BREVITY,REGISTRY,ROUNDING,TYPOLOGYID,TAG,DELETED,ROLEID,USERINSERTID,USERUPDATEID,USERDELETEID,TIMEINSERT,TIMEUPDATE,TIMEDELETE";
        $values="'$SYSID','$NAME','$DESCRIPTION','$BREVITY',$REGISTRY,$ROUNDING,'$TYPOLOGYID','$TAG',$DELETED,'$ROLEID','$USERINSERTID','$USERUPDATEID','$USERDELETEID',$TIMEINSERT,$TIMEUPDATE,$TIMEDELETE";
        $sql="INSERT INTO QVGENRES($columns) VALUES($values)";
        
        if(!maestro_execute($maestro, $sql, false, $clobs)){
            $babelcode="QVERR_EXECUTE";
            $trace=debug_backtrace();
            $b_params=array("FUNCTION" => $trace[0]["function"] );
            $b_pattern=$maestro->errdescr;
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        // GESTIONE DEI DATI ESTESI
        qv_extension($maestro, $data, "QVGENRE", $SYSID, $TYPOLOGYID, 0);
        
        // TRIGGER PERSONALIZZATO
        qv_triggergenre($maestro, $data, $SYSID, $TYPOLOGYID, 0);
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