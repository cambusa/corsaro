<?php 
/****************************************************************************
* Name:            qv_legend_complete.php                                   *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_legend_complete($maestro, $data){
    global $babelcode, $babelparams;
    global $path_cambusa;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // LEGGO IL LEGEND
        $legend=qv_solverecord($maestro, $data, "QW_LEGEND", "LEGENDID", "", $LEGENDID, "TOLERANCE");
        if($LEGENDID==""){
            $babelcode="QVERR_LEGENDID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare la configurazione";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $TOLERANCE=$legend["TOLERANCE"];
        
        if(isset($data["INPUT"]))
            $INPUT=$data["INPUT"];
        else
            $INPUT="";

        $selected="";

        switch($INPUT){
        case "RYQUE":
            $REQUESTID=$data["REQUESTID"];
            $CURRENT=intval($data["CURRENT"]);
            $SELECTION=$data["SELECTION"];
            $INVERT=intval($data["INVERT"]);
            
            $reqpath=$path_cambusa."ryque/requests/".$REQUESTID;
            $index=file_get_contents($reqpath.".ndx");
            $lenkey=$maestro->lenid;
            
            if($SELECTION=="" && $INVERT==0){
                if($CURRENT>0){
                    $selected=substr($index, ($CURRENT-1)*($lenkey+1), $lenkey);
                }
            }
            elseif($INVERT==0){
                $v=explode("|", $SELECTION);
                $s=array();
                foreach($v as $i){
                    $s[]=substr($index, (intval($i)-1)*($lenkey+1), $lenkey);
                }
                $selected=implode("|", $s);
            }
            else{
                $v=explode("|", $SELECTION);
                $s=array();
                for($i=1; $i<=round(strlen($index)/($lenkey+1)); $i++){
                    if(!in_array($i, $v)){
                        $s[]=substr($index, ($i-1)*($lenkey+1), $lenkey);
                    }
                }
                $selected=implode("|", $s);
            }
            break;
        case "LIST":
            $selected=$data["SELECTION"];
            break;
        default:
            $babelcode="QVERR_INPUT";
            $b_params=array();
            $b_pattern="Tipo di ingresso non riconosciuto";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        if($selected!=""){
            $selected="'".str_replace("|", "','", $selected)."'";
            $DATACERT="[:DATE(".date("Ymd").")]";
            $TYPOLOGYID=qv_actualid($maestro, "0MOVIMENTI00");

            // DETERMINO LE PRATICHE DI ABBINAMENTO
            $sql="SELECT SYSID FROM QVQUIVERS WHERE SYSID IN ($selected) AND ABS(AUXAMOUNT)<=$TOLERANCE";
            maestro_query($maestro, $sql, $prat);
            for($i=0; $i<count($prat); $i++){
                $PRATICAID=$prat[$i]["SYSID"];

                // VERIFICO TUTTI I MOVIMENTI DELLA PRATICA
                $sql="UPDATE QVARROWS SET STATUS=2, STATUSTIME=$DATACERT WHERE TYPOLOGYID='$TYPOLOGYID' AND STATUS<2 AND SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$PRATICAID')";
                maestro_execute($maestro, $sql);

                // CHIUDO LA PRATICA
                $sql="UPDATE QVQUIVERS SET STATUS=1, STATUSTIME=$DATACERT WHERE SYSID='$PRATICAID'";
                maestro_execute($maestro, $sql);
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