<?php 
/****************************************************************************
* Name:            rypaper.php                                              *
* Project:         Cambusa/ryPaper                                          *
* Version:         1.69                                                     *
* Description:     Reporting Utilities                                      *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
if(!isset($tocambusa))
    $tocambusa="../";
class rypaper{
    public $header;
    public $footer;
    public $pathfile;
    private $width;
    private $height;
    private $headerheight;
    private $bodyheight;
    private $footerheight;
    private $marginleft;
    private $marginright;
    private $margintop;
    private $marginbottom;
    private $rowheight;
    private $flagfooter;
    private $page;
    private $offsety;
    private $landscape;
    private $font;
    private $fontsize;
    private $letters;
    private $tabledef;
    private $tablecount;
    private $tablerowheight;
    private $deltarowheight;
    private $paged; // Salto pagina automatico
    private $pdf;   // Output PDF
    private $objpdf;
    private $filed;  // Output su file
    private $filept;
    private $buffer;
    private $nextformat;
    function rypaper(){
        $this->headerheight=0;
        $this->footerheight=0;
        $this->pagewidth=0;
        $this->pageheight=0;
        $this->marginleft=12.7;
        $this->marginright=12.7;
        $this->margintop=12.7;
        $this->marginbottom=31;
        $this->rowheight=4.4;
        $this->flagfooter=false;
        $this->landscape=false;
        $this->font="verdana,sans-serif";
        $this->fontsize=12;
        $this->paged=true;
        $this->pdf=false;
        $this->objpdf=false;
        $this->buffer="";
        $this->nextformat=false;
        $this->filed=false;
        $this->pathfile="";
        $this->filept=0;
        $this->setformat('{"format":"A4"}');
        $this->header=false;
        $this->footer=false;
        
        $this->page=0;
        $this->offsety=0;
        $this->tabledef=false;
        $this->tablecount=0;
        $this->tablerowheight=$this->rowheight;
        $this->deltarowheight=0.5;
        $this->letters=array();
        $this->letters[1]="";
        $this->letters[2]="uno";
        $this->letters[3]="due";
        $this->letters[4]="tre";
        $this->letters[5]="quattro";
        $this->letters[6]="cinque";
        $this->letters[7]="sei";
        $this->letters[8]="sette";
        $this->letters[9]="otto";
        $this->letters[10]="nove";
        $this->letters[11]="dieci";
        $this->letters[12]="undici";
        $this->letters[13]="dodici";
        $this->letters[14]="tredici";
        $this->letters[15]="quattordici";
        $this->letters[16]="quindici";
        $this->letters[17]="sedici";
        $this->letters[18]="diciassette";
        $this->letters[19]="diciotto";
        $this->letters[20]="diciannove";
        $this->letters[21]="venti";
        $this->letters[22]="trenta";
        $this->letters[23]="quaranta";
        $this->letters[24]="cinquanta";
        $this->letters[25]="sessanta";
        $this->letters[26]="settanta";
        $this->letters[27]="ottanta";
        $this->letters[28]="novanta";
    }
    public function setformat($json){
        global $tocambusa;
        $args=json_decode($json);
        // ALTEZZA TESTATA
        if(isset($args->headerheight))
            $this->headerheight=floatval($args->headerheight);
        // ALTEZZA PIEDE
        if(isset($args->footerheight))
            $this->footerheight=floatval($args->footerheight);
        // USCITA PDF
        if(isset($args->pdf))
            $this->pdf=(boolean)intval($args->pdf);
        // USCITA SU FILE
        if(isset($args->file)){
            include $tocambusa."sysconfig.php";
            $this->filed=true;
            $this->pathfile=$args->file;
            $this->pathfile=str_replace("@customize/", $path_customize, $this->pathfile);
            $this->pathfile=str_replace("@cambusa/", "../", $this->pathfile);
            $this->pathfile=str_replace("@databases/", $path_databases, $this->pathfile);
            $this->pathfile=str_replace("@apps/", $path_applications, $this->pathfile);
            if($this->pdf)
                $this->pathfile.=".pdf";
        }
        // USCITA SU FILE SPECIFICANDO LA DIRECTORY TEMPORANEA
        if(isset($args->environ)){
            include $tocambusa."sysconfig.php";
            if(is_file($path_databases."_environs/".$args->environ.".php")){
                $this->filed=true;
                // REPERISCO L'EFFETTIVA DIRECTORY TEMPORANEA
                $env_strconn="";
                include($path_databases."_environs/".$args->environ.".php");
                $dirtemp=$env_strconn;
                // DETERMINO L'ESTENSIONE
                if($this->pdf)
                    $ext="pdf";
                else    
                    $ext="htm";
                // DETERMINO UN PERCORSO UNIVOCO
                $this->pathfile=$this->uniquepath($dirtemp, $ext);
                $this->pathfile=str_replace("@customize/", $path_customize, $this->pathfile);
                $this->pathfile=str_replace("@cambusa/", "../", $this->pathfile);
                $this->pathfile=str_replace("@databases/", $path_databases, $this->pathfile);
            }
        }
        // ORIENTAZIONE
        if(isset($args->landscape)){
            $this->landscape=(boolean)intval($args->landscape);
        }
        // FISSO ALCUNI DEFAULT
        if($this->pdf){
            $this->rowheight=4.2;
            $this->marginbottom=20;
        }
        else{
            $this->rowheight=4.4;
            if($this->landscape)
                $this->marginbottom=22;
            else
                $this->marginbottom=31;
        }
        // MARGINE SINISTRO
        if(isset($args->marginleft))
            $this->marginleft=floatval($args->marginleft);
        // MARGINE DESTRO
        if(isset($args->marginright))
            $this->marginright=floatval($args->marginright);
        // MARGINE SUPERIORE
        if(isset($args->margintop))
            $this->margintop=floatval($args->margintop);
        // MARGINE INFERIORE
        if(isset($args->marginbottom))
            $this->marginbottom=floatval($args->marginbottom);
        // ALTEZZA RIGA
        if(isset($args->rowheight)){
            $this->rowheight=floatval($args->rowheight);
            $this->tablerowheight=$this->rowheight;
        }
        // ALTEZZA RIGA TABELLA
        if(isset($args->tablerowheight))
            $this->tablerowheight=floatval($args->tablerowheight);
        if($this->tablerowheight>=7)
            $this->deltarowheight=3;
        elseif($this->tablerowheight>=6)
            $this->deltarowheight=2;
        elseif($this->tablerowheight>=5)
            $this->deltarowheight=0.85;
        // PAGINAZIONE AUTOMATICA
        if(isset($args->paged))
            $this->paged=(boolean)intval($args->paged);
        // FONT FAMILY
        if(isset($args->font))
            $this->font=$args->font;
        if($this->pdf)
            $this->font="dejavusans";
        // FONT SIZE
        if(isset($args->fontsize))
            $this->fontsize=$args->fontsize;
        // RISOLUZIONE FORMATO CARTA
        if(isset($args->format)){
            switch(strtoupper($args->format)){
            case "B3":
                $this->pagewidth=353;
                $this->pageheight=500;
                break;
            case "B4":
                $this->pagewidth=250;
                $this->pageheight=353;
                break;
            case "B5":
                $this->pagewidth=176;
                $this->pageheight=250;
                break;
            case "A5":
                $this->pagewidth=148;
                $this->pageheight=210;
                break;
            case "A3":
                $this->pagewidth=297;
                $this->pageheight=420;
                break;
            case "A4":
            default:
                $this->pagewidth=210;
                $this->pageheight=297;
            }
        }
        if(isset($args->width))
            $this->width=intval($args->width);
        if(isset($args->height))
            $this->height=intval($args->height);
        if($this->pdf)
            $this->paged=true;
        // GESTIONE LANDSCAPE
        if($this->landscape){
            if($this->pagewidth < $this->pageheight){
                $aux=$this->pagewidth;
                $this->pagewidth=$this->pageheight;
                $this->pageheight=$aux;
            }
        }
        else{
            if($this->pagewidth > $this->pageheight){
                $aux=$this->pagewidth;
                $this->pagewidth=$this->pageheight;
                $this->pageheight=$aux;
            }
        }
        // APPLICAZIONE MARGINI
        $this->width=$this->pagewidth-$this->marginleft-$this->marginright;
        $this->height=$this->pageheight-$this->margintop-$this->marginbottom;
        $this->bodyheight=$this->height-$this->headerheight-$this->footerheight;
    }
    public function write($html){
        if($this->pdf)
            $this->buffer.=$html;
        elseif($this->filed)
            fwrite($this->filefp,$html);
        else
            print $html;
    }
    public function cdate(){
        $argnum=func_num_args();
        $args=func_get_args();
        switch($argnum){
        case 0:
            $d=date("Ymd");
            break;
        default:
            $d=$args[0];
        }
        if(substr($d,4,1)=="-")
            return substr($d,8,2)."/".substr($d,5,2)."/".substr($d,0,4);
        else
            return substr($d,6,2)."/".substr($d,4,2)."/".substr($d,0,4);
    }
    public function cnumber(){
        $argnum=func_num_args();
        $args=func_get_args();
        switch($argnum){
        case 0:
            $n=0;
            $d=2;
            break;
        case 1:
            $n=floatval($args[0]);
            $d=2;
            break;
        default:
            $n=floatval($args[0]);
            $d=floatval($args[1]);
        }
        return number_format($n,$d, ",", ".");
    }
    public function cboolean($value){
        if((integer)$value)
            return "&#x2714;";
        else
            return "&#x0020;";
    }
    public function timestamp(){
        return date("Y-m-d H:i:s");
    }
    public function timereport(){
        return date("d/m/Y H:i");
    }
    public function lnumber($number){
        $ret="";
        $punto=strpos($number,".");
        if($punto===false){
            $dec="/00";
        }
        else{
            $dec="/".substr($number,$punto+1);
            if(strlen($dec)==2)
                $dec=$dec."0";
        }
        $value=abs(intval($number));
        if($value!=0){
            for($pow=4;$pow>=0;$pow--){
                $fract1=(integer)($value/pow(1000,$pow));
                if($fract1>=1){
                    $fract2=$fract1;
                    if($fract2>99){
                        $fract3=(integer)($fract2/100);
                        $fract2=$fract2-$fract3*100;
                        if($fract3==1)
                            $ret=$ret."cento";
                        else
                            $ret=$ret.$this->letters[$fract3+1]."cento";
                    }
                    if($fract2<=20)
                        $ret=$ret.$this->letters[$fract2+1];
                    else{
                        $fract3=(integer)($fract2/10);
                        $ret=$ret.$this->letters[$fract3+19];
                        $fract2=$fract2-$fract3*10;
                        if($fract2==1 || $fract2==8)
                            $ret=substr($ret,0,strlen($ret)-1);
                        $ret=$ret.$this->letters[$fract2+1];
                    }
                    switch($pow){
                    case 1:
                        if ($fract1==1)
                            $ret=substr($ret,0,strlen($ret)-3)."mille";
                        else
                            $ret=$ret."mila";
                        break;
                    case 2:
                        if ($fract1==1)
                            $ret=substr($ret,0,strlen($ret)-3)."unmilione";
                        else
                            $ret=$ret."milioni";
                        break;
                    case 3:
                        if($fract1==1)
                            $ret=substr($ret,0,strlen($ret)-3)."unmiliardo";
                        else
                            $ret=$ret."miliardi";
                        break;
                    case 4:
                        if($fract1==1)
                            $ret="mille";
                        else
                            $ret=$ret."mila";
                        if((integer)(($value-$fract1*pow(1000,$pow))/pow(1000,3))==0)
                            $ret=$ret."miliardi";
                        break;
                    }
                    $value=$value-$fract1*pow(1000,$pow);
                }
            }
        }
        else{
            $ret="zero";
        }
        $ret=$ret.$dec;
        return $ret;
    }
    public function ldate(){
        $argnum=func_num_args();
        $args=func_get_args();
        switch($argnum){
        case 0:
            $d=date("Ymd");
            break;
        default:
            $d=$args[0];
        }
        if(substr($d,4,1)=="-"){
            $day=intval(substr($d,8,2));
            $month=intval(substr($d,5,2));
        }
        else{
            $day=intval(substr($d,6,2));
            $month=intval(substr($d,4,2));
        }
        switch($month){
        case 1: $month="gennaio"; break;
        case 2: $month="febbraio"; break;
        case 3: $month="marzo"; break;
        case 4: $month="aprile"; break;
        case 5: $month="maggio"; break;
        case 6: $month="giugno"; break;
        case 7: $month="luglio"; break;
        case 8: $month="agosto"; break;
        case 9: $month="settembre"; break;
        case 10: $month="ottobre"; break;
        case 11: $month="novembre"; break;
        case 12: $month="dicembre"; break;
        }
        $year=substr($d,0,4);
        return $day." ".$month." ".$year;
    }
    public function lweek(){
        $ret="";
        $argnum=func_num_args();
        $args=func_get_args();
        switch($argnum){
        case 0:
            $d=date("Ymd");
            break;
        default:
            $d=$args[0];
        }
        if(substr($d,4,1)=="-"){
            $y=intval(substr($d,0,4));
            $m=intval(substr($d,5,2));
            $d=intval(substr($d,8,2));
        }
        else{
            $y=intval(substr($d,0,4));
            $m=intval(substr($d,4,2));
            $d=intval(substr($d,6,2));
        }
        switch(date("D",mktime(0,0,0,$m,$d,$y))){
        case "Mon": $ret = "Luned&igrave;"; break;
        case "Tue": $ret = "Marted&igrave;"; break;
        case "Wed": $ret = "Mercoled&igrave;"; break;
        case "Thu": $ret = "Gioved&igrave;"; break;
        case "Fri": $ret = "Venerd&igrave;"; break;
        case "Sat": $ret = "Sabato"; break;
        case "Sun": $ret = "Domenica"; break;
        }
        return $ret;
    }
    public function getvalue($vector, $index, $name){
        try{
            $ret="";
            if(isset($vector[$index])){
                if(isset($vector[$index][$name]))
                    $ret=$vector[$index][$name];
            }
        }
        catch(Exception $e){}
        //return htmlentities($ret);
        return $ret;
    }
    public function printblock(){
        $args=func_get_args();
        $html=$args[0];
        if(func_num_args()==1)
            $deltay=$this->rowheight;
        else
            $deltay=$args[1];
        if($this->paged){
            if($this->offsety+$deltay > $this->bodyheight){
                $this->pagebreak();
            }
        }
        $this->write($html."\n");
        $this->offsety+=$deltay;
    }
    public function dotted($text, $maxlen){
        if(strlen($text)<=$maxlen)
            return $text;
        else
            return substr($text, 0, $maxlen-1) . "&#8230;"; // &hellip;
    }
    public function pagebreak(){
        $this->footermanage();
        $this->write("<p style='page-break-after:always'>&nbsp;</p>\n");
        $this->page+=1;
        $this->offsety=0;
        $this->headermanage();
    }
    public function begindocument(){
        global $tocambusa;
        if($this->pdf){
            try{
                require_once $tocambusa."html2pdf/html2pdf.class.php";
                $this->objpdf=new HTML2PDF( 
                    ($this->landscape ? "L" : "P"), 
                    array( $this->pagewidth, $this->pageheight), 
                    "en", 
                    true, 
                    "UTF-8", 
                    array($this->marginleft, $this->margintop, $this->marginright, $this->margintop)
                );
            }
            catch(HTML2PDF_exception $e) {
                $this->pathfile.=".txt";
                $fp=fopen($this->pathfile, "w");
                fwrite($fp, $e);
                fclose($fp);
            }
        }
        elseif($this->filed){
            $this->filefp=fopen($this->pathfile,"w");
        }
        $this->write("<!DOCTYPE html>\n");
        $this->write("<html>\n");
        $this->write("<head>\n");
        $this->write("<meta charset='utf-8' />\n");
        $this->write("<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n");
        $this->write("<title>Report</title>\n");
        $this->write("\n");
        $this->write("<style type='text/css'>\n");
        $this->write("body{font-family:".$this->font.";font-size:".$this->fontsize."px;background-color:white;}\n");
        $this->write("table{font-family:".$this->font.";font-size:".$this->fontsize."px;border-collapse:collapse;}\n");
        $this->write("td{padding-right:5px;vertical-align:top;}\n");
        $this->write(".cellsx{text-align:left;overflow-x:hidden;overflow-y:visible;white-space:nowrap;minheight:".$this->tablerowheight."mm;}\n");
        $this->write(".celldx{text-align:right;overflow-x:hidden;overflow-y:visible;white-space:nowrap;minheight:".$this->tablerowheight."mm;}\n");
        $this->write(".cellcx{text-align:center;overflow-x:hidden;overflow-y:visible;white-space:nowrap;minheight:".$this->tablerowheight."mm;}\n");
        $this->write(".cellax{text-align:align;}\n");
        $this->write(".table-header-row{background-color:gray;}\n");
        $this->write(".table-header-cell{color:white;font-weight:bold;minheight:".$this->tablerowheight."mm;}\n");
        $this->write(".subtitle{font-size:18px;}\n");
        $this->write(".report-selection{position:absolute;top:50px;left:0px;font-size:14px;}\n");
        $this->write("</style>\n");
        $this->write("\n");
        $this->write("</head>\n");
        $this->write("\n");
        $this->write("<body>\n");
        $this->page=1;
        $this->headermanage();
    }
    public function enddocument(){
        $this->footermanage();
        $this->write("</body>\n");
        $this->write("</html>\n");
        if($this->pdf){
            try{
                $this->buffer=str_replace("<!DOCTYPE html>", "", $this->buffer);
                $this->buffer=str_replace("<head>", "", $this->buffer);
                $this->buffer=str_replace("</head>", "", $this->buffer);
                $this->buffer=preg_replace("@</?meta[^>]*>@", "", $this->buffer);
                $this->buffer=preg_replace("@<title>[^<]*</title>@", "", $this->buffer);
                $this->buffer=str_replace("<body>", "", $this->buffer);
                $this->buffer=str_replace("</body>", "", $this->buffer);
                $this->buffer=str_replace("<html>", "", $this->buffer);
                $this->buffer=str_replace("</html>", "", $this->buffer);
                $this->buffer="<page>".$this->buffer."</page>";

                $this->objpdf->WriteHTML($this->buffer);
                $this->objpdf->Output($this->pathfile, "F");
            }
            catch(HTML2PDF_exception $e) {
                $this->pathfile.=".txt";
                $fp=fopen($this->pathfile, "w");
                fwrite($fp, $e);
                fclose($fp);
            }
        }
        elseif($this->filed){
            fclose($this->filefp);
        }
    }
    public function onceformat($json){
        $this->nextformat=json_decode($json);
    }
    public function tablerow(){
        $argnum=func_num_args();
        $args=func_get_args();
        if($argnum>count($this->tabledef))
            $argnum=count($this->tabledef);
        if($this->paged){
            if($this->offsety+$this->tablerowheight > $this->bodyheight){
                $this->endtable();
                $this->pagebreak();
                $this->headertable();
            }
        }
        // LETTURA DEI PARAMETRI
        $color="black";
        if($this->nextformat){
            if(isset($this->nextformat->tr)){
                $tr=$this->nextformat->tr;
                if(isset($tr->color)){
                    $color=$tr->color;
                }
            }
            $this->nextformat=false;
        }
        $this->tablecount+=1;
        $offset=0;
        $this->write("<tr style='color:$color;'>");
        for($i=0;$i<$argnum;$i++){
            $class="cellsx";
            $align="left";
            $width=30;
            $type="";
            if(isset($this->tabledef[$i]->w))
                $width=intval($this->tabledef[$i]->w);
            if(isset($this->tabledef[$i]->t))
                $type=$this->tabledef[$i]->t;
            $value=$args[$i];
            switch($type){
                case "?":
                    if($value)
                        $value="&#x2714;";
                    else
                        $value="&#x0020;";
                    $class="cellcx";
                    $align="center";
                    break;
                case "0":case "1":case "2":case "3":case "4":
                    $value=$this->cnumber($value,$type);
                    $class="celldx";
                    $align="right";
                    break;
                case "/":
                    $value=$this->cdate($value);
                    break;
                case "=":
                    $class="cellax";
                    $align="left";
                    break;
            }
            $this->write("<td width='".$width."mm'>");
            $this->write("<div class='".$class."' style='width:".$width."mm;text-align:".$align."'>");            
            $this->write($value);
            $this->write("</div>");
            $this->write("</td>");
            $offset+=$width;
        }
        $this->write("</tr>");
        $this->write("\n");
        $this->offsety+=$this->tablerowheight+$this->deltarowheight;
    }
    private function headertable(){
        $this->tablecount=0;
        $offset=0;
        // APRO IL CONTENITORE
        $this->write("<table>\n");
        $this->write("<tr class='table-header-row'>");
        foreach($this->tabledef as $key => $arg){
            $class="cellsx";
            $align="left";
            $width=30;
            $type="";
            $value="";
            if(isset($arg->w))
                $width=intval($arg->w);
            if(isset($arg->t))
                $type=$arg->t;
            if(isset($arg->d))
                $value=$arg->d;
            switch($type){
                case "?":
                    $class="cellcx";
                    $align="center";
                    break;
                case "0":case "1":case "2":case "3":case "4":
                    $class="celldx";
                    $align="right";
                    break;
            }
            $this->write("<td width='".$width."mm'>");
            $this->write("<div class='".$class." table-header-cell' style='width:".$width."mm;text-align:".$align."'>");            
            $this->write($value);
            $this->write("</div>");
            $this->write("</td>");
            $offset+=$width;
        }
        $this->write("</tr>\n");
        $this->offsety+=$this->tablerowheight+$this->deltarowheight;
    }
    public function begintable($json){
        $this->tabledef=json_decode($json);
        $this->tablerowheight=$this->rowheight;
        $this->headertable();
    }
    public function endtable(){
        // CHIUDO IL CONTENITORE
        $this->write("</table>");
    }
    public function uniquepath($dir, $ext){
        if( is_dir($dir) ){
            $filename=date("YmdHis");
            $path=$dir . $filename . "." . $ext;
            while (file_exists($path)) {
                $filename .= rand(10, 99);
            }
            return $path;
        }
        else{
            return "";
        }
    }
    private function headermanage(){
        // APRO PAGE
        if($this->paged)
            $this->write("<div style='position:relative;top:0mm;height:".($this->height)."mm;overflow:hidden;'>\n");
        else
            $this->write("<div style='position:relative;top:0mm;'>\n");
        
        // STAMPO HEADER
        if($this->paged)
            $this->write("<div style='position:absolute;top:0mm;width:".($this->width)."mm;height:".($this->headerheight)."mm;overflow:hidden;'>\n");
        else
            $this->write("<div style='height:".($this->headerheight)."mm;overflow:hidden;'>\n");
        if($this->header){
            $funct=$this->header;
            $funct();
        }
        $this->flagfooter=true;
        $this->write("\n</div>\n");
        
        // APRO BODY
        if($this->paged)
            $this->write("<div style='position:absolute;width:".($this->width)."mm;top:".($this->headerheight)."mm;height:".($this->bodyheight)."mm;overflow:hidden;'>\n");
        else
            $this->write("<div>\n");
    }
    private function footermanage(){
        if($this->flagfooter){
            // CHIUDO BODY
            $this->write("\n</div>\n");
        
            // STAMPO FOOTER
            if($this->paged)
                $this->write("<div style='position:absolute;top:".($this->height-$this->footerheight)."mm;width:".($this->width)."mm;height:".($this->footerheight)."mm;'>\n");
            else
                $this->write("<div style='height:".($this->footerheight)."mm;'>\n");
            if($this->footer){
                $funct=$this->footer;
                $funct();
            }
            $this->flagfooter=false;
            $this->write("\n</div>\n");
            
            // CHIUDO PAGE
            $this->write("</div>\n");
        }
    }
}
$PAPER=new rypaper();
?>