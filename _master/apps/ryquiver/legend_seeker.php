<?php 
/****************************************************************************
* Name:            legend_seeker.php                                        *
* Project:         Corsaro/ryQuiver Extension                               *
* Version:         1.69                                                     *
* Description:     Arrows-oriented Library                                  *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
define("SEEKER_STOP", 0);
define("SEEKER_INCLUDE", 1);
define("SEEKER_CONTINUE", 2);
class rySeeker{
    public $bags;
    public $bagnames;
    public $indexes;
    public $business;
    public $maestro;
    public $legendid;
    public $processoid;
    public $tolerance;
    public $statoid;
    public $contoid;
    public $genreid;
    public $praticaid;
    public $gaugeid;
    public $lasterrnumber;
    public $lasterrdescription;
    public $progressenabled;
    public $progressblock;
    public $trdate;
    
    public function rySeeker(){
        $this->bags=array();
        $this->bagnames=array();
        $this->indexes=array();
        $this->business=array();
        $this->maestro=false;
        $this->legendid="";
        $this->processoid="";
        $this->tolerance=0.0001;
        $this->statoid="";
        $this->contoid="";
        $this->genreid="";
        $this->praticaid="";
        $this->gaugeid="";
        $this->lasterrnumber=0;
        $this->lasterrdescription="";
        $this->progressenabled=0;
        $this->progressblock=1000;
        $this->trdate=array("-", ":", "T", " ", "'", ".");
    }
    public function dataload($NAME=""){
        // CARICO I RECORD
        foreach($this->bags as $BAGNAME => &$BAG){
            if($NAME=="" || $BAGNAME==$NAME){
                $VIEW=$BAG["VIEW"];
                $SELECT=$BAG["SELECT"];
                $arrows=&$BAG["ARROWS"];
                if(count($arrows)>0){
                    legend_appendselect($SELECT, "SYSID");
                    legend_appendselect($SELECT, "AMOUNT");
                    legend_appendselect($SELECT, "BOWID");
                    legend_appendselect($SELECT, "BOWTIME");
                    legend_appendselect($SELECT, "TARGETTIME");
                    legend_appendselect($SELECT, "AUXTIME");

                    $c=array_chunk($arrows, 1000);
                    foreach($c as $v){
                        $selection="'".implode("','", $v)."'";
                        $sql="SELECT $SELECT FROM $VIEW WHERE SYSID IN (".$selection.")";
                        $res=maestro_unbuffered($this->maestro, $sql);
                        while( $row=maestro_fetch($this->maestro, $res) ){
                            $INDEX=$this->indexes[ $row["SYSID"] ];
                            $BAG["DATA"][$INDEX]=$row;
                            $record=&$BAG["DATA"][ $INDEX ];
                            // CONVERTO AMOUNT A FLOAT
                            $record["AMOUNT"]=floatval($record["AMOUNT"]);
                            $record["BOWTIME"]=substr(str_replace($this->trdate, "", $record["BOWTIME"]), 0, 8);
                            $record["TARGETTIME"]=substr(str_replace($this->trdate, "", $record["TARGETTIME"]), 0, 8);
                            $record["AUXTIME"]=substr(str_replace($this->trdate, "", $record["AUXTIME"]), 0, 8);
                            $record["REGDATE"]=$record["AUXTIME"];
                            // IMPOSTO IL SEGNO NEGATIVO PER LE FRECCE IN USCITA
                            // DAL CONTO DI RIFERIMENTO
                            if($record["BOWID"]==$this->contoid){
                                $record["AMOUNT"]=-$record["AMOUNT"];
                                $record["TRASFDATE"]=$record["BOWTIME"];
                            }
                            else{
                                $record["TRASFDATE"]=$record["TARGETTIME"];
                            }
                        }
                        maestro_free($this->maestro, $res);
                    }
                }
            }
        }
    }
    
    public function clusterize($arrows, $CLUSTERID=""){
        try{
            if($CLUSTERID==""){
                $CLUSTERID=qv_createsysid($this->maestro);
            }
            if(!is_array($arrows)){
                $arrows=array($arrows);
            }
            foreach($arrows as $ARROWID){
                // RISOLVO IL BAGNAME
                $bagname=$this->bagnames[$ARROWID];
                // BLOCCO LA FRECCIA
                $this->business[$ARROWID]=true;
                // RISOLVO LA TABELLA ESTESA E LA AGGIORNO
                $TABLE=$this->bags[$bagname]["TABLE"];
                $sql="UPDATE $TABLE SET CLUSTERID='$CLUSTERID' WHERE SYSID='$ARROWID'";
                maestro_execute($this->maestro, $sql, false);
            }
            return $CLUSTERID;
        }
        catch(Exception $e){
            $this->lasterrnumber=1;
            $this->lasterrdescription=$e->getMessage();
            return "";
        }            
    }
    public function partition($bagname, $funct){
        $ret=false;
        try{
            if(isset($this->bags[$bagname])){
                $BAG=&$this->bags[$bagname];
                $BAG["PARTITION"]=array();
                $partition=&$BAG["PARTITION"];
                $data=&$BAG["DATA"];
                // SCANDISCO I RECORD LIBERI DEL BAG
                // E LI RAGGRUPPO PER VALORE DI PARTIZIONE
                // MEMORIZZANDO I SYSID DELLE FRECCE
                foreach($data as $record){
                    $ARROWID=$record["SYSID"];
                    if(!$this->business[$ARROWID]){
                        $value=$funct($record);
                        if($value!==false){
                            $partition[$value][]=$ARROWID;
                            $this->business[$ARROWID]=true;
                        }
                    }
                }
                $ret=true;
            }
        }
        catch(Exception $e){
            $this->lasterrnumber=1;
            $this->lasterrdescription=$e->getMessage();
        }
        return $ret;
    }
    public function index($table, $column, &$index, $order=">"){
        try{
            $aux=array();
            $index=array();
            $type=0;
            foreach($table as $r => $record){
                if($type==0){
                    if(gettype($record[$column])=="string")
                        $type=1;
                    else
                        $type=2;
                }
                if($type==2)
                    $aux[$r]=floatval($record[$column]);
                else
                    $aux[$r]=strtolower($record[$column]);
                $index[$r]=$r;
            }
            if($order=="<")
                array_multisort($aux, SORT_DESC, $index);
            else
                array_multisort($aux, SORT_ASC, $index);
            return true;
        }
        catch(Exception $e){
            $this->lasterrnumber=1;
            $this->lasterrdescription=$e->getMessage();
            return false;
        }            
    }
    public function dichotomic($table, $column, $value, $index, $identity=false){
        try{
            $first=false;
            $test=false;
            if(gettype($value)=="string"){
                $value=trim(strtolower($value));
                $numeric=false;
            }
            else{
                $numeric=true;
            }
            $count=count($index);
            $min=0;
            $max=$count-1;
            if($max>=$min){
                do{
                    if($max-$min<2){
                        if($numeric){
                            if(abs($value-$table[$index[$min]][$column])<0.0001)
                                $test=$min;
                            elseif(abs($value-$table[$index[$max]][$column])<0.0001)
                                $test=$max;
                            else
                                $test=$min;
                        }
                        else{
                            if($value==$table[$index[$min]][$column])
                                $test=$min;
                            elseif($value==$table[$index[$max]][$column])
                                $test=$max;
                            else
                                $test=$min;
                        }
                        break;
                    }
                    else{
                        $curr=floor($min+($max-$min)/2);
                        if($numeric){
                            if($value<=$table[$index[$curr]][$column]+0.0001)
                               $max=$curr;
                            else
                               $min=$curr;
                        }
                        else{
                            if($value<=$table[$index[$curr]][$column])
                               $max=$curr;
                            else
                               $min=$curr;
                        }
                    }
                }while(true);
            }
            if($test!==false){
                $ARROWID=$table[$index[$test]]["SYSID"];
                do{
                    if($this->business[$ARROWID]){
                        $test+=1;
                        if($test>=$count){
                            break;
                        }
                        if($numeric){
                            if($value>$table[$index[$test]][$column]+0.0001){
                                break;
                            }
                        }
                        else{
                            if($value>$table[$index[$test]][$column]){
                                break;
                            }
                        }
                        $ARROWID=$table[$index[$test]]["SYSID"];
                    }
                    else{
                        // IL CORRENTE E' LIBERO
                        if($identity){
                            // CONTROLLO CHE IN CORRISPONDENZA DELL'ELEMENTO 
                            // IL VALORE SIA IDENTICO A QUELLO CERCATO
                            if($numeric){
                                if(abs($value-$table[$index[$test]][$column])<0.0001){
                                    $first=$test;
                                }
                            }
                            else{
                                if($value==$table[$index[$test]][$column]){
                                    $first=$test;
                                }
                            }
                        }
                        else{
                            // IL VALORE POTREBBE ESSERE QUELLO CERCATO (SE ESISTE)
                            // OPPURE QUELLO IMMEDIATAMENTE INFERIORE
                            $first=$test;
                        }
                        break;
                    }
                }while(true);
            }
        }
        catch(Exception $e){
            $this->lasterrnumber=1;
            $this->lasterrdescription=$e->getMessage();
        }            
        return $first;
    }
    public function search($table, $column, $value, $index){
        $ret=false;
        try{
            if(gettype($value)=="string"){
                $value=trim(strtolower($value));
                $numeric=false;
            }
            else{
                $numeric=true;
            }
            $first=$this->dichotomic($table, $column, $value, $index, true);
            if($first!==false){
                // ESTRAGGO L'INSIEME DI VALORI UGUALI
                $test=$first;
                $count=count($index);
                $ret=array($first);
                do{
                    $test+=1;
                    if($test>=$count){
                        break;
                    }
                    if($numeric){
                        if(abs($value-$table[$index[$test]][$column])>=0.0001){
                            break;
                        }
                    }
                    else{
                        if($value!=$table[$index[$test]][$column]){
                            break;
                        }
                    }
                    $ret[]=$test;
                }while(true);
            }
        }
        catch(Exception $e){
            $this->lasterrnumber=1;
            $this->lasterrdescription=$e->getMessage();
        }            
        return $ret;
    }
    public function markov_initialize(){
        $this->gaugeid=qv_createsysid($this->maestro);
    }
    public function markov($table, $gauge, $tolerance=0.0001){
        try{
            $values=array();
            $refs=array();
            foreach($table as $i => $record){
                if(!$this->business[ $record["SYSID"] ]){
                    $values[]=$record["AMOUNT"];
                    $refs[]=$i;
                }
            }
            if(count($values)>0){
                $ret=gaugesearch($this->gaugeid, array("gauge" => $gauge, "exhaustive" => 2, "tolerance" => $tolerance, "timeout" => 2), $values, $refs);
                if(count($ret)>0)
                    return $ret;
                else
                    return false;
            }
            else{
                return false;
            }
        }
        catch(Exception $e){
            $this->lasterrnumber=1;
            $this->lasterrdescription=$e->getMessage();
            return false;
        }            
    }
    public function markov_terminate(){
        gaugedispose($this->gaugeid);
    }
    public function smart($table, $column, $value){
        try{
            $ret=array();
            foreach($table as $i => $record){
                if(!$this->business[ $record["SYSID"] ]){
                    if(text_seeker($value, $record[$column])){
                        $ret[]=$i;
                    }
                }
            }
            if(count($ret)>0)
                return $ret;
            else
                return false;
        }
        catch(Exception $e){
            $this->lasterrnumber=1;
            $this->lasterrdescription=$e->getMessage();
            return false;
        }            
    }
    public function list2table($list){
        try{
            $ret=array();
            if(count($list)>0){
                $bagname=$this->bagnames[ $list[0] ];
                $BAG=&$this->bags[$bagname];
                foreach($list as $ARROWID){
                    $INDEX=$this->indexes[$ARROWID];
                    $ret[]=$BAG["DATA"][$INDEX];
                }
            }
            return $ret;
        }
        catch(Exception $e){
            $this->lasterrnumber=1;
            $this->lasterrdescription=$e->getMessage();
            return false;
        }            
    }
    public function map2table($source, &$target, $map){
        try{
            $target=array();
            foreach($map as $m){
                $target[]=$source[$m];
            }
            return true;
        }
        catch(Exception $e){
            $this->lasterrnumber=1;
            $this->lasterrdescription=$e->getMessage();
            return false;
        }            
    }
    public function column2table($source, $column, &$target){
        try{
            $target=array();
            foreach($source as $i => $record){
                $target[]=array("SYSID" => $record["SYSID"], $column => $record[$column]);
            }
            return true;
        }
        catch(Exception $e){
            $this->lasterrnumber=1;
            $this->lasterrdescription=$e->getMessage();
            return false;
        }            
    }
    public function table2free($source, &$target){
        try{
            $target=array();
            foreach($source as $record){
                if(!$this->business[ $record["SYSID"] ]){
                    $target[]=$record;
                }
            }
            return true;
        }
        catch(Exception $e){
            $this->lasterrnumber=1;
            $this->lasterrdescription=$e->getMessage();
            return false;
        }            
    }
    public function getinfo($table){
        try{
            if(count($table)>0){
                $ret=array();
                $ret["COUNT"]=0;
                $ret["SUM"]=0;
                $ret["DATEMIN"]=HIGHEST_DATE;
                $ret["DATEMAX"]=LOWEST_DATE;
                $ret["REGDATEMIN"]=HIGHEST_DATE;
                $ret["REGDATEMAX"]=LOWEST_DATE;
                foreach($table as $record){
                    $ret["COUNT"]+=1;
                    $ret["SUM"]+=$record["AMOUNT"];
                    // DATA TRASFERIMENTO
                    if($record["BOWID"]==$this->contoid)
                        $d=$record["BOWTIME"];
                    else
                        $d=$record["TARGETTIME"];
                    if($d<$ret["DATEMIN"]){
                        $ret["DATEMIN"]=$d;
                    }
                    if($d>$ret["DATEMAX"]){
                        $ret["DATEMAX"]=$d;
                    }
                    // DATA REGISTRAZIONE
                    $d=$record["AUXTIME"];
                    if($d<$ret["REGDATEMIN"]){
                        $ret["REGDATEMIN"]=$d;
                    }
                    if($d>$ret["REGDATEMAX"]){
                        $ret["REGDATEMAX"]=$d;
                    }
                }
            }
            else{
                $ret=false;
            }
        }
        catch(Exception $e){
            $this->lasterrnumber=1;
            $this->lasterrdescription=$e->getMessage();
            $ret=false;
        }            
        return $ret;
    }
    public function padstring($value, $size=30){
        return strtoupper(str_pad($value, $size));
    }
    public function paddate($value){
        return substr(str_replace($this->trdate, "", $value), 0, 8);
    }
    public function padnumber($value){
        $value=strval($value);
        $p=strpos($value, ".");
        if($value!==false){
            $INT=substr($value, 0, $p);
            $DEC=substr($value, $p+1);
        }
        else{
            $INT=$value;
            $DEC="0000000";
        }
        $INT=str_pad($INT, 18, "0", STR_PAD_LEFT);
        $DEC=str_pad($DEC, 7, "0");
        return "$INT.$DEC";
    }
    public function padboolean($value){
        if(intval($value))
            return "1";
        else
            return "0";
    }
    public function datediff($d1, $d2){
        $d1=date_create(substr($d1,0,4)."-".substr($d1,4,2)."-".substr($d1,6,2));
        $d2=date_create(substr($d2,0,4)."-".substr($d2,4,2)."-".substr($d2,6,2));
        return ry_datediff($d1, $d2);
    }
    public function dateadd($d, $days){
        $d=date_create(substr($d,0,4)."-".substr($d,4,2)."-".substr($d,6,2));
        return date_format(ry_dateadd($d, $days), "Ymd");
    }
    public function progressenabled($size=1000){
        $this->progressenabled=false;
        $this->progressblock=1000;
    }
    public function progressinit($total){
        if($this->progressenabled){
            print substr( $total . str_repeat(" ", $this->progressblock), 0, $this->progressblock);
            flush();
        }
    }
    public function progress(){
        if($this->progressenabled){
            print str_repeat("X", $this->progressblock);
            flush();
        }
    }
}
?>