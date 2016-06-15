<?php
/****************************************************************************
* Name:            ryvlad.php                                               *
* Project:         Cambusa/ryVlad                                           *
* Version:         1.69                                                     *
* Description:     Vampire Locates and Acquires Data                        *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
include_once $tocambusa."rygeneral/json_loader.php";
include_once $tocambusa."rygeneral/writelog.php";
$VLAD=new ryVlad();
$BLOOD=new ryBlood();
class ryVlad{
    private $config;
    private $remaining;
    private $loadedbytes;
    private $totalbytes;
    private $adjustment;
    private $endrow;
    private $livellostack;
    private $lasterrnumber;
    private $lasterrdescription;
    private $trace;
    private $buffer;
    
    // Funzionerebbe solo da PHP 5.3
    //public function __construct(){
    public function ryVlad(){
        $this->config="";
        $this->remaining=0;
        $this->loadedbytes=0;
        $this->totalbytes=0;
        $this->adjustment="";
        $this->endrow="";
        $this->livellostack=0;
        $this->lasterrnumber=0;
        $this->lasterrdescription="";
        $this->trace="";
        $this->buffer="";
    }
    
    // Funzionerebbe solo da PHP 5.3
    //public function __invoke($json){
    public function config($json){
        try{
            if(is_string($json)){
                if(substr(trim($json),0,1)!="{")
                    $json=file_get_contents($json);
                $json=json_decode($json);
                if($json==null){
                    $this->lasterrnumber=1;
                    $this->lasterrdescription="Wrong format";
                }
            }
            if($this->config=="")
                $this->config=$json;
            else
                $this->config=object_merge_recursive($this->config, $json);
        }
        catch (Exception $e) {
            $this->lasterrnumber=1;
            $this->lasterrdescription=$e->getMessage();
        }            
    }
    public function bleed(){ 
        try{
            $this->lasterrnumber=0;
            $this->lasterrdescription="";
            $this->buffer="";
            $json=$this->config;
            if(isset($json->source)){
                $source=$json->source;
                if(is_file($source)){
                    $this->remaining=filesize($source);
                    $this->loadedbytes=0;
                    $this->totalbytes=$this->remaining;
                    $this->adjustment="";
                    $this->endrow="";
                    $this->livellostack=0;
                    
                    if(isset($json->root)){
                        // APERTURA FILE DI LOG
                        if(isset($json->trace)){
                            $this->trace=$json->trace;
                            if($this->trace!=""){
                                log_open($this->trace);
                            }
                        }
                        
                        // CONDIZIONI DI CARICAMENTO TOTALE
                        $blocksize=0;
                        if(isset($livello->match)){
                            if($livello->match=="")
                                $blocksize=$this->remaining;
                        }
                        else
                            $blocksize=$this->remaining;
                            
                        $fp=fopen($source,'rb');
                        $this->loadchunk($fp,$blocksize);
                        $this->analyzelevel($fp,"root",$json->root,"",true);
                        fclose($fp);

                        // CHIUSURA FILE DI LOG
                        if($this->trace!="")
                            log_close();
                    }
                    else{
                        $this->lasterrnumber=1;
                        $this->lasterrdescription="Level 'root' doesn't exist";
                    }
                }
                else{
                    $this->lasterrnumber=1;
                    $this->lasterrdescription="Source $source doesn't exist";
                }
            }
            else{
                $this->lasterrnumber=1;
                $this->lasterrdescription="Source undefined";
            }
            
        }
        catch (Exception $e) {
            $this->lasterrnumber=1;
            $this->lasterrdescription=$e->getMessage();
        }            
    }
    private function loadchunk($fp, $blocksize){
        if($blocksize>0){
            $pezzo=$blocksize;
        }
        else{
            if($this->remaining<1000000)
                $pezzo=$this->remaining;
            else
                $pezzo=1000000;
        }
        $buff=fread($fp,$pezzo);
        $this->loadedbytes+=strlen($buff);
        $this->remaining=$this->remaining-strlen($buff);
     
        if($this->adjustment!=""){
            $buff=$this->adjustment.$buff;
            $this->adjustment="";
        }
        if($this->endrow==""){
            if(strpos($buff,"\r\n")!==false)
                $this->endrow="\r\n";
            elseif(strpos($buff,"\n")!==false)
                $this->endrow="\n";
            else
                $this->endrow="\r";
        }
        if($this->remaining>0){
            $poscrlf=strpos($buff,$this->endrow);
            if($poscrlf!==false){
                $this->adjustment=substr($buff, $poscrlf+strlen($this->endrow));
                $buff=substr($buff,0,$poscrlf+strlen($this->endrow)-1);
            }
        }
        if($this->remaining==0){
            if(substr($buff,-2)!="\r\n"){
                $buff.="\r\n";
            }
        }
        $this->buffer.=$buff;
    }
    private function analyzelevel($fp,$livname,$livello,$strcurr,$flagroot){
        global $BLOOD;
        if($this->livellostack>100){
            return false;
        }
        $this->livellostack+=1;
        $prevlen=-1;
        if($flagroot){
            $strcurr=$this->buffer;
        }
        // INIZIALIZZO METHOD
        if(!isset($livello->method))
            $livello->method="SET";
            
        // INIZIALIZZO RESET
        if(!isset($livello->reset))
            $livello->reset=0;
            
        // INIZIALIZZO CLEAR
        if(!isset($livello->clear))
            $livello->clear=0;
        
        // INIZIALIZZO INDEXES
        if(!isset($livello->indexes))
            $livello->indexes="";
            
        // INIZALIZZO MATCHTYPE
        if(!isset($livello->matchtype))   // il pattern individua: "A" tutto il blocco, "B" l'inizio fino alla prossima occorrenza o EOF
            $livello->matchtype="A";
            
        // INIZIALIZZO CSV
        if(!isset($livello->csv))
            $livello->csv=0;
        
        // INIZIALIZZO INVERTED
        if(!isset($livello->inverted))
            $livello->inverted=0;
        
        $conflict=false;
        while(true){
            if(($strcurr=="" && $flagroot==false) || ($this->remaining==0 && $prevlen==strlen($strcurr)) ){
                break;
            }
            if($flagroot){
               if($strcurr==""){
                    if($this->remaining>0){
                        $this->buffer=$strcurr;
                        $this->loadchunk($fp,0);
                        $strcurr=$this->buffer;
                    }
                    else{
                         break;
                    }
               }
            }
            if($this->remaining==0)
                $prevlen=strlen($strcurr);
            
            // ESISTENZA DEL PATTERN DI RICERCA
            $matchexists=false;
            if(isset($livello->match)){
                if($livello->match!=""){
                    $matchexists=true;
                }
            }
          
            if($matchexists){
                $maxtrovati=false;
                $strlivello="";
                if($livello->matchtype=="A")
                    $maxtrovati=preg_match_all("/".$livello->match."/mi", $strcurr, $matchcurr, PREG_OFFSET_CAPTURE);
                else
                    $maxtrovati=preg_match("/".$livello->match."/mi", $strcurr, $matchcurr, PREG_OFFSET_CAPTURE);
                if($maxtrovati!==false){
                    if($maxtrovati>0){
                        if($livello->matchtype=="A"){
                            $strlivello=$matchcurr[0][0][0];
                            $fine=$matchcurr[0][$maxtrovati-1][1]+strlen($matchcurr[0][$maxtrovati-1][0]);
                            $strcurr=substr($strcurr,$fine);
                            $trovato=true;
                        }
                        else{
                            // DETERMINO L'INIZIO E LUNGHEZZA DELL'OCCORRENZA
                            $inizio=$matchcurr[0][1];
                            $lenbegin=strlen($matchcurr[0][0]);
                            // ESTRAGGO L'INIZIO BLOCCO
                            $strcurr=substr($strcurr,$inizio);
                            // CERCO LA PROSSIMA OCCORRENZA...
                            if(strlen($strcurr)>$lenbegin){
                                // ...DALLA FINE DELL'INIZIO DEL BLOCCO
                                $nexttrovati=preg_match($livello->match, substr($strcurr, $lenbegin), $matchsucc, PREG_OFFSET_CAPTURE);
                            }
                            else{
                                // ...SIMULANDO UNA RICERCA POICHE' NON C'E' UN DOPO
                                $nexttrovati=preg_match($livello->match, "", $matchsucc, PREG_OFFSET_CAPTURE);
                            }
                            if($nexttrovati>0 || ($flagroot==true && $this->remaining==0) || $flagroot==false){
                                // HO TROVATO UNA PROSSIMA OCCORRENZA
                                // OPPURE SONO SUL LIVELLO RADICE E TUTTO IL FILE E' STATO CARICATO
                                // OPPURE SONO SU UN SOTTOLIVELLO
                                if($nexttrovati>0){
                                    $fine=$matchsucc[0][1]+$lenbegin;      // INIZIO DEL SUCCESSIVO + LUNGHEZZA DELL'OCCORRENZA (BLOCCO DI INIZIO)
                                    $strlivello=substr($strcurr,0,$fine-1);
                                    $strcurr=substr($strcurr, $fine);
                                }
                                else{
                                    $strlivello=$strcurr;
                                    $strcurr="";
                                }
                                $trovato=true;
                            }
                            else{
                                $trovato=false;
                            }
                            unset($matchsucc);
                        }
                    }
                    else{
                        $trovato=false;
                    }
                }
                else{
                    $trovato=false;
                    $this->lasterrnumber=1;
                    $this->lasterrdescription="Wrong pattern";
                }
                if($trovato==true && $livello->inverted==0){
                    for($t=0;$t<$maxtrovati;$t++){
                        // PER CIASCUNA OCCORRENZA DETERMINO IL BLOCCO E IL RESIDUO
                        if($t>0){
                            $strlivello=$matchcurr[0][$t][0];
                        }
                        if($t==$maxtrovati-1){
                            if(substr($strlivello,-1)=="\r" && substr($strcurr,0,1)=="\n"){
                                $strlivello.="\n";
                                $strcurr=substr($strcurr,1);
                            }
                        }
                        if($livello->csv){
                            // TRASFORMAZIONE CSV
                            $strlivello=$this->solvecsv($strlivello);
                        }
                        // ANALIZZO I DATI DA ESTRARRE
                        $match=array();
                        if(isset($livello->attributes)){
                            foreach($livello->attributes as $name => $params){
                                if(isset($params->remove))
                                    $remove=intval($params->remove);
                                else
                                    $remove=0;
                                // SORGENTE DATO
                                if(isset($params->index))
                                    $index=$params->index;
                                else
                                    $index=0;
                                // DESTINAZIONE DATO
                                if(!isset($params->target))
                                    $params->target=$name;
                                // DETERMINAZIONE VALORE STRINGA
                                $attrval="";
                                if($index>0){
                                    if($livello->matchtype=="A")
                                        @$attrval=$matchcurr[$index][$t][0];
                                    else
                                        @$attrval=$matchcurr[$index][0];
                                }
                                elseif(isset($params->default))
                                    @$attrval=$params->default;
                                // CONVERSIONE
                                if(isset($params->type))
                                    $typevalue=$params->type;
                                else
                                    $typevalue="S";
                                if($typevalue=="N"){
                                    // SEPARATORE DEI DECIMALI
                                    if(isset($params->sepdec))
                                        $sepdec=$params->sepdec;
                                    else
                                        $sepdec=".";
                                    // SIMBOLO SEGNO MENO
                                    if(isset($params->minus))
                                        $minus=$params->minus;
                                    else
                                        $minus="-";
                                    $attrval=$this->getnumber($attrval,$sepdec,$minus);
                                }
                                elseif($typevalue=="D"){
                                    // FORMATO
                                    if(isset($params->format))
                                        $format=$params->format;
                                    else
                                        $format=".";
                                    $attrval=$this->getdate($attrval,$format);
                                    if(isset($params->output)){
                                        $attrval=date($params->output, $attrval);
                                    }
                                }
                                // CARICAMENTO
                                if($remove)
                                    @$match[$params->target]=null;
                                else
                                    @$match[$params->target]=$attrval;
                                
                                // ESISTENZA DI UNA GESTIONE CONCORRENZE
                                if(isset($params->conflict))
                                    $conflict=true;
                            }
                        }
                        // SCRITTURA FILE DI LOG
                        if($this->trace!=""){
                            log_write(substr($livname.str_repeat(" ",16),0,16).str_repeat(" ",4*$this->livellostack));
                            log_write(serialize($match));
                            log_write("\r\n");
                        }

                        // DETERMINO L'ARRAY DEI SOTTOINDICI
                        if($livello->indexes!="")
                            $subindexes=explode(",", $livello->indexes);
                        else    
                            $subindexes=array();
                        $prev=array();
                        for($s=0; $s<count($subindexes); $s++){
                            if($subindexes[$s]=="#"){
                                $subindexes[$s]=$BLOOD->count($prev);
                            }
                            $prev[$s]=$subindexes[$s];
                        }

                        // RESETTO BLOOD
                        if($livello->reset){
                            $BLOOD->reset($subindexes);
                        }
                        elseif($livello->clear){
                            $BLOOD->clear();
                        }
                        
                        // CHIAMO LA CALLBACK DI INIZIALIZZAZIONE "DATI DEL LIVELLO" COMPLETI
                        $call=$livname."_prepare";
                        if(is_callable($call)){
                            $call($match);
                        }
                        
                        // CARICO BLOOD
                        switch($livello->method){
                            case "SET":
                                if($conflict){
                                    // ESISTONO CAMPI PER CUI SI DEVE GESTIRE LA CONCORRENZA
                                    foreach($livello->attributes as $name => $params){
                                        if(isset($params->conflict)){
                                            $BLOOD->conflict=$params->conflict;
                                            $BLOOD->set($subindexes, array( $params->target => $match[$params->target] ));
                                            $BLOOD->conflict="";
                                        }
                                        else{
                                            $BLOOD->set($subindexes, array( $params->target => $match[$params->target] ));
                                        }
                                    }
                                }
                                else{
                                    $BLOOD->set($subindexes, $match);
                                }
                                break;
                            case "PUSH":
                                $BLOOD->push($subindexes, $match);
                                break;
                        }
                        
                        // ANALIZZO I SOTTOLIVELLI
                        $this->analyzesublevels($fp, $livello, $strlivello);
                        
                        // CHIAMO LA CALLBACK DI "DATI DEL LIVELLO" COMPLETI
                        $call=$livname."_complete";
                        if(is_callable($call)){
                            $call($match);
                        }
                        unset($match);
                        unset($subindexes);
                    }
                }
                else{
                    if($flagroot){
                        if($this->remaining>0){
                            $this->buffer=$strcurr;
                            $this->loadchunk($fp,0);
                            $strcurr=$this->buffer;
                        }
                        else{
                            break;
                        }
                    }
                    else{
                        if(substr($strlivello,-1)=="\r" && substr($strcurr,0,1)=="\n"){
                            $strlivello.="\n";
                            $strcurr=substr($strcurr,1);
                        }
                        // GESTISCO IL FLAG INVERTED
                        if($livello->inverted){
                            // ANALIZZO I SOTTOLIVELLI
                            $this->analyzesublevels($fp, $livello, $strcurr);
                        }
                        break;
                    }
                }
                unset($match);
            }
            else{
                // MATCH NON DEFINITO: ANALIZZO LO STESSO BLOCCO CON I SOTTOLIVELLI
                $this->analyzesublevels($fp,$livello,$strcurr);
            }
        }
        if($flagroot){
            $this->buffer=$strcurr;
        }
        $this->livellostack-=1;
    }
    private function analyzesublevels($fp, $livello, $strlivello){
        // ANALISI SOTTOLIVELLI
        if(isset($livello->levels)){
            foreach($livello->levels as $key => $value){
                if(isset($value->enabled))
                    $enabled=$value->enabled;
                else
                    $enabled=true;
                if($enabled){
                    $this->analyzelevel($fp,$key,$value,$strlivello,false);
                }
            }
        }
    }
    private function solvecsv($buff){
        // Sostituisco le barre / con /0
        $buff=preg_replace('/\//', '/0', $buff);
        
        // Sostituisco 3 doppi apici con "/1 (/1 codifica 2 doppi apici)
        $buff=preg_replace('/"""/', '"/1', $buff);
        
        // Sostituisco "" apici con "/1
        $buff=preg_replace('/""/', '/1', $buff);

        // Sostituisco - con /2 (il meno lo voglio usare al posto delle virgolette " finali)
        $buff=preg_replace('/-/', '/2', $buff);
        
        // Sostituisco # con § (# viene assunto come futuro separatore non ambiguo)
        $buff=preg_replace('/#/', '§', $buff);
        
        // Sostituisco le eventuali doppie virgolette agli estremi dei campi
        // La " all'inizio con /3 e l'ultima con -
        $buff=preg_replace('/"([^"]+)"/', '/3$1-', $buff);
        
        // Sostituisco ; usata propriamente (non come separatore CSV) con /4
        while(preg_match('/\/3([^;-]*);([^-]*)-/', $buff)){
            $buff=preg_replace('/\/3([^;-]*);([^-]*)-/', '/3$1/4$2-', $buff);
        }
        
        // Cambio il separatore CSV ; con il meno probabile #
        $buff=preg_replace('/;/', '#', $buff);

        // Tolgo /3 che derivano dalle virgolette " iniziali
        $buff=preg_replace('/\/3/', '', $buff);

        // Tolgo - che derivano dalle virgolette " finali
        $buff=preg_replace('/-/', '', $buff);

        // Ripristino le ; proprie non usate come separatori
        $buff=preg_replace('/\/4/', ';', $buff);

        // Ripristino il trattino -
        $buff=preg_replace('/\/2/', '-', $buff);
        
        // Ripristino le virgolette " effettive nei valori di campo
        $buff=preg_replace('/\/1/', '"', $buff);

        // Ripristino la barra
        $buff=preg_replace('/\/0/', '/', $buff);
        
        // Elimino i caratteri di accapo
        $buff=preg_replace('/\n/', '', $buff);
        $buff=preg_replace('/\r/', '', $buff);
        
        return $buff;
    }
    public function getnumber(){            // number [, decsep [, minuschar]]
        try{
            $args=func_get_args();
            $c=func_num_args();
            switch($c){
                case 0: 
                    $val="0";
                    $sep=".";
                    $minus="-";
                    break;
                case 1: 
                    $val=$args[0];
                    $sep=".";
                    $minus="-";
                    break;
                case 2: 
                    $val=$args[0];
                    $sep=$args[1];
                    $minus="-";
                    break;
                default: 
                    $val=$args[0];
                    $sep=$args[1];
                    $minus=$args[2];
                    break;
            }
            $neg=(strpos($val,$minus)!==false);
            $val=preg_replace("/[^0-9".$sep."]/","",$val);
            if($sep!="."){
                $val=str_replace($sep,".",$val);
            }
            $val=floatval($val);
            if($neg){
                $val=-$val;
            }
            return $val;
        }
        catch (Exception $e){
            return false;
        }
    }
    public function getdate(){              // date [, format]
        try{
            $args=func_get_args();
            $c=func_num_args();
            switch($c){
                case 0: 
                    $val="";
                    $form="Ymd";
                    break;
                case 1: 
                    $val=trim($args[0]);
                    $form="Ymd";
                    break;
                default: 
                    $val=trim($args[0]);
                    $form=$args[1];
                    break;
            }
            // Funzionerebbe solo da PHP 5.3
            //$d=date_parse_from_format($form,$val);
            $d=array();
            $len=strlen($val);
            switch($form){
                case "YMD":
                    if($len>=8){
                        $d["year"]=substr($val,0,4);
                        $d["month"]=substr($val,4,2);
                        $d["day"]=substr($val,6,2);
                    }
                    elseif($len>=6){
                        $d["year"]=substr($val,0,2);
                        $d["month"]=substr($val,2,2);
                        $d["day"]=substr($val,4,2);
                    }
                    else{
                        $d=false;
                    }
                    break;
                case "DMY":
                    if($len>=8){
                        $d["year"]=substr($val,4,4);
                        $d["month"]=substr($val,2,2);
                        $d["day"]=substr($val,0,2);
                    }
                    elseif($len>=6){
                        $d["year"]=substr($val,4,2);
                        $d["month"]=substr($val,2,2);
                        $d["day"]=substr($val,0,2);
                    }
                    else{
                        $d=false;
                    }
                    break;
                case "MDY":
                    if($len>=8){
                        $d["year"]=substr($val,4,4);
                        $d["month"]=substr($val,0,2);
                        $d["day"]=substr($val,2,2);
                    }
                    elseif($len>=6){
                        $d["year"]=substr($val,4,2);
                        $d["month"]=substr($val,0,2);
                        $d["day"]=substr($val,2,2);
                    }
                    else{
                        $d=false;
                    }
                    break;
                case "Y#M#D":
                    if(preg_match("/[^0-9]*(\d+)[^0-9](\d+)[^0-9](\d+)/",$val,$m)){
                        $d["year"]=$m[1];
                        $d["month"]=$m[2];
                        $d["day"]=$m[3];
                    }
                    else{
                        $d=false;
                    }
                    break;
                case "D#M#Y":
                    if(preg_match("/[^0-9]*(\d+)[^0-9](\d+)[^0-9](\d+)/",$val,$m)){
                        $d["year"]=$m[3];
                        $d["month"]=$m[2];
                        $d["day"]=$m[1];
                    }
                    else{
                        $d=false;
                    }
                    break;
                case "M#D#Y":
                    if(preg_match("/[^0-9]*(\d+)[^0-9](\d+)[^0-9](\d+)/",$val,$m)){
                        $d["year"]=$m[3];
                        $d["month"]=$m[1];
                        $d["day"]=$m[2];
                    }
                    else{
                        $d=false;
                    }
                    break;
            }
            
            if($d!==false){
                if(strval($d["year"])==2){
                    if($d["year"]>="70")
                        $d["year"]="19".$d["year"];
                    else
                        $d["year"]="20".$d["year"];
                }
                $val=mktime(0,0,0,intval($d["month"]),intval($d["day"]),intval($d["year"]));
            }
            else{
                $val=false;
            }
            return $val;
        }
        catch (Exception $e){
            return false;
        }
    }
}

class ryBlood{
    public $default;
    public $conflict="";
    private $repository;
    
    // Funzionerebbe solo da PHP 5.3
    //public function __construct(){
    public function ryBlood(){
        $this->repository=array();
        $this->repository["__COUNTER"]=0;
        $this->default="";
    }
    // Funzionerebbe solo da PHP 5.3
    //public function __invoke(){
    public function data(){
        try{
            $args=func_get_args();
            $c=func_num_args();
            $rep=$this->repository;
            for($i=0;$i<$c;$i++){
                $j=$args[$i];
                if(isset($rep[$j])){
                    $rep=$rep[$j];
                }
                else{
                    $rep=$this->default;
                    break;
                }
            }
            return $rep;
        }
        catch (Exception $e){
            return $this->default;
        }
    }
    // Il metodo "set" carica le variabili di un array associativo (passato come ultimo parametro)
    // in una posizione gerarchica determinata da indici (tutti gli altri parametri fino al penultimo)
    // la variabile magica __COUNTER conta gli elementi di ciascun nodo
    public function set(){
        try{
            $args=func_get_args();
            $c=func_num_args();
            if($c>0){
                $dati=$args[$c-1];
                if($c==2){
                    // Se il primo parametro è un array lo considero
                    // come l'elenco di indici
                    if(is_array($args[0])){
                        $args=$args[0];
                        $c=count($args)+1;
                    }
                }
                $rep=&$this->repository;
                for($i=0;$i<$c-1;$i++){
                    $j=$args[$i];
                    if(!isset($rep[$j])){
                        $rep[$j]=array();
                        $rep[$j]["__COUNTER"]=0;
                    }
                    if(gettype($j)=="integer"){
                        $counter=$rep["__COUNTER"];
                        if($counter<$j)
                            $rep["__COUNTER"]=$j;
                    }
                    $rep=&$rep[$j];
                }
                foreach($dati as $key => $value){
                    if($this->conflict==""){
                        // IN CASO DI CONCORRENZA SOVRASCRIVO
                        if($value!==null)
                            $rep[$key]=$value;
                        else
                            unset($rep[$key]);
                    }
                    else{
                        if(isset($rep[$key])){
                            // IN CASO DI CONCORRENZA UTILIZZO LA FORMULA
                            $rep[$key]=$this->solveconflict($this->conflict,$rep[$key],$value);
                        }
                        else{
                            $rep[$key]=$value;
                        }
                    }
                }
            }
        }
        catch (Exception $e){
            return "";
        }
    }
    // Il metodo "push" carica le variabili di un array associativo (passato come ultimo parametro)
    // in una posizione gerarchica determinata da indici (tutti gli altri parametri fino al penultimo)
    // ma anche da un ulteriore indice autoincrementato per cui i valori vengono "appesi"
    // come dettagli
    public function push(){
        try{
            $args=func_get_args();
            $c=func_num_args();
            if($c>0){
                $dati=$args[$c-1];
                if($c==2){
                    // Se il primo parametro è un array lo considero
                    // come l'elenco di indici
                    if(is_array($args[0])){
                        $args=$args[0];
                        $c=count($args)+1;
                    }
                }
                $rep=&$this->repository;
                for($i=0;$i<$c-1;$i++){
                    $j=$args[$i];
                    if(!isset($rep[$j])){
                        $rep[$j]=array();
                        $rep[$j]["__COUNTER"]=0;
                    }
                    if(gettype($j)=="integer"){
                        $counter=$rep["__COUNTER"];
                        if($counter<$j)
                            $rep["__COUNTER"]=$j;
                    }
                    $rep=&$rep[$j];
                }
                $i=$rep["__COUNTER"]+1;
                $rep["__COUNTER"]=$i;
                $rep[$i]=array();
                $rep[$i]["__COUNTER"]=0;
                $rep=&$rep[$i];
                foreach($dati as $key => $value){
                    $rep[$key]=$value;
                }
            }
        }
        catch (Exception $e){}
    }
    public function count(){
        try{
            $args=func_get_args();
            $c=func_num_args();
            if($c==1){
                // Se il primo parametro è un array lo considero
                // come l'elenco di indici
                if(is_array($args[0])){
                    $args=$args[0];
                    $c=count($args);
                }
            }
            $rep=&$this->repository;
            for($i=0;$i<$c;$i++){
                $rep=&$rep[$args[$i]];
            }
            return $rep["__COUNTER"];
        }
        catch (Exception $e){
            return 0;
        }
    }
    public function clear(){
        unset($this->repository);
        $this->repository=array();
        $this->repository["__COUNTER"]=0;
    }
    public function reset(){
        try{
            $args=func_get_args();
            $c=func_num_args();
            if($c==1){
                // Se il primo parametro è un array lo considero
                // come l'elenco di indici
                if(is_array($args[0])){
                    $args=$args[0];
                    $c=count($args);
                }
            }
            $rep=&$this->repository;
            for($i=0;$i<$c;$i++){
                $rep=&$rep[$args[$i]];
            }
            $counter=$rep["__COUNTER"];
            for($i=1;$i<=$counter;$i++){
                unset($rep[$i]);
            }
            $rep["__COUNTER"]=0;
        }
        catch (Exception $e){}
    }
    public function remove(){
        try{
            $args=func_get_args();
            $c=func_num_args();
            if($c>0){
                $datum=$args[$c-1];
                if($c==2){
                    // Se il primo parametro è un array lo considero
                    // come l'elenco di indici
                    if(is_array($args[0])){
                        $args=$args[0];
                        $c=count($args);
                    }
                }
                $rep=&$this->repository;
                for($i=0;$i<$c;$i++){
                    $rep=&$rep[$args[$i]];
                }
                unset($rep[$datum]);
            }
        }
        catch (Exception $e){}
    }
    public function solveconflict($formula, $old, $new){
        try{
            $formula=str_replace("OLD","(\$old)",$formula);
            $formula=str_replace("NEW","(\$new)",$formula);
            $formula="return ".$formula.";";
            $value=eval($formula);
            if(is_string($value))
                $value=trim($value);
            return $value;
        }
        catch (Exception $e){
            return 1000;
        }
    }
}
?>