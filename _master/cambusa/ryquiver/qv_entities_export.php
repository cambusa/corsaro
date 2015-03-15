<?php 
/****************************************************************************
* Name:            qv_entities_export.php                                   *
* Project:         Cambusa/ryQuiver                                         *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
include_once "quiverfil.php";
function qv_entities_export($maestro, $data){
    global $babelcode, $babelparams;
    global $path_customize;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // RISOLVO DIRECTORY TEMPORANEA E DIRECTORY ALLEGATI
        qv_environs($maestro, $dirtemp, $dirattach);
        
        // CARICO LA STRUTTURA DEL DATABASE
        $maestro->loadinfo();
        
        // DETERMINO TABLEBASE
        $TABLEBASE="";
        if(isset($data["TABLEBASE"])){
            $TABLEBASE=ryqEscapize($data["TABLEBASE"]);
        }
        if($TABLEBASE==""){
            $babelcode="QVERR_TABLEBASE";
            $b_params=array();
            $b_pattern="Nome tabella non specificato";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        switch(strtoupper($TABLEBASE)){
        case "QVGENRES":
            $tabletypes="QVGENRETYPES";
            $tableviews="QVGENREVIEWS";
            break;
        case "QVOBJECTS":
            $tabletypes="QVOBJECTTYPES";
            $tableviews="QVOBJECTVIEWS";
            break;
        case "QVMOTIVES":
            $tabletypes="QVMOTIVETYPES";
            $tableviews="QVMOTIVEVIEWS";
            break;
        case "QVARROWS":
            $tabletypes="QVARROWTYPES";
            $tableviews="QVARROWVIEWS";
            break;
        case "QVQUIVERS":
            $tabletypes="QVQUIVERTYPES";
            $tableviews="QVQUIVERVIEWS";
            break;
        default:
            $babelcode="QVERR_NOTABLEBASE";
            $b_params=array();
            $b_pattern="Nome tabella non riconosciuto";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }

        // INDIVIDUAZIONE RECORD
        $record=qv_solverecord($maestro, $data, $TABLEBASE, "SYSID", "NAME", $SYSID, "*");
        if($SYSID==""){
            $babelcode="QVERR_SYSID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il record";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        $EXPORTNAME=$record["SYSID"];
        $EXPORTDESCR=$record["DESCRIPTION"];
        $TYPOLOGYID=$record["TYPOLOGYID"];
        
        // INIZIALIZZO IL VETTORE DEI CAMPI
        $FIELDNAMES=array();
        $FIELDTYPES=array();
        
        // CAMPI BASE
        $fields=$maestro->infobase->{$TABLEBASE}->fields;
        foreach($fields as $field => $attr){
            $FIELDNAMES[]=$field;
            $FIELDTYPES[]=strtoupper($attr->type);
        }

        // LEGGO LA TIPOLOGIA
        maestro_query($maestro,"SELECT TABLENAME FROM $tabletypes WHERE SYSID='$TYPOLOGYID'",$r);
        if(count($r)==1){
            $TABLENAME=$r[0]["TABLENAME"];
            if($TABLENAME!=""){
                // LA VISTA DOVREBBE ESTENDERE I DATI
                maestro_query($maestro,"SELECT * FROM $tableviews WHERE TYPOLOGYID='$TYPOLOGYID'", $f);
                if(count($f)>0){
                    for($i=0;$i<count($f);$i++){
                        if(intval($F[$i]["WRITABLE"])){
                            $FIELDNAMES[]=$f[$i]["FIELDNAME"];
                            $FIELDTYPES[]=$f[$i]["FIELDTYPE"];
                        }
                    }
                }
                // AGGIUNGO I DATI ESTESI
                maestro_query($maestro,"SELECT * FROM $TABLENAME WHERE SYSID='$SYSID'", $f);
                if(count($f)==1){
                    $record=array_merge($record, $f[0]);
                }
            }
        }
        else{
            $babelcode="QVERR_NOTYPOLOGY";
            $b_params=array();
            $b_pattern="Tipologia sconosciuta";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        // PREPARAZIONE 
        $statement=array();
        // Tabella
        $statement["table"]=$TABLEBASE;
        $statement["extension"]=$TABLENAME;
        // Dati
        $datax=array();
        for($i=0; $i<count($FIELDNAMES); $i++){
            $field=$FIELDNAMES[$i];
            if(isset($record[$field]))
                $value=$record[$field];
            else
                $value="";
            // TIPIZZAZIONE
            switch($FIELDTYPES[$i]){
            case "INTEGER":
                $value=strval(intval($value));
                break;
            case "RATIONAL":
                $value=strval(round(floatval($value),7));
                break;
            case "DATE":
                $value=qv_strtime($value);
                break;
            case "TIMESTAMP":
                $value=qv_strtime($value);
                break;
            case "BOOLEAN":
                if(intval($value)!=0)
                    $value="1";
                else
                    $value="0";
                break;
            default:
                if(strpos($FIELDTYPES[$i], "RATIONAL(")!==false){
                    $dec=intval(substr($FIELDTYPE, 9));
                    $value=strval(round(floatval($value), $dec));
                }
                elseif(strpos($FIELDTYPES[$i], "SYSID")!==false){
                    if($field=="SYSID")
                        $value="[:SYSID(".$value.")]";
                    elseif($field=="TYPOLOGYID" || substr($value, 0, 1)=="0")
                        $value=qv_actualid($maestro, $value);
                    else
                        $value="";
                }
                elseif($field=="NAME"){
                    if(substr($value, 0, 2)=="__"){
                        $value="";
                    }
                }
                break;
            }
            $datax[$field]=$value;
        }
        unset($record);
        // Aggancio all'istruzione
        $statement["data"]=$datax;
        $program[]=$statement;
        unset($datax);
        unset($statement);

        // Aggancio alla radice
        $export["type"]=$TABLEBASE;
        $export["description"]=$EXPORTDESCR;
        $export["program"]=$program;
        unset($program);
        
        // SERIALIZZAZIONE
        $buff=serialize($export);
        
        // SCRITTURA SU FILE
        $pathname=$path_customize."_export/$EXPORTNAME.QVR";
        $fp=fopen($pathname, "wb");
        fwrite($fp, $buff);
        fclose($fp);
        
        // VARIABILI DI RITORNO
        $babelparams["EXPORTED"]="_export/$EXPORTNAME.QVR";
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