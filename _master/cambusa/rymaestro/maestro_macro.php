<?php 
/****************************************************************************
* Name:            maestro_macro.php                                        *
* Project:         Cambusa/ryMaestro                                        *
* Version:         1.00                                                     *
* Description:     Databases modeling and maintenance                       *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rymaestro/maestro_execlib.php";
include_once $tocambusa."ryque/ryq_util.php";
include_once $tocambusa."ryquiver/quiversex.php";

function maestro_macro($maestro, $sql){
    try{
        switch($maestro->provider){ // CRITERIO "DALMOS"
        case "sqlite":
            $sql=preg_replace("/\{[DAMOS]+:([^{}]+)\}/", "", $sql);
            $sql=preg_replace("/\{[DAMOS]*L[DAMOS]*:([^{}]+)\}/", "$1", $sql);
            break;
        case "mysql":
            $sql=preg_replace("/\{[DALOS]+:([^{}]+)\}/", "", $sql);
            $sql=preg_replace("/\{[DALOS]*M[DALOS]*:([^{}]+)\}/", "$1", $sql);
            break;
        case "oracle":
            $sql=preg_replace("/\{[DALMS]+:([^{}]+)\}/", "", $sql);
            $sql=preg_replace("/\{[DALMS]*O[DALMS]*:([^{}]+)\}/", "$1", $sql);
            while(preg_match("/( SET .+)= *''(.* WHERE )/i", $sql)){
                $sql=preg_replace("/( SET .+)= *''(.* WHERE )/i", "$1=NULL$2", $sql);
            }
            $sql=preg_replace("/= *''( |\)|$)/i", " IS NULL$1", $sql);
            $sql=preg_replace("/<> *''( |\)|$)/i", " IS NOT NULL$1", $sql);
            break;
        case "db2odbc":
            $sql=preg_replace("/\{[ALMOS]+:([^{}]+)\}/", "", $sql);
            $sql=preg_replace("/\{[ALMOS]*D[ALMOS]*:([^{}]+)\}/", "$1", $sql);
            break;
        case "access":
            $sql=preg_replace("/\{[DLMOS]+:([^{}]+)\}/", "", $sql);
            $sql=preg_replace("/\{[DLMOS]*A[DLMOS]*:([^{}]+)\}/", "$1", $sql);
            break;
        default:
            $sql=preg_replace("/\{[DALMO]+:([^{}]+)\}/", "", $sql);
            $sql=preg_replace("/\{[DALMO]*S[DALMO]*:([^{}]+)\}/", "$1", $sql);
        }
        // SOSTITUISCO I SYSID
        $max=preg_match_all("/\[:SYSID\]/mi", $sql, $m, PREG_OFFSET_CAPTURE);
        for($i=$max-1;$i>=0;$i--){
            $sysid=qv_createsysid($maestro);
            $offset=$m[0][$i][1];
            $length=strlen($m[0][$i][0]);
            $sql=substr($sql,0,$offset)."'".$sysid."'".substr($sql,$offset+$length);
        }
        $max=preg_match_all("/\[:SYSID\(([0-9A-Z]+)\)\]/mi", $sql, $m, PREG_OFFSET_CAPTURE);
        for($i=$max-1;$i>=0;$i--){
            $sysid=trim(strtoupper($m[1][$i][0]));
            if(strlen($sysid)!=$maestro->lenid){
                $sysid=substr($sysid . str_repeat("0", $maestro->lenid), 0, $maestro->lenid);
            }
            $offset=$m[0][$i][1];
            $length=strlen($m[0][$i][0]);
            $sql=substr($sql,0,$offset)."'".$sysid."'".substr($sql,$offset+$length);
        }
        unset($m);
        // SOSTITUISCO LE FUNZIONI
        // [:DATE(value, add unit)]
        // [:TIME(value, add unit)]
        // [:TODAY()]
        // [:NOW()]
        // [:UPPER(text)]
        // [:BOOL(bvalue)]
        $max=preg_match_all("/\[: *([^()\[\]]+) *\(([^\[\]]*)\) *\]/mi", $sql, $m, PREG_OFFSET_CAPTURE);
        for($i=$max-1;$i>=0;$i--){
            $funct=trim(strtoupper($m[1][$i][0]));
            $params=trim($m[2][$i][0]);
            $offset=$m[0][$i][1];
            $length=strlen($m[0][$i][0]);
            switch($funct){
            case "DATE":
                if(preg_match("/(.+) *, *(-?\d+) *(DAY|MONTH|YEAR)S?/mi", $params, $p)){
                    $value=trim($p[1]);
                    $incr=intval($p[2]);
                    $unit=strtoupper($p[3]);
                }
                else{
                    $value=$params;
                    $incr=0;
                    $unit="";
                }
                unset($p);
                if($value==""){
                    $value="19000101";
                }
                if(strpos("0123456789", substr($value,0,1))!==false){
                    // COSTANTE
                    // NORMALIZZAZIONE
                    $value=str_replace("-", "", $value);
                    // PREVENZIONE SQL-INJECTION
                    $value=str_replace("'", "", $value);
                    $year=substr($value,0,4);
                    $month=substr($value,4,2);
                    $day=substr($value,6,2);
                    switch($maestro->provider){
                    case "sqlite":
                        $value="'".$year."-".$month."-".$day."'";
                        break;
                    case "mysql":
                        $value="'".$year."-".$month."-".$day."'";
                        break;
                    case "oracle":
                        $value="TO_DATE('".$year.$month.$day."', 'YYYYMMDD')";
                        break;
                    case "db2odbc":
                        $value="DATE('".$year."-".$month."-".$day."')";
                        break;
                    case "access":
                        $value="#".$year."-".$month."-".$day."#";
                        break;
                    default:
                        $value="'".$year.$month.$day."'";
                    }
                }
                if($incr!=0){
                    switch($maestro->provider){
                    case "sqlite":
                        $value="date(".$value.",'".$incr." ".$unit."')";
                        break;
                    case "mysql":
                        $value="ADDDATE(".$value.", INTERVAL ".$incr." ".$unit.")";
                        break;
                    case "oracle":
                        switch($unit){
                        case "DAY":
                            $value=$value."+".$incr;
                            break;
                        case "MONTH":
                            $value="ADD_MONTHS(".$value.", ".$incr.")";
                            break;
                        case "YEAR":
                            $value="ADD_MONTHS(".$value.", ".(12*$incr).")";
                            break;
                        default:
                            $value="";
                        }
                        break;
                    case "db2odbc":
                        $value=$value."+".$incr." ".$unit;
                        break;
                    case "access":
                        switch($unit){
                            case "DAY":$unit="'d'";break;
                            case "MONTH":$unit="'m'";break;
                            case "YEAR":$unit="'yyyy'";break;
                        }
                        $value="DATEADD(".$unit.",".$incr.",".$value.")";
                        break;
                    default:
                        $value="DATEADD(".$unit.",".$incr.",".$value.")";
                    }
                }
                break;
            case "TIME":
                if(preg_match("/(.+) *, *(-?\d+) *(SECOND|MINUTE|HOUR)S?/mi", $params, $p)){
                    $value=trim($p[1]);
                    $incr=intval($p[2]);
                    $unit=strtoupper($p[3]);
                }
                else{
                    $value=$params;
                    $incr=0;
                    $unit="";
                }
                unset($p);
                if($value==""){
                    $value="19000101";
                }
                if(strpos("0123456789", substr($value,0,1))!==false){
                    // COSTANTE
                    // NORMALIZZAZIONE
                    $value=str_replace("-", "", $value);
                    $value=str_replace(":", "", $value);
                    $value=str_replace("T", "", $value);
                    $value=str_replace(" ", "", $value);
                    // PREVENZIONE SQL-INJECTION
                    $value=str_replace("'", "", $value);
                    $year=substr($value,0,4);
                    $month=substr($value,4,2);
                    $day=substr($value,6,2);
                    if(strlen($value)>8){
                        $hour=substr($value,8,2);
                        $minute=substr($value,10,2);
                        $second=substr($value,12,2);
                    }
                    else{
                        $hour="00";
                        $minute="00";
                        $second="00";
                    }
                    switch($maestro->provider){
                    case "sqlite":
                        $value="'".$year."-".$month."-".$day." ".$hour.":".$minute.":".$second."'";
                        break;
                    case "mysql":
                        $value="'".$year."-".$month."-".$day." ".$hour.":".$minute.":".$second."'";
                        break;
                    case "oracle":
                        $value="TO_DATE('".$year.$month.$day." ".$hour.":".$minute.":".$second."', 'YYYYMMDD HH24:MI:SS')";
                        break;
                    case "db2odbc":
                        $value="TIMESTAMP('".$year."-".$month."-".$day." ".$hour.":".$minute.":".$second."')";
                        break;
                    case "access":
                        $value="#".$year."-".$month."-".$day." ".$hour.":".$minute.":".$second."#";
                        break;
                    default:
                        $value="'".$year.$month.$day." ".$hour.":".$minute.":".$second."'";
                    }
                }
                if($incr!=0){
                    switch($maestro->provider){
                    case "sqlite":
                        $value="datetime(".$value.",'".$incr." ".$unit."')";
                        break;
                    case "mysql":
                        $days=0;
                        $hours=0;
                        $minutes=0;
                        $seconds=0;
                        switch($unit){
                        case "HOUR":
                            $days=floor($incr/24);
                            $hours=$incr % 24;
                            break;
                        case "MINUTE":
                            $days=floor($incr/(24*60));
                            $hours=floor($incr/60) % 24;
                            $minutes=$incr % 60;
                            break;
                        case "SECOND":
                            $days=floor($incr/(24*3600));
                            $hours=floor($incr/3600) % 24;
                            $minutes=floor($incr/60) % 60;
                            $seconds=$incr % 60;
                            break;
                        }
                        $value="ADDTIME(".$value.", '$days $hours:$minutes:$seconds')";
                        break;
                    case "oracle":
                        switch($unit){
                        case "HOUR":
                            $value=$value."+".($incr/24);
                            break;
                        case "MINUTE":
                            $value=$value."+".($incr/(24*60));
                            break;
                        case "SECOND":
                            $value=$value."+".($incr/(24*3600));
                            break;
                        default:
                            $value="";
                        }
                        break;
                    case "db2odbc":
                        $value=$value."+".$incr." ".$unit;
                        break;
                    case "access":
                        switch($unit){
                            case "SECOND":$unit="'s'";break;
                            case "MINUTE":$unit="'n'";break;
                            case "HOUR":$unit="'h'";break;
                        }
                        $value="DATEADD(".$unit.",".$incr.",".$value.")";
                        break;
                    default:
                        $value="DATEADD(".$unit.",".$incr.",".$value.")";
                    }
                }
                break;
            case "TODAY":
                switch($maestro->provider){
                case "sqlite":
                    $value="date('now')";
                    break;
                case "mysql":
                    $value="CURDATE()";
                    break;
                case "oracle":
                    $value="TRUNC(CURRENT_DATE)";
                    break;
                case "db2odbc":
                    $value="(current date)";
                    break;
                case "access":
                    $value="DATE()";
                    break;
                default:
                    $value="CAST(GETDATE() AS DATE)";
                }
                break;
            case "NOW":
                switch($maestro->provider){
                case "sqlite":
                    $value="datetime('now','localtime')";
                    break;
                case "mysql":
                    $value="CURRENT_TIMESTAMP()";
                    break;
                case "oracle":
                    $value="CURRENT_DATE";
                    break;
                case "db2odbc":
                    $value="(current timestamp)";
                    break;
                case "access":
                    $value="NOW";
                    break;
                default:
                    $value="GETDATE()";
                }
                break;
            case "UPPER":
                if($maestro->provider=="access")
                    $value="UCASE(".$params.")";
                else
                    $value="UPPER(".$params.")";
                break;            
            case "BOOL":
                if($maestro->provider!="access")
                    $value="(CASE WHEN (".$params.") THEN 1 ELSE 0 END)";
                else
                    $value="(".$params.")";
                break;
            default:
                $value="";
            }
            $sql=substr($sql,0,$offset).$value.substr($sql,$offset+$length);
        }
        unset($m);
    }
    catch(Exception $e){}
    return $sql;
}
?>