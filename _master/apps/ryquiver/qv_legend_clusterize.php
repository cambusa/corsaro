<?php 
/****************************************************************************
* Name:            qv_legend_clusterize.php                                 *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_legend_clusterize($maestro, $data){
    global $babelcode, $babelparams;
    global $path_cambusa;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
                
        $FLAG=intval($data["FLAG"]);
        if($FLAG){
            $SYSID=qv_createsysid($maestro);
        }
        
        $QUERIES=$data["QUERIES"];
        foreach($QUERIES as $QUERY){
            $REQUESTID=$QUERY["REQUESTID"];
            $CURRENT=intval($QUERY["CURRENT"]);
            $SELECTION=$QUERY["SELECTION"];
            $INVERT=intval($QUERY["INVERT"]);
            $TABLE=$QUERY["TABLE"];
            
            $reqpath=$path_cambusa."ryque/requests/".$REQUESTID;
            $index=file_get_contents($reqpath.".ndx");
            $lenkey=$maestro->lenid;
            $selected="";
            
            $s=array();
            if($SELECTION!="" || $INVERT!=0){
                $v=explode("|", $SELECTION);
                if($INVERT==0){
                    foreach($v as $i){
                        $s[]=substr($index, (intval($i)-1)*($lenkey+1), $lenkey);
                    }
                }
                else{
                    for($i=1; $i<=round(strlen($index)/($lenkey+1)); $i++){
                        if(!in_array($i, $v)){
                            $s[]=substr($index, ($i-1)*($lenkey+1), $lenkey);
                        }
                    }
                }
            }
            if(count($s)>0){
                $c=array_chunk($s, 1000);
                foreach($c as $v){
                    $selected="'".implode("','", $v)."'";
                    if($SYSID=="")
                        $AUX="AND CLUSTERID<>''";
                    else
                        $AUX="";
                    $sql="UPDATE $TABLE SET CLUSTERID='$SYSID' WHERE SYSID IN ($selected) $AUX";
                    if(!maestro_execute($maestro, $sql, false)){
                        $babelcode="QVERR_EXECUTE";
                        $trace=debug_backtrace();
                        $b_params=array("FUNCTION" => $trace[0]["function"] );
                        $b_pattern=$maestro->errdescr;
                        throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
                    }
                }
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