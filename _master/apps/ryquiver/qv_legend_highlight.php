<?php 
/****************************************************************************
* Name:            qv_legend_highlight.php                                  *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_legend_highlight($maestro, $data){
    global $babelcode, $babelparams;
    global $path_cambusa;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        $lenkey=$maestro->lenid;
        $ORDERBY=array();

        // DETERMINO LA TOTALITA' DELLE FRECCE COINVOLTE 
        $QUERIES=$data["QUERIES"];
        foreach($QUERIES as $QUERY){
            $REQUESTID=$QUERY["REQUESTID"];
            $SELECTION=$QUERY["SELECTION"];
            $QUERYID=$QUERY["QUERYID"];
            
            $reqpath=$path_cambusa."ryque/requests/".$REQUESTID;
            $index=file_get_contents($reqpath.".ndx");
            
            $in="'";
            if($index!=""){
                if($SELECTION!=""){
                    $v=explode("|", $SELECTION);
                    foreach($v as $i){
                        $ARROWID=substr($index, ($i-1)*($lenkey+1), $lenkey);
                        // ELENCO ORDERBY
                        if($in=="'")
                            $in.=$ARROWID;
                        else
                            $in.="','".$ARROWID;
                    }
                }
            }
            $in.="'";
            if($in!="''")
                $ORDERBY[$QUERYID]="(CASE WHEN SYSID IN ($in) THEN 0 ELSE 1 END)";
            else
                $ORDERBY[$QUERYID]="SYSID";
        }
        // VARIABILI DI RITORNO
        $babelparams["ORDERBY"]=$ORDERBY;
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