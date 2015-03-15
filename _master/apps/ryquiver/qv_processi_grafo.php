<?php 
/****************************************************************************
* Name:            qv_processi_grafo.php                                    *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function qv_processi_grafo($maestro, $data){
    global $global_quiveruserid,$global_quiverroleid;
    global $babelcode, $babelparams;
    try{
        // IMPOSTO I VALORI DI RITORNO PREDEFINITI
        $success=1;
        $message="Operazione riuscita";
        $SYSID="";
        
        // INIZIALIZZO IL GRAFO
        $grafo="";
        
        // PREFISSO DEGLI IDENTIFICATORI
        if(isset($data["PREFIX"]))
            $PREFIX=$data["PREFIX"];
        else
            $PREFIX="";
        
        // LEGGO IL PROCESSO
        $processo=qv_solverecord($maestro, $data, "QW_PROCESSI", "PROCESSOID", "", $PROCESSOID, "*");
        if($PROCESSOID==""){
            $babelcode="QVERR_PROCESSOID";
            $b_params=array();
            $b_pattern="Dati insufficienti per individuare il processo";
            throw new Exception( qv_babeltranslate($b_pattern, $b_params) );
        }
        
        $offsety=40;
        $width=140;
        $height=50;
        $deltah=60;
        $w=$width+6;
        $h=$height+8;
        // LEGGO GLI STATI
        /*********************************************************
         __________________ 
        |                  BU
        |                  |
        |__________________|
          TD             _______________TU_ 
                        |                  |
                        |                  |
                       BD__________________|
        
        *********************************************************/
        $stati=array();
        maestro_query($maestro, "SELECT * FROM QW_PROCSTATI WHERE PROCESSOID='$PROCESSOID' ORDER BY ORDINATORE", $r);
        for($i=0; $i<count($r); $i++){
            $STATOID=$r[$i]["SYSID"];
            $stati[$STATOID]=array();
            $stati[$STATOID]["DESCRIPTION"]=$r[$i]["DESCRIPTION"];
            $stati[$STATOID]["COORDX"]=80*$i;
            $stati[$STATOID]["COORDY"]=$offsety+$deltah*$i;
            $stati[$STATOID]["PROGRESSIVO"]=$i;
            
            $stati[$STATOID]["BUX"]=80*$i+$w;
            $stati[$STATOID]["BUY"]=$offsety+$deltah*$i+10;
            $stati[$STATOID]["BDX"]=80*$i;
            $stati[$STATOID]["BDY"]=$offsety+$deltah*$i+$h-10;
            
            $stati[$STATOID]["TUX"]=80*$i+$w-10;
            $stati[$STATOID]["TUY"]=$offsety+$deltah*$i-2;
            $stati[$STATOID]["TDX"]=80*$i+10;
            $stati[$STATOID]["TDY"]=$offsety+$deltah*$i+$h;
            
            if($r[$i]["INIZIALE"]=="1" && $r[$i]["FINALE"]=="1")
                $stati[$STATOID]["BACKGROUND"]="#80E0E0";
            elseif($r[$i]["INIZIALE"]=="1")
                $stati[$STATOID]["BACKGROUND"]="#80E080";
            elseif($r[$i]["FINALE"]=="1")
                $stati[$STATOID]["BACKGROUND"]="#F08080";
            else
                $stati[$STATOID]["BACKGROUND"]="#F0F0F0";
        }
        $screenw=80*count($r)+$width;
        $screenh=$deltah*count($r)+$height;
        
        // LEGGO LE TRANSIZIONI
        $transizioni=array();
        maestro_query($maestro, "SELECT * FROM QW_TRANSIZIONI WHERE SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$PROCESSOID') AND BOWID<>'' AND TARGETID<>''", $r);
        for($i=0; $i<count($r); $i++){
            $TRANSID=$r[$i]["SYSID"];
            $transizioni[$TRANSID]["BOWID"]=$r[$i]["BOWID"];
            $transizioni[$TRANSID]["TARGETID"]=$r[$i]["TARGETID"];
            $transizioni[$TRANSID]["SVINCOLANTE"]=$r[$i]["SVINCOLANTE"];
            $transizioni[$TRANSID]["DESCRIPTION"]=$r[$i]["DESCRIPTION"];
        }
        // TRACCIO GLI STATI
        foreach($stati as $STATOID => $stato){
            $grafo.=
"<div 
class='winz-rounded'
style='position:absolute;
left:".($stato["COORDX"])."px;
top:".($stato["COORDY"])."px;
border:1px solid silver;
width:".$width."px;
height:".$height."px;
background-color:".($stato["BACKGROUND"]).";
margin:1px;
padding:1px;
overflow:hidden;
font-size:10px;'>".
$stato["DESCRIPTION"].
"</div>
";
        }
        // TRACCIO LE TRANSIZIONI
        $markerB=$PREFIX."_EndMarkerB";
        $markerG=$PREFIX."_EndMarkerG";
        $grafo.=
"<svg style='position:absolute;' version='1.1' width='$screenw' height='$screenh'>
<defs>
    <marker id='$markerB' viewBox='0 0 10 10' refX='7' refY='5' markerUnits='strokeWidth' markerWidth='3' markerHeight='3' stroke='blue' stroke-width='3' fill='none' orient='auto'>
        <path d = 'M 0 0 L 10 5 M 0 10 L 10 5'/>
    </marker>
    <marker id='$markerG' viewBox='0 0 10 10' refX='7' refY='5' markerUnits='strokeWidth' markerWidth='3' markerHeight='3' stroke='green' stroke-width='3' fill='none' orient='auto'>
        <path d = 'M 0 0 L 10 5 M 0 10 L 10 5'/>
    </marker>
</defs>
";
        foreach($transizioni as $TRANSID => $trans){
            $DESCRIPTION=str_replace("'", "´" ,$trans["DESCRIPTION"]);
            $BOWID=$trans["BOWID"];
            $TARGETID=$trans["TARGETID"];
            $bprogr=$stati[$BOWID]["PROGRESSIVO"];
            $tprogr=$stati[$TARGETID]["PROGRESSIVO"];
            if($bprogr<$tprogr){
                $bx=$stati[$BOWID]["BUX"];
                $by=$stati[$BOWID]["BUY"];
                $tx=$stati[$TARGETID]["TUX"];
                $ty=$stati[$TARGETID]["TUY"];
            }
            else{
                $bx=$stati[$BOWID]["BDX"];
                $by=$stati[$BOWID]["BDY"];
                $tx=$stati[$TARGETID]["TDX"];
                $ty=$stati[$TARGETID]["TDY"];
            }
            $dx=$bx+($tx-$bx)/2;
            $dy=$by-($ty-$by)/2;
            $SVINCOLANTE=$trans["SVINCOLANTE"];
            if($SVINCOLANTE){
                $stroke="green";
                $marker=$markerG;
            }
            else{
                $stroke="blue";
                $marker=$markerB;
            }
            $PATHID=$PREFIX."_".$TRANSID;
            $grafo.="<path d='M $bx $by S $dx $dy $tx $ty' fill='none' stroke='$stroke' stroke-width='2' marker-end='url(#$marker)' title='$DESCRIPTION' />";
        }

        $grafo.="</svg>\r\n";
        $babelparams["GRAFO"]=$grafo;
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