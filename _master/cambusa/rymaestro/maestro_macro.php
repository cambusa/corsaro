<?php 
/****************************************************************************
* Name:            maestro_macro.php                                        *
* Project:         Cambusa/ryMaestro                                        *
* Version:         1.70                                                     *
* Description:     Databases modeling and maintenance                       *
* Copyright (C):   2016  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
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
            $sql=maestro_dalmos($sql, "L", "DAMOS");
            break;
        case "mysql":
            $sql=maestro_dalmos($sql, "M", "DALOS");
            $sql=str_replace("\\", "\\\\", $sql);
            break;
        case "oracle":
            $sql=maestro_dalmos($sql, "O", "DALMS");
            // SOSTITUISCO I "SET A STRINGA VUOTA" CON "SET A NULL"
            while(preg_match("/(\sSET\s[^\\x00]+)=\s*§§([^\\x00]*\sWHERE\s)/i", maestro_mute($sql), $m, PREG_OFFSET_CAPTURE)==1){
                $offset=$m[0][1];
                $length=strlen($m[0][0]);
                $prefix=substr($sql, $m[1][1], strlen($m[1][0]));
                $suffix=substr($sql, $m[2][1], strlen($m[2][0]));
                $value=$prefix."=NULL".$suffix;
                $sql=substr($sql, 0, $offset).$value.substr($sql, $offset+$length);
            }
            // SOSTITUISCO GLI "UGUALI DA STRINGA VUOTA" CON "IS NULL"
            $max=preg_match_all("/=\s*§§(\s|\)|$)/", maestro_mute($sql), $m, PREG_OFFSET_CAPTURE);
            for($i=$max-1;$i>=0;$i--){
                $text=$m[1][$i][0];
                $offset=$m[0][$i][1];
                $length=strlen($m[0][$i][0]);
                $sql=substr($sql, 0, $offset)." IS NULL".$text.substr($sql, $offset+$length);
            }
            // SOSTITUISCO I "DIVERSI DA STRINGA VUOTA" CON "IS NOT NULL"
            $max=preg_match_all("/<>\s*§§(\s|\)|$)/", maestro_mute($sql), $m, PREG_OFFSET_CAPTURE);
            for($i=$max-1;$i>=0;$i--){
                $text=$m[1][$i][0];
                $offset=$m[0][$i][1];
                $length=strlen($m[0][$i][0]);
                $sql=substr($sql, 0, $offset)." IS NOT NULL".$text.substr($sql, $offset+$length);
            }
            break;
        case "db2odbc":
            $sql=maestro_dalmos($sql, "D", "ALMOS");
            break;
        case "access":
            $sql=maestro_dalmos($sql, "A", "DLMOS");
            break;
        default:
            $sql=maestro_dalmos($sql, "S", "DALMO");
        }
        // SOSTITUISCO I SYSID SENZA ARGOMENTO (NUOVO SYSID)
        $max=preg_match_all("/\[:SYSID\]/mi", maestro_mute($sql), $m, PREG_OFFSET_CAPTURE);
        for($i=$max-1;$i>=0;$i--){
            $sysid=qv_createsysid($maestro);
            $offset=$m[0][$i][1];
            $length=strlen($m[0][$i][0]);
            $sql=substr($sql,0,$offset)."'".$sysid."'".substr($sql,$offset+$length);
        }
        // SOSTITUISCO I SYSID CON ARGOMENTO (FORMATTAZIONE PER ADATTARNE LA LUNGHEZZA)
        $max=preg_match_all("/\[:SYSID\(([0-9A-Z]+)\)\]/mi", maestro_mute($sql), $m, PREG_OFFSET_CAPTURE);
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
        // [:ZEROLEN(field)]
        // [:NOTEMPTY(field)]
        // [:SETEMPTY(field)]
        // [:LENGTH(field)]
        // [:SUBSTR(field, begin, length)]
        // [:RIGHT(field, numchar)]
        // [:DATETOSTR(field)]
        // [:TIMETOSTR(field)]
        // [:NUMTOSTR(field, dec)]
        // [:STRTONUM(text)]
        // [:CONCAT(text1, text2, ...)]
        $mute=maestro_mute($sql);
        while(preg_match("/\[: *(DATE|TIME|TODAY|NOW|UPPER|BOOL|ZEROLEN|NOTEMPTY|SETEMPTY|LENGTH|SUBSTR|RIGHT|DATETOSTR|TIMETOSTR|NUMTOSTR|STRTONUM|CONCAT) *\(([^\[\]]*)\) *\]/mi", $mute)){
            $max=preg_match_all("/\[: *(DATE|TIME|TODAY|NOW|UPPER|BOOL|ZEROLEN|NOTEMPTY|SETEMPTY|LENGTH|SUBSTR|RIGHT|DATETOSTR|TIMETOSTR|NUMTOSTR|STRTONUM|CONCAT) *\(([^\[\]]*)\) *\]/mi", $mute, $m, PREG_OFFSET_CAPTURE);
            for($i=$max-1;$i>=0;$i--){
                $funct=trim(strtoupper($m[1][$i][0]));
                //$params=trim($m[2][$i][0]); // questa non va bene perché è stata "ammutolita"
                $params=trim(substr($sql, $m[2][$i][1], strlen($m[2][$i][0])));
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
                case "ZEROLEN":
                    if($maestro->provider!="oracle")
                        $value=$params."=''";
                    else
                        $value=$params." IS NULL";
                    break;
                case "NOTEMPTY":
                    if($maestro->provider!="oracle")
                        $value=$params."<>''";
                    else
                        $value="NOT ".$params." IS NULL";
                    break;
                case "SETEMPTY":
                    if($maestro->provider!="oracle")
                        $value=$params."=''";
                    else
                        $value=$params."=NULL";
                    break;
                case "LENGTH":
                    switch($maestro->provider){
                    case "sqlserver":
                    case "access":
                        $value="LEN(".$params.")";
                        break;
                    default:
                        $value="LENGTH(".$params.")";
                    }
                    break;
                case "SUBSTR":
                    switch($maestro->provider){
                    case "sqlserver":
                        $value="SUBSTRING(".$params.")";
                        break;
                    case "access":
                        $value="MID(".$params.")";
                        break;
                    default:
                        $value="SUBSTR(".$params.")";
                    }
                    break;
                case "RIGHT":
                    if(preg_match("/(.+) *, *(\d+)/mi", $params, $p)){
                        $value=trim($p[1]);
                        $len=intval($p[2]);
                    }
                    else{
                        $value=$params;
                        $len=0;
                    }
                    unset($p);
                    switch($maestro->provider){
                    case "sqlite":
                    case "mysql":
                    case "oracle":
                        $value="SUBSTR(RTRIM($value), -$len, $len)";
                        break;
                    default:
                        $value="RIGHT(RTRIM($value), $len)";
                    }
                    break;
                case "DATETOSTR":
                    switch($maestro->provider){
                    case "sqlite":
                        $value="SUBSTR($params,1,4)||SUBSTR($params,6,2)||SUBSTR($params,9,2)";
                        break;
                    case "mysql":
                        $value="DATE_FORMAT($params, '%Y%m%d')";
                        break;
                    case "oracle":
                        $value="TO_CHAR($params, 'YYYYMMDD')";
                        break;
                    case "db2odbc":
                        $value="TO_CHAR($params, 'YYYYMMDD')";
                        break;
                    case "access":
                        $value="FORMAT($params, 'YYYYMMDD')";
                        break;
                    default:
                        $value="CONVERT(VARCHAR(8), $params, 112)";
                    }
                    break;
                case "TIMETOSTR":
                    switch($maestro->provider){
                    case "sqlite":
                        $value="SUBSTR($params,1,4)||SUBSTR($params,6,2)||SUBSTR($params,9,2)||SUBSTR($params,12,2)||SUBSTR($params,15,2)||SUBSTR($params,18,2)";
                        break;
                    case "mysql":
                        $value="DATE_FORMAT($params, '%Y%m%d%H%i%s')";
                        break;
                    case "oracle":
                        $value="TO_CHAR($params, 'YYYYMMDDHH24MISS')";
                        break;
                    case "db2odbc":
                        $value="TO_CHAR($params, 'YYYYMMDDHH24MISS')";
                        break;
                    case "access":
                        $value="FORMAT($params, 'YYYYMMDDHHMMSS')";
                        break;
                    default:
                        $value="CONVERT(VARCHAR(8), $params, 112)+REPLACE(CONVERT(VARCHAR(8), $params, 108), ':', '')";
                    }
                    break;
                case "NUMTOSTR":
                    if(preg_match("/(.+) *, *(\d+)/mi", $params, $p)){
                        $value=trim($p[1]);
                        $dec=intval($p[2]);
                    }
                    else{
                        $value=$params;
                        $dec=0;
                    }
                    unset($p);
                    switch($maestro->provider){
                    case "sqlite":
                        $value="FORMAT($value, $dec)";
                        break;
                    case "mysql":
                        $value="REPLACE(FORMAT($value, $dec), ',', '')";
                        break;
                    case "oracle":
                        if($dec>0)
                            $value="TO_CHAR($value, '99999999999999999990.".(str_repeat("9", $dec))."')";
                        else
                            $value="TO_CHAR($value, '99999999999999999990')";
                        break;
                    case "db2odbc":
                        if($dec>0)
                            $value="TO_CHAR($value, '99999999999999999990.".(str_repeat("9", $dec))."')";
                        else
                            $value="TO_CHAR($value, '99999999999999999990')";
                        break;
                    case "access":
                        // DECIMALI NON GESTITI
                        $value="STR($value)";
                        break;
                    default:
                        $value="CAST(CONVERT(DECIMAL(20, $dec), $value) AS VARCHAR(20))";
                    }
                    break;
                case "STRTONUM":
                    switch($maestro->provider){
                    case "sqlite":
                        $value="CAST($params AS DECIMAL(28,7))";
                        break;
                    case "mysql":
                        $value="CAST($params AS DECIMAL(28,7))";
                        break;
                    case "oracle":
                        $value="CAST($params AS NUMBER(28,7))";
                        break;
                    case "db2odbc":
                        $value="CAST($params AS DECIMAL(28,7))";
                        break;
                    case "access":
                        $value="VAL($params)";
                        break;
                    default:
                        $value="CAST($params AS DECIMAL(28,7))";
                    }
                    break;
                case "CONCAT":
                    switch($maestro->provider){
                    case "sqlite":
                        $value=str_replace(",", "||", $params);
                        break;
                    case "access":
                        $value=str_replace(",", "+", $params);
                        break;
                    default:
                        $value="CONCAT($params)";
                    }
                    break;
                default:
                    $value="######";
                }
                $sql=substr($sql,0,$offset).$value.substr($sql,$offset+$length);
            }
            unset($m);
            $mute=maestro_mute($sql);
        }
    }
    catch(Exception $e){}
    return $sql;
}

function maestro_mute($sql){
    $max=preg_match_all("/'[^']*'/", $sql, $m, PREG_OFFSET_CAPTURE);
    for($i=$max-1;$i>=0;$i--){
        $offset=$m[0][$i][1];
        $length=strlen($m[0][$i][0]);
        $value="§".str_repeat("_", $length-2)."§";
        $sql=substr($sql, 0, $offset).$value.substr($sql, $offset+$length);
    }
    return $sql;
}

function maestro_dalmos($sql, $incl, $escl){
    $max=preg_match_all("/\{[$escl]+:([^{}]+)\}/", maestro_mute($sql), $m, PREG_OFFSET_CAPTURE);
    for($i=$max-1;$i>=0;$i--){
        $offset=$m[0][$i][1];
        $length=strlen($m[0][$i][0]);
        $sql=substr($sql,0,$offset).substr($sql,$offset+$length);
    }
    $max=preg_match_all("/\{[$escl]*".$incl."[$escl]*:([^{}]+)\}/", maestro_mute($sql), $m, PREG_OFFSET_CAPTURE);
    for($i=$max-1;$i>=0;$i--){
        //$text=$m[1][$i][0];
        $text=substr($sql, $m[1][$i][1], strlen($m[1][$i][0]));
        $offset=$m[0][$i][1];
        $length=strlen($m[0][$i][0]);
        $sql=substr($sql,0,$offset).$text.substr($sql,$offset+$length);
    }
    return $sql;
}
?>