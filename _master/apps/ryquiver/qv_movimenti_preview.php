<?php 
/****************************************************************************
* Name:            qv_movimenti_preview.php                                 *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_movimenti_preview($maestro, $data){
    global $global_quiveruserid, $global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $PREVIEW="";
        
        // DETERMINO IL MOVIMENTO
        if(isset($data["ARROWID"]))
            $ARROWID=$data["ARROWID"];
        else
            $ARROWID="";
        
        // DETERMINO IL CONTO DI RIFERIMENTO
        if(isset($data["CONTOID"]))
            $CONTOID=$data["CONTOID"];
        else
            $CONTOID="";
            
        // LEGGO LA FRECCIA
        maestro_query($maestro, "SELECT TYPOLOGYID,DESCRIPTION,REGISTRY,GENREID,MOTIVEID,BOWID,TARGETID,BOWTIME,TARGETTIME,AUXTIME,AMOUNT FROM QVARROWS WHERE SYSID='$ARROWID'", $r);
        if(count($r)==1){
            $TYPOLOGYID=$r[0]["TYPOLOGYID"];
            $DESCRIPTION=substr($r[0]["DESCRIPTION"], 0, 40);
            $REGISTRY=$r[0]["REGISTRY"];
            $GENREID=$r[0]["GENREID"];
            $MOTIVEID=$r[0]["MOTIVEID"];
            $BOWID=$r[0]["BOWID"];
            $TARGETID=$r[0]["TARGETID"];

            // DATE
            $trdate=array("-", ":", "T", " ", "'", ".");
            $BOWTIME=substr(str_replace($trdate, "", $r[0]["BOWTIME"]), 0, 8);
            $TARGETTIME=substr(str_replace($trdate, "", $r[0]["TARGETTIME"]), 0, 8);
            $AUXTIME=substr(str_replace($trdate, "", $r[0]["AUXTIME"]), 0, 8);

            // IMPORTO E DATE
            $AMOUNT=round(floatval($r[0]["AMOUNT"]), 2);
            if($BOWID==$CONTOID){
                $AMOUNT=-$AMOUNT;
                $CONTROID=$TARGETID;
                $DATAVAL=$BOWTIME;
            }
            else{
                $CONTROID=$BOWID;
                $DATAVAL=$TARGETTIME;
            }
            $AMOUNT=formatta_numero($AMOUNT, 2);
            $AUXTIME=substr($AUXTIME,6,2)."/".substr($AUXTIME,4,2)."/".substr($AUXTIME,0,4);
            $DATAVAL=substr($DATAVAL,6,2)."/".substr($DATAVAL,4,2)."/".substr($DATAVAL,0,4);
            
            // DESCRIZIONE DIVISA
            $DIVISA="(indefinita)";
            maestro_query($maestro, "SELECT DESCRIPTION FROM QVGENRES WHERE SYSID='$GENREID'", $r);
            if(count($r)==1){
                $DIVISA=substr($r[0]["DESCRIPTION"], 0, 40);
            }
            // DESCRIZIONE CONTO DI RIFERIMENTO
            $CONTO="(indefinito)";
            maestro_query($maestro, "SELECT DESCRIPTION FROM QVOBJECTS WHERE SYSID='$CONTOID'", $r);
            if(count($r)==1){
                $CONTO=substr($r[0]["DESCRIPTION"], 0, 40);
            }
            // DESCRIZIONE CONTROPARTE
            if($CONTROID!=""){
                $CONTRO="(indefinito)";
                maestro_query($maestro, "SELECT DESCRIPTION FROM QVOBJECTS WHERE SYSID='$CONTROID'", $r);
                if(count($r)==1){
                    $CONTRO=substr($r[0]["DESCRIPTION"], 0, 40);
                }
            }
            else{
                $CONTRO="";
            }
            // DESCRIZIONE CAUSALE
            $CAUSALE="(indefinita)";
            maestro_query($maestro, "SELECT DESCRIPTION FROM QVMOTIVES WHERE SYSID='$MOTIVEID'", $r);
            if(count($r)==1){
                $CAUSALE=substr($r[0]["DESCRIPTION"], 0, 40);
            }
            // APRO LA TABELLA
            $PREVIEW.="<table style='width:100%;'>";
            // MOVIMENTO E DESCRIZIONE
            $PREVIEW.="<tr>";
            $PREVIEW.="<td style='padding-right:10px;'><b>Movimento:</b></td><td style='padding-right:20px;'>$ARROWID</td><td style='padding-right:10px;'><b>Descrizione:</b></td><td style='padding-right:10px;'>$DESCRIPTION</td>";
            $PREVIEW.="</tr>";
            // CONTI
            $PREVIEW.="<tr>";
            $PREVIEW.="<td style='padding-right:10px;'><b>Conto:</b></td><td style='padding-right:20px;'>$CONTO</td><td style='padding-right:10px;'><b>Controparte:</b></td><td style='padding-right:20px;'>$CONTRO</td>";
            $PREVIEW.="</tr>";
            // DATE
            $PREVIEW.="<tr>";
            $PREVIEW.="<td style='padding-right:10px;'><b>Registrazione:</b></td><td style='padding-right:20px;'>$AUXTIME</td><td style='padding-right:10px;'><b>Valuta:</b></td><td style='padding-right:20px;'>$DATAVAL</td>";
            $PREVIEW.="</tr>";
            // CAUSALE
            $PREVIEW.="<tr>";
            $PREVIEW.="<td style='padding-right:10px;'><b>Causale:</b></td><td colspan='3' style='padding-right:20px;'>$CAUSALE</td>";
            $PREVIEW.="</tr>";
            // IMPORTO
            $PREVIEW.="<tr>";
            $PREVIEW.="<td style='padding-right:10px;'><b>Importo:</b></td><td style='padding-right:20px;text-align:right;'>$AMOUNT</td><td colspan='2' style='padding-right:10px;'>$DIVISA</td>";
            $PREVIEW.="</tr>";
            
            // PIANO DEI CAMPI PERSONALIZZATI
            $colonna=0;
            $aperta=false;
            // RISOLUZIONE TABELLA
            maestro_query($maestro, "SELECT TABLENAME FROM QVARROWTYPES WHERE SYSID='$TYPOLOGYID'", $v);
            if(count($v)==1){
                $TABLENAME=$v[0]["TABLENAME"];
                if($TABLENAME!=""){
                    // LEGGO LA TABELLA
                    maestro_query($maestro, "SELECT * FROM $TABLENAME WHERE SYSID='$ARROWID'", $v);
                    if(count($v)==1){
                        // RISOLUZIONE CAMPI ESTESI
                        maestro_query($maestro, "SELECT * FROM QVARROWVIEWS WHERE TYPOLOGYID='$TYPOLOGYID'", $f);
                        for($i=0; $i<count($f); $i++){
                            $FIELDNAME=$f[$i]["FIELDNAME"];
                            $FIELDTYPE=strtoupper($f[$i]["FIELDTYPE"]);
                            $CAPTION=$f[$i]["CAPTION"];
                            if(strpos($FIELDTYPE, "SYSID")===false){
                                // DETERMINO IL VALORE E GESTISCO IL TIPO
                                $VALUE=$v[0][$FIELDNAME];
                                $align="";
                                if($FIELDTYPE=="INTEGER"){
                                    $VALUE=intval($VALUE);
                                    $align="text-align:right;";
                                }
                                elseif(substr($FIELDTYPE, 0, 8)=="RATIONAL"){
                                    $VALUE=formatta_numero(floatval($VALUE), 2);
                                    $align="text-align:right;";
                                }
                                elseif($FIELDTYPE=="BOOLEAN"){
                                    if(intval($VALUE))
                                        $VALUE="&#x2714;";
                                    else
                                        $VALUE="&#x0020;";
                                }
                                elseif($FIELDTYPE=="DATE" || $FIELDTYPE=="TIMESTAMP"){
                                    $VALUE=substr(str_replace($trdate, "", $VALUE), 0, 8);
                                    if($VALUE>"19000101")
                                        $VALUE=substr($VALUE,6,2)."/".substr($VALUE,4,2)."/".substr($VALUE,0,4);
                                    else
                                        $VALUE="";
                                }
                                else{
                                    $VALUE=substr($VALUE, 0, 40);
                                }
                                if($colonna==0){
                                    $PREVIEW.="<tr>";
                                    $aperta=true;
                                }
                                $PREVIEW.="<td style='padding-right:10px;'><b>$CAPTION:</b></td><td style='padding-right:20px;$align'>$VALUE</td>";
                                if($colonna==1){
                                    $PREVIEW.="</tr>";
                                    $aperta=false;
                                }
                                if($colonna==0)
                                    $colonna=1;
                                else
                                    $colonna=0;
                            }
                        }
                        if($aperta){
                            $PREVIEW.="</tr>";
                        }
                    }
                }
            }
            
            // RIGA
            $PREVIEW.="<tr>";
            $PREVIEW.="<td colspan='4' style='padding-top:5px;border-bottom:1px solid #eee;'> </td>";
            $PREVIEW.="</tr>";
            // RIGA VUOTA
            $PREVIEW.="<tr>";
            $PREVIEW.="<td colspan='4' style='padding-top:5px;'> </td>";
            $PREVIEW.="</tr>";
            // NOTE
            $PREVIEW.="<tr>";
            $PREVIEW.="<td colspan='4' style='padding-right:10px;'>$REGISTRY</td>";
            $PREVIEW.="</tr>";

            // CHIUDO LA TABELLA
            $PREVIEW.="</table>";
        }
        
        // VARIABILI DI RITORNO
        $babelparams["PREVIEW"]=$PREVIEW;
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
function formatta_numero($VALUE, $NUMDEC){
    $VALUE=strval($VALUE);
    if(strpos($VALUE, "-")!==false){
        $SIGNUM="-";
        $VALUE=str_replace("-", "", $VALUE);
    }
    else{
        $SIGNUM="";
    }
        
    $p=strpos($VALUE, ".");
    if($p!==false){
        $INT=substr($VALUE, 0, $p);
        $DEC=substr($VALUE, $p+1);
    }
    else{
        $INT=$VALUE;
        $DEC="";
        $p=strlen($INT);
    }
    if($INT==""){
        $INT="0";
    }
    for($i=$p-3;$i>0;$i-=3){
        $INT=substr($INT, 0, $i)."&#x02D9;".substr($INT, $i);
    }
    if($NUMDEC==0){
        $VALUE=$SIGNUM.$INT;
    }
    else{
        $DEC=substr($DEC."0000000", 0, $NUMDEC);
        $VALUE=$SIGNUM.$INT.",".$DEC;
    }
    return $VALUE;
}
?>