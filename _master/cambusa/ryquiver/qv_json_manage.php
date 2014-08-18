<?php 
/****************************************************************************
* Name:            qv_json_manage.php                                       *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.00                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_json_manage($maestro, $data){
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // DETERMINO action ( {write|delete} )
        if(isset($data["action"]))
            $action=strtolower(ryqEscapize($data["action"]));
        else
            $action="";

        // DETERMINO USERID
        if(isset($data["USERID"]))
            $USERID=ryqEscapize($data["USERID"]);
        else
            $USERID="";

        // DETERMINO ROLEID
        if(isset($data["ROLEID"]))
            $ROLEID=ryqEscapize($data["ROLEID"]);
        else
            $ROLEID="";
        
        // DETERMINO FUNCTNAME
        if(isset($data["FUNCTNAME"]))
            $FUNCTNAME=strtoupper(ryqEscapize($data["FUNCTNAME"]));
        else
            $FUNCTNAME="";
        
        // DETERMINO DOCNAME
        if(isset($data["DOCNAME"]))
            $DOCNAME=strtoupper(ryqEscapize($data["DOCNAME"]));
        else
            $DOCNAME="";
        
        // DETERMINO REGISTRY
        $clobs=false;
        if($action=="write"){
            if(isset($data["REGISTRY"]))
                $REGISTRY=json_encode( $data["REGISTRY"] );
            else
                $REGISTRY=json_encode( array() );
            qv_setclob($maestro, "REGISTRY", $REGISTRY, $REGISTRY, $clobs);
        }

        if($USERID=="" || $ROLEID=="" || $FUNCTNAME==""){
            $babelcode="QVERR_JSONKEY";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // INDIVIDUAZIONE RECORD
        maestro_query($maestro, "SELECT SYSID FROM QVJSON WHERE USERID='$USERID' AND ROLEID='$ROLEID' AND FUNCTNAME='$FUNCTNAME' AND DOCNAME='$DOCNAME'", $r);
        if(count($r)==1){
            $SYSID=$r[0]["SYSID"];
            if($action=="write")
                $action="update";
        }
        else{
            if($action=="write"){
                $SYSID=qv_createsysid($maestro);
                $action="insert";
            }
            else{
                $action="";
            }
        }
        
        // COSTRUZIONE DELLA QUERY
        switch($action){
        case "insert":
            $columns="SYSID,USERID,ROLEID,FUNCTNAME,DOCNAME,REGISTRY";
            $values="'$SYSID','$USERID','$ROLEID','$FUNCTNAME','$DOCNAME',$REGISTRY";
            $sql="INSERT INTO QVJSON($columns) VALUES($values)";
            break;
        case "update":
            $sets="";
            qv_appendcomma($sets,"USERID='$USERID'");
            qv_appendcomma($sets,"ROLEID='$ROLEID'");
            qv_appendcomma($sets,"FUNCTNAME='$FUNCTNAME'");
            qv_appendcomma($sets,"DOCNAME='$DOCNAME'");
            qv_appendcomma($sets,"REGISTRY=$REGISTRY");
            $sql="UPDATE QVJSON SET $sets WHERE SYSID='$SYSID'";
            break;
        case "delete":
            $sql="DELETE FROM QVJSON WHERE SYSID='$SYSID'";
            break;
        default:
            $sql="";
        }

        // ESECUZIONE DELLA QUERY
        if($sql!=""){
            if(!maestro_execute($maestro, $sql, false, $clobs)){
                $babelcode="QVERR_EXECUTE";
                $trace=debug_backtrace();
                $b_params=array("FUNCTION" => $trace[0]["function"] );
                $b_pattern=$maestro->errdescr;
                throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
            }
        }
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