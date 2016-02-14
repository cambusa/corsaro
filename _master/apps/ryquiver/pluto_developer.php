<?php 
/****************************************************************************
* Name:            pluto_developer.php                                      *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
class ryDeveloper{
    public $maestro;
    public $plutoid;
    public $processoid;
    public $statoid;
    public $attoreid;
    public $contoid;
    public $controid;
    public $genreid;
    public $richiedenteid;
    public $segno;
    public $dividendo;
    public $divisore;
    public $dvdinc;
    public $dvsinc;
    public $dvdpag;
    public $dvspag;
    public $anticipati;
    public $lasterrnumber;
    public $lasterrdescription;
    public $trdate;
    public $parametri;
    public $sviluppo;
    public $swap;
    public $simulazione;
    
    public function ryDeveloper(){
        $this->maestro=false;
        $this->plutoid="";
        $this->processoid="";
        $this->statoid="";
        $this->attoreid="";
        $this->contoid="";
        $this->controid="";
        $this->genreid="";
        $this->richiedenteid="";
        $this->segno=1;
        $this->dividendo=365;
        $this->divisore=365;
        $this->dvdinc=365;
        $this->dvsinc=365;
        $this->dvdpag=365;
        $this->dvspag=365;
        $this->anticipati=false;
        $this->lasterrnumber=0;
        $this->lasterrdescription="";
        $this->trdate=array("-", ":", "T", " ", "'", ".");
        $this->parametri=array();
        $this->sviluppo=array();
        $this->swap=false;
        $this->simulazione=false;
    }
    public function datediff($d1, $d2){
        $d1=date_create(substr($d1,0,4)."-".substr($d1,4,2)."-".substr($d1,6,2));
        $d2=date_create(substr($d2,0,4)."-".substr($d2,4,2)."-".substr($d2,6,2));
        return ry_datediff($d1, $d2);
    }
    public function datediff365($d1, $d2){
        $d1=date_create(substr($d1,0,4)."-".substr($d1,4,2)."-".substr($d1,6,2));
        $d2=date_create(substr($d2,0,4)."-".substr($d2,4,2)."-".substr($d2,6,2));
        return ry_datediff365($d1, $d2);
    }
    public function datediff360($d1, $d2){
        $d1=date_create(substr($d1,0,4)."-".substr($d1,4,2)."-".substr($d1,6,2));
        $d2=date_create(substr($d2,0,4)."-".substr($d2,4,2)."-".substr($d2,6,2));
        return ry_datediff360($d1, $d2);
    }
    public function dateadd($d, $days){
        $d=date_create(substr($d,0,4)."-".substr($d,4,2)."-".substr($d,6,2));
        return date_format(ry_dateadd($d, $days), "Ymd");
    }
    public function normalizza($capzero=false){
        $aggr=array();
        // DETERMINO SE E' UNO SWAP
        $this->swap=false;
        foreach($this->sviluppo as $flusso){
            if(isset($flusso["CAPITALE"])){
                break;
            }
            elseif(isset($flusso["NOMINALE"])){
                $this->swap=true;
                break;
            }
        }
        if($this->swap)
            $fields=array("NOMINALE", "INTINC", "COMMINC", "TASSOINC", "SPREADINC", "INTPAG", "COMMPAG", "TASSOPAG", "SPREADPAG");
        else
            $fields=array("CAPITALE", "INTERESSI", "COMMISSIONI", "TASSO", "SPREAD");

        // AGGREGO PER DATA
        foreach($this->sviluppo as $flusso){
            if(isset($flusso["DATA"])){
                $CHIAVE=$flusso["DATA"];
                if(!isset($aggr[$CHIAVE])){
                    $aggr[$CHIAVE]=array();
                    $aggr[$CHIAVE]["DATA"]=$CHIAVE;
                    $aggr[$CHIAVE]["_POSIZIONE"]=1;
                    foreach($fields as $field){
                        $aggr[$CHIAVE][$field]=0;
                        $aggr[$CHIAVE]["_".$field]=false;
                        $aggr[$CHIAVE]["@".$field]="";
                    }
                }
                if($this->swap){
                    if(isset($flusso["NOMINALE"])){
                        $aggr[$CHIAVE]["NOMINALE"]=$flusso["NOMINALE"];
                        $aggr[$CHIAVE]["_NOMINALE"]=true;
                        if(isset($flusso["@NOMINALE"])){
                            $aggr[$CHIAVE]["@NOMINALE"]=$flusso["@NOMINALE"];
                        }
                    }
                    if(isset($flusso["INTINC"])){
                        $aggr[$CHIAVE]["INTINC"]+=$flusso["INTINC"];
                        $aggr[$CHIAVE]["_INTINC"]=true;
                        if(isset($flusso["@INTINC"])){
                            $aggr[$CHIAVE]["@INTINC"]=$flusso["@INTINC"];
                        }
                    }
                    if(isset($flusso["COMMINC"])){
                        $aggr[$CHIAVE]["COMMINC"]+=$flusso["COMMINC"];
                        $aggr[$CHIAVE]["_COMMINC"]=true;
                        if(isset($flusso["@COMMINC"])){
                            $aggr[$CHIAVE]["@COMMINC"]=$flusso["@COMMINC"];
                        }
                    }
                    if(isset($flusso["TASSOINC"])){
                        $aggr[$CHIAVE]["TASSOINC"]+=$flusso["TASSOINC"];
                        $aggr[$CHIAVE]["_TASSOINC"]=true;
                        if(isset($flusso["@TASSOINC"])){
                            $aggr[$CHIAVE]["@TASSOINC"]=$flusso["@TASSOINC"];
                        }
                    }
                    if(isset($flusso["SPREADINC"])){
                        $aggr[$CHIAVE]["SPREADINC"]+=$flusso["SPREADINC"];
                        $aggr[$CHIAVE]["_SPREADINC"]=true;
                        if(isset($flusso["@SPREADINC"])){
                            $aggr[$CHIAVE]["@SPREADINC"]=$flusso["@SPREADINC"];
                        }
                    }
                    if(isset($flusso["INTPAG"])){
                        $aggr[$CHIAVE]["INTPAG"]+=$flusso["INTPAG"];
                        $aggr[$CHIAVE]["_INTPAG"]=true;
                        if(isset($flusso["@INTPAG"])){
                            $aggr[$CHIAVE]["@INTPAG"]=$flusso["@INTPAG"];
                        }
                    }
                    if(isset($flusso["COMMPAG"])){
                        $aggr[$CHIAVE]["COMMPAG"]+=$flusso["COMMPAG"];
                        $aggr[$CHIAVE]["_COMMPAG"]=true;
                        if(isset($flusso["@COMMPAG"])){
                            $aggr[$CHIAVE]["@COMMPAG"]=$flusso["@COMMPAG"];
                        }
                    }
                    if(isset($flusso["TASSOPAG"])){
                        $aggr[$CHIAVE]["TASSOPAG"]+=$flusso["TASSOPAG"];
                        $aggr[$CHIAVE]["_TASSOPAG"]=true;
                        if(isset($flusso["@TASSOPAG"])){
                            $aggr[$CHIAVE]["@TASSOPAG"]=$flusso["@TASSOPAG"];
                        }
                    }
                    if(isset($flusso["SPREADPAG"])){
                        $aggr[$CHIAVE]["SPREADPAG"]+=$flusso["SPREADPAG"];
                        $aggr[$CHIAVE]["_SPREADPAG"]=true;
                        if(isset($flusso["@SPREADPAG"])){
                            $aggr[$CHIAVE]["@SPREADPAG"]=$flusso["@SPREADPAG"];
                        }
                    }
                }
                else{
                    if(isset($flusso["CAPITALE"])){
                        $aggr[$CHIAVE]["CAPITALE"]+=$flusso["CAPITALE"];
                        $aggr[$CHIAVE]["_CAPITALE"]=true;
                        if(isset($flusso["@CAPITALE"])){
                            $aggr[$CHIAVE]["@CAPITALE"]=$flusso["@CAPITALE"];
                        }
                    }
                    if(isset($flusso["INTERESSI"])){
                        $aggr[$CHIAVE]["INTERESSI"]+=$flusso["INTERESSI"];
                        $aggr[$CHIAVE]["_INTERESSI"]=true;
                        if(isset($flusso["@INTERESSI"])){
                            $aggr[$CHIAVE]["@INTERESSI"]=$flusso["@INTERESSI"];
                        }
                    }
                    if(isset($flusso["COMMISSIONI"])){
                        $aggr[$CHIAVE]["COMMISSIONI"]+=$flusso["COMMISSIONI"];
                        $aggr[$CHIAVE]["_COMMISSIONI"]=true;
                        if(isset($flusso["@COMMISSIONI"])){
                            $aggr[$CHIAVE]["@COMMISSIONI"]=$flusso["@COMMISSIONI"];
                        }
                    }
                    if(isset($flusso["TASSO"])){
                        $aggr[$CHIAVE]["TASSO"]+=$flusso["TASSO"];
                        $aggr[$CHIAVE]["_TASSO"]=true;
                        if(isset($flusso["@TASSO"])){
                            $aggr[$CHIAVE]["@TASSO"]=$flusso["@TASSO"];
                        }
                    }
                    if(isset($flusso["SPREAD"])){
                        $aggr[$CHIAVE]["SPREAD"]+=$flusso["SPREAD"];
                        $aggr[$CHIAVE]["_SPREAD"]=true;
                        if(isset($flusso["@SPREAD"])){
                            $aggr[$CHIAVE]["@SPREAD"]=$flusso["@SPREAD"];
                        }
                    }
                }
            }
        }
        // ORDINO PER DATA
        ksort($aggr);
        if(count($aggr)>0){
            $keys=array_keys($aggr);
            $aggr[ $keys[0]              ]["_POSIZIONE"]=0;
            $aggr[ $keys[count($keys)-1] ]["_POSIZIONE"]=2;
        }
        // GESTIONE CAPITALI A ZERO: SOSTITUIRLI CON IL CAPITALE RESIDUO
        if($capzero){
            if(!$this->swap){
                $CAPRES=0;
                foreach($aggr as $DATA => &$flusso){
                    if($flusso["_CAPITALE"]){
                        if(abs($flusso["CAPITALE"])>0.0001){
                            $CAPRES-=$flusso["CAPITALE"];
                        }
                        else{
                            $flusso["CAPITALE"]=$CAPRES;
                            $CAPRES=0;
                        }
                    }
                }
            }
        }
        // DETERMINAZIONE INTERESSI ANTICIPATI
        if(!$this->simulazione){
            reset($aggr);
            $primo=current($aggr);
            $this->anticipati=($primo["INTERESSI"]!=0 || $primo["INTINC"]!=0 || $primo["INTPAG"]!=0);
        }
        // TRAVASO COME SVILUPPO NORMALIZZATO
        $this->sviluppo=$aggr;
    }
    public function calcolainteressi(){
        if($this->swap)
            $this->calcolainteressiswap();
        else
            $this->calcolainteressifin();
    }
    public function calcolainteressifin(){
        $dataliq="";
        $tasso=0;
        $spread=0;
        $capres=0;
        $liq=0;
        foreach($this->sviluppo as &$flusso){
            $DATA=$flusso["DATA"];
            // INIZIALIZZO EVENTUALMENTE LE ULTIME DATE DI LIQUIDAZIONE INTERESSI
            if($dataliq==""){
                $dataliq=$DATA;
            }
            // CONTROLLO SE C'E' UN EVENTO
            if($flusso["_SPREAD"] || $flusso["_TASSO"] || $flusso["_INTERESSI"] || $flusso["_CAPITALE"]){
                // CALCOLO GLI INTERESSI MATURATI 
                if($this->dividendo==360)
                    $gg=$this->datediff360($dataliq, $DATA);
                else
                    $gg=$this->datediff365($dataliq, $DATA);
                if($gg>0){
                    $liq+=($gg*($tasso+$spread)*abs($capres)/$this->divisore/100);
                }
                if($flusso["_TASSO"]){
                    $tasso=$flusso["TASSO"];
                }
                if($flusso["_SPREAD"]){
                    $spread=$flusso["SPREAD"];
                }
                $dataliq=$DATA;
            }
            if($flusso["_INTERESSI"]){
                if($flusso["INTERESSI"]==0 || $flusso["_POSIZIONE"]==2){
                    $flusso["INTERESSI"]=$liq;
                }
                $liq=0;
                $dataliq=$DATA;
            }
            if($flusso["_CAPITALE"]){
                $capres-=$flusso["CAPITALE"];
            }
        }
        if($this->anticipati){
            $prevkey=false;
            $liq=0;
            foreach($this->sviluppo as $key => &$flusso){
                if($prevkey===false){
                    $prevkey=$key;
                }
                elseif($flusso["_INTERESSI"]){
                    $this->sviluppo[$prevkey]["INTERESSI"]=$flusso["INTERESSI"];
                    $flusso["INTERESSI"]=0;
                    $prevkey=$key;
                }
            }
        }
    }
    public function calcolainteressiswap(){
        $lastpag="";
        $lastinc="";
        $tassopag=0;
        $tassoinc=0;
        $spreadpag=0;
        $spreadinc=0;
        $capres=0;
        $liqpag=0;
        $liqinc=0;
        foreach($this->sviluppo as &$flusso){
            $DATA=$flusso["DATA"];
            // INIZIALIZZO EVENTUALMENTE LE ULTIME DATE DI LIQUIDAZIONE INTERESSI
            if($lastinc=="")
                $lastinc=$DATA;
            if($lastpag=="")
                $lastpag=$DATA;
            if($flusso["_NOMINALE"]){
                $capres+=$flusso["NOMINALE"];
            }
            // EVENTO TASSO INCASSATO
            if($flusso["_SPREADINC"] || $flusso["_TASSOINC"] || $flusso["_INTINC"]){
                // CALCOLO GLI INTERESSI MATURATI 
                if($this->dvdinc==360)
                    $gg=$this->datediff360($lastinc, $DATA);
                else
                    $gg=$this->datediff365($lastinc, $DATA);
                if($gg>0){
                    $liqinc+=($gg*($tassoinc+$spreadinc)*abs($capres)/$this->dvsinc/100);
                }
                if($flusso["_TASSOINC"]){
                    $tassoinc=$flusso["TASSOINC"];
                }
                if($flusso["_SPREADINC"]){
                    $spreadinc=$flusso["SPREADINC"];
                }
                $dataliq=$DATA;
            }
            if($flusso["_INTINC"]){
                if($flusso["INTINC"]==0 || $flusso["_POSIZIONE"]==2){
                    $flusso["INTINC"]=$liqinc;
                }
                $liqinc=0;
                $lastinc=$DATA;
            }
            // EVENTO TASSO PAGATO
            if($flusso["_SPREADPAG"] || $flusso["_TASSOPAG"] || $flusso["_INTPAG"]){
                // CALCOLO GLI INTERESSI MATURATI 
                if($this->dvdpag==360){
                    $gg=$this->datediff360($lastpag, $DATA);
                }
                else{
                    $gg=$this->datediff365($lastpag, $DATA);
                }
                if($gg>0){
                    $liqpag+=($gg*($tassopag+$spreadpag)*abs($capres)/$this->dvspag/100);
                }
                if($flusso["_TASSOPAG"]){
                    $tassopag=$flusso["TASSOPAG"];
                }
                if($flusso["_SPREADPAG"]){
                    $spreadpag=$flusso["SPREADPAG"];
                }
                $dataliq=$DATA;
            }
            if($flusso["_INTPAG"]){
                if($flusso["INTPAG"]==0 || $flusso["_POSIZIONE"]==2){
                    $flusso["INTPAG"]=$liqpag;
                }
                $liqpag=0;
                $lastpag=$DATA;
            }
        }
        if($this->anticipati){
            $prevkeyi=false;
            $liqi=0;
            $prevkeyp=false;
            $liqp=0;
            foreach($this->sviluppo as $key => &$flusso){
                if($prevkeyi===false){
                    $prevkeyi=$key;
                }
                elseif($flusso["_INTINC"]){
                    $this->sviluppo[$prevkeyi]["INTINC"]=$flusso["INTINC"];
                    $flusso["INTINC"]=0;
                    $prevkeyi=$key;
                }
                if($prevkeyp===false){
                    $prevkeyp=$key;
                }
                elseif($flusso["_INTPAG"]){
                    $this->sviluppo[$prevkeyp]["INTPAG"]=$flusso["INTPAG"];
                    $flusso["INTPAG"]=0;
                    $prevkeyp=$key;
                }
            }
        }
    }
    public function sviluppodate($INIZIO, $PROGRESSIONE, $USCITA, $lav=true){
        $date=array();
        if(is_string($USCITA)){
            // L'USCITA E' LA DATA FINALE
            $FINE=$USCITA;
            $flussi=99999;
        }
        else{
            // L'USCITA E' IL NUMERO DI FLUSSI
            $FINE="99991231";
            $flussi=$USCITA;
        }
        $date[]=$INIZIO;
        $prev=$INIZIO;
        $count=1;

        do{
            $y=intval(substr($prev, 0, 4));
            $m=intval(substr($prev, 4, 2));
            $d=intval(substr($prev, 6, 2));
            $finemese=(date("d", mktime(0,0,0, $m, $d+1, $y))=="1");
            switch($PROGRESSIONE){
            case "1D":
                $curr=date("Ymd", mktime(0,0,0, $m, $d+1, $y));
                break;
            case "1W":
                $curr=date("Ymd", mktime(0,0,0, $m, $d+7, $y));
                break;
            case "1M":
                if($finemese)
                    $curr=date("Ymd", mktime(0,0,0, $m+2, 0, $y));
                else
                    $curr=date("Ymd", mktime(0,0,0, $m+1, $d, $y));
                break;
            case "2M":
                if($finemese)
                    $curr=date("Ymd", mktime(0,0,0, $m+3, 0, $y));
                else
                    $curr=date("Ymd", mktime(0,0,0, $m+2, $d, $y));
                break;
            case "3M":
                if($finemese)
                    $curr=date("Ymd", mktime(0,0,0, $m+4, 0, $y));
                else
                    $curr=date("Ymd", mktime(0,0,0, $m+3, $d, $y));
                break;
            case "4M":
                if($finemese)
                    $curr=date("Ymd", mktime(0,0,0, $m+5, 0, $y));
                else
                    $curr=date("Ymd", mktime(0,0,0, $m+4, $d, $y));
                break;
            case "6M":
                if($finemese)
                    $curr=date("Ymd", mktime(0,0,0, $m+7, 0, $y));
                else
                    $curr=date("Ymd", mktime(0,0,0, $m+6, $d, $y));
                break;
            case "1Y":
                if($finemese)
                    $curr=date("Ymd", mktime(0,0,0, $m+1, 0, $y+1));
                else
                    $curr=date("Ymd", mktime(0,0,0, $m, $d, $y+1));
                break;
            }
            $date[]=$curr;
            $prev=$curr;
            $count+=1;
        }while($curr<$FINE && $count<$flussi);
        
        if($lav){
            // MI PONGO SUL PRIMO GIORNO LAVORATIVO
            foreach($date as &$s){
                $y=substr($s, 0, 4);
                $m=substr($s, 4, 2);
                $d=substr($s, 6, 2);
                if(!ry_businessday(intval($y), intval($m), intval($d))){
                    $s=ry_businessadd($s, 0);
                }
            }
        }
        return $date;
    }
    public function caricafin($PRATICAID){
        // DETERMINO SE E' UNO SWAP
        maestro_query($this->maestro, "SELECT SYSID FROM QW_MOVIMENTI WHERE SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$PRATICAID') AND CONSISTENCY=2", $r);
        
        $this->swap=(count($r)>0);
        $this->anticipati=false;

        // LEGGO LE FRECCE DEL FINANZIAMENTO E CARICO IL DEVELOPER
        maestro_query($this->maestro, "SELECT * FROM QVARROWS WHERE SYSID IN (SELECT ARROWID FROM QVQUIVERARROW WHERE QUIVERID='$PRATICAID')", $r);
        for($i=0; $i<count($r); $i++){
            $ARROWID=$r[$i]["SYSID"];
            $DATA=substr(str_replace($this->trdate, "", $r[$i]["AUXTIME"]), 0, 8);
            $AMOUNT=floatval($r[$i]["AMOUNT"]);
            $GENREID=substr($r[$i]["GENREID"], 0, 12);
            $MOTIVEID=substr($r[$i]["MOTIVEID"], 0, 12);
            $BOWID=$r[$i]["BOWID"];
            $TARGETID=$r[$i]["TARGETID"];
            $TIPOFLUSSO="";
            switch($MOTIVEID){
            case "0CAUSCAPPAG0":
                if($this->swap){
                    $AMOUNT=-round($AMOUNT, 2);
                    $TIPOFLUSSO="NOMINALE";
                }
                else{
                    $AMOUNT=-$this->segno*round($AMOUNT, 2);
                    $TIPOFLUSSO="CAPITALE";
                }
                break;
            case "0CAUSCAPINC0":
                if($this->swap){
                    $AMOUNT=round($AMOUNT, 2);
                    $TIPOFLUSSO="NOMINALE";
                }
                else{
                    $AMOUNT=$this->segno*round($AMOUNT, 2);
                    $TIPOFLUSSO="CAPITALE";
                }
                break;
            case "0CAUSINTPAG0":
                $AMOUNT=round($AMOUNT, 2);
                if($this->swap)
                    $TIPOFLUSSO="INTPAG";
                else
                    $TIPOFLUSSO="INTERESSI";
                break;
            case "0CAUSINTINC0":
                $AMOUNT=round($AMOUNT, 2);
                if($this->swap)
                    $TIPOFLUSSO="INTINC";
                else
                    $TIPOFLUSSO="INTERESSI";
                break;
            case "0CAUSCOMMPAG":
                $AMOUNT=round($AMOUNT, 2);
                if($this->swap)
                    $TIPOFLUSSO="COMMPAG";
                else
                    $TIPOFLUSSO="COMMISSIONI";
                break;
            case "0CAUSCOMMINC":
                $AMOUNT=round($AMOUNT, 2);
                if($this->swap)
                    $TIPOFLUSSO="COMMINC";
                else
                    $TIPOFLUSSO="COMMISSIONI";
                break;
            }
            switch($GENREID){
            case "0TASSOANNUO0":
                $AMOUNT=round($AMOUNT, 5);
                if($this->swap){
                    if($BOWID!="")
                        $TIPOFLUSSO="TASSOPAG";
                    else
                        $TIPOFLUSSO="TASSOINC";
                }
                else
                    $TIPOFLUSSO="TASSO";
                break;
            case "0SPREADANNUO":
                $AMOUNT=round($AMOUNT, 5);
                if($this->swap){
                    if($BOWID!="")
                        $TIPOFLUSSO="SPREADPAG";
                    else
                        $TIPOFLUSSO="SPREADINC";
                }
                else
                    $TIPOFLUSSO="SPREAD";
                break;
            }
            if($TIPOFLUSSO!=""){
                $this->sviluppo[]=array("DATA" => $DATA, $TIPOFLUSSO => $AMOUNT, "@".$TIPOFLUSSO => $ARROWID);
            }
        }
        
        // GENERO IL PROSPETTO
        $this->normalizza();
    }
}
?>