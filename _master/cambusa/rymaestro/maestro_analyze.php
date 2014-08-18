<?php 
/****************************************************************************
* Name:            maestro_analyze.php                                      *
* Project:         Cambusa/ryMaestro                                        *
* Version:         1.00                                                     *
* Description:     Databases modeling and maintenance                       *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

function MaestroAnalyze($maestro, &$success, &$description){
    global $path_databases;
    
    $json="";
    $success=1;
    $description="Analisi terminata";

    $allcolumns=array();
    $allcolumns["__INFOS"]=array("type" => "infos", "provider" => $maestro->provider);
    
    $conn=&$maestro->conn;
    
    // GESTIONE PER DATABASE
    switch($maestro->provider){
    case "sqlite":
        $sql=sqlite_query($conn, "SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;");
        $res=sqlite_fetch_all($sql, SQLITE_ASSOC);
        foreach($res as $entry){
            $tab=$entry['name'];
            $allcolumns[$tab]=array();
            $allcolumns[$tab]["type"]="database";
            $allcolumns[$tab]["fields"]=array();

            $cols=sqlite_fetch_column_types($tab, $conn, SQLITE_ASSOC);
            foreach($cols as $f => $t) {
                // DETERMINO IL TIPO ASTRATTO
                $dbsize=-1;
                $dbprec=-1;
                $dbscale=-1;
                maestro_abstract($f, $t, $dbsize, $dbprec, $dbscale, $abstract, $size);
                // CARICO IL VETTORE
                $allcolumns[$tab]["fields"][$f]["type"]=$abstract;
                if($size>0)
                    $allcolumns[$tab]["fields"][$f]["size"]=$size;
            }
        }
        break;
    case "mysql":
        $res=mysqli_query($conn, "SHOW TABLES FROM ".$maestro->strconn);
        while($row=mysqli_fetch_row($res)){
            $tab=$row[0];
            $allcolumns[$tab]=array();
            $allcolumns[$tab]["type"]="database";
            $allcolumns[$tab]["fields"]=array();
            if($res2=mysqli_query($conn, "SHOW COLUMNS FROM $tab")){
                while($row2=mysqli_fetch_row($res2)){
                    $f=$row2[0];
                    $t=$row2[1];
                    // DETERMINO IL TIPO ASTRATTO
                    $dbsize=-1;
                    $dbprec=-1;
                    $dbscale=-1;
                    maestro_abstract($f, $t, $dbsize, $dbprec, $dbscale, $abstract, $size);
                    // CARICO IL VETTORE
                    $allcolumns[$tab]["fields"][$f]["type"]=$abstract;
                    if($size>0)
                        $allcolumns[$tab]["fields"][$f]["size"]=$size;
                    if($row2[2]=="NO")
                        $allcolumns[$tab]["fields"][$f]["notnull"]="1";
                }
                mysqli_free_result($res2);
            }
        }
        mysqli_free_result($res);
        break;
    case "oracle":
        oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_DATE_FORMAT='YYYY-MM-DD\"T\"HH24:MI:SS\".000Z\"'"));
        oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_TIMESTAMP_FORMAT='YYYY-MM-DD\"T\"HH24:MI:SS.FF3\"Z\"'"));
        oci_execute(oci_parse($conn, "ALTER SESSION SET NLS_NUMERIC_CHARACTERS='.,'"));
        $res=oci_parse($conn, "SELECT TABLE_NAME FROM tabs where UPPER(tablespace_name)=UPPER('".$maestro->user."')");
        oci_execute($res);
        while($row=oci_fetch_array($res, OCI_ASSOC+OCI_RETURN_NULLS)){
            $tab=$row["TABLE_NAME"];
            if(strpos($tab,"$")===false){
                $allcolumns[$tab]=array();
                $allcolumns[$tab]["type"]="database";
                $allcolumns[$tab]["fields"]=array();
                $res2=oci_parse($conn, "SELECT COLUMN_NAME, DATA_TYPE, DATA_LENGTH, DATA_PRECISION, DATA_SCALE, NULLABLE FROM all_tab_columns WHERE table_name = '$tab'");
                oci_execute($res2);
                while($row2=oci_fetch_array($res2, OCI_ASSOC+OCI_RETURN_NULLS)){
                    $f=$row2["COLUMN_NAME"];
                    $t=$row2["DATA_TYPE"];
                    $s=$row2["DATA_LENGTH"];
                    // DETERMINO IL TIPO ASTRATTO
                    $dbsize=$s;
                    $dbprec=-1;
                    $dbscale=-1;
                    if(isset($row2["DATA_PRECISION"]))
                        $dbprec=$row2["DATA_PRECISION"];
                    if(isset($row2["DATA_SCALE"]))
                        $dbscale=$row2["DATA_SCALE"];
                    maestro_abstract($f, $t, $dbsize, $dbprec, $dbscale, $abstract, $size);
                    // CARICO IL VETTORE
                    $allcolumns[$tab]["fields"][$f]["type"]=$abstract;
                    if(strpos($t,"CHAR")!==false)
                        $allcolumns[$tab]["fields"][$f]["size"]=$s;
                    if($row2["NULLABLE"]=="N")
                        $allcolumns[$tab]["fields"][$f]["notnull"]="1";
                }
                oci_free_statement($res2);
            }
        }
        oci_free_statement($res);
        break;
    default:
        $res=odbc_tables($conn);
        while(odbc_fetch_row($res)){
            if(odbc_result($res,"TABLE_TYPE")=="TABLE"){
                $tab=odbc_result($res,"TABLE_NAME");
                $allcolumns[$tab]=array();
                $allcolumns[$tab]["type"]="database";
                $allcolumns[$tab]["fields"]=array();
            }
        }
        odbc_free_result($res);
        
        $res=odbc_columns($conn);
        while ($rows=odbc_fetch_object($res)){
            $tab=$rows->TABLE_NAME;
            if(array_key_exists($tab,$allcolumns)){
                $f=$rows->COLUMN_NAME;
                $allcolumns[$tab]["fields"][$f]=array();
                $t=$rows->TYPE_NAME;
                $s=$rows->COLUMN_SIZE;
                // DETERMINO IL TIPO ASTRATTO
                $dbsize=$s;
                $dbprec=-1;
                $dbscale=-1;
                if(isset($rows->DECIMAL_DIGITS)){
                    $dbprec=$dbsize;
                    $dbscale=$rows->DECIMAL_DIGITS;
                }
                maestro_abstract($f, $t, $dbsize, $dbprec, $dbscale, $abstract, $size);
                // CARICO IL VETTORE
                $allcolumns[$tab]["fields"][$f]["type"]=$abstract;
                if($size>0)
                    $allcolumns[$tab]["fields"][$f]["size"]=$size;
                if($rows->NULLABLE=="0")
                    $allcolumns[$tab]["fields"][$f]["notnull"]="1";
                if($rows->COLUMN_DEF)
                    $allcolumns[$tab]["fields"][$f]["default"]=$rows->COLUMN_DEF;
            }
        }
        odbc_free_result($res);
    }
    $json=json_encode($allcolumns);
    
    $json=preg_replace("/\{([^{}]*)\}/", "\x02$1\x03", $json);
    $json=preg_replace("/\{([^{}]*)\}/", "\x04$1\x05", $json);
    $json=preg_replace("/(\x03,)/", "$1\r\n            ", $json);
    
    $json=preg_replace("/\x22type\x22:\x22database\x22,\x22fields\x22:(\x04)/", "        \x01type\x01:\x01database\x01,\r\n        \x01fields\x01:$1\r\n            ", $json);
    
    $json=preg_replace("/[}]$/", "\r\n}", $json);
    $json=preg_replace("/(\x22[^\x22]+\x22:[{])/", "\r\n    $1\r\n", $json);
    
    $json=preg_replace("/\x01/", "\x22", $json);
    $json=preg_replace("/\x02/", "{", $json);
    $json=preg_replace("/\x03/", "}", $json);
    $json=preg_replace("/\x04/", "{", $json);
    $json=preg_replace("/\x05/", "}", $json);
    
    $json=preg_replace("/\}\}\}(,?)/", "}\r\n        }\r\n    }$1", $json);

    return $json;
}
?>