<?php
/****************************************************************************
* Name:            ryq_gaugeminus.php                                       *
* Project:         Cambusa/ryQue                                            *
* Version:         1.69                                                     *
* Description:     Exhaustive search                                        *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

// Rimedio alla complessità computazionale 
// del cosiddetto problema delle somme parziali
// Nella versione MINUS tale ricerca non è implementata

define("MAX_LEVEL",  10);

// Costanti di fase
define("zeroExhaustive",  0);
define("zeroMarkov",  1);
define("zeroEnd",  2);

class StructElemento{
     
     public $Valore;
     public $IndiceOrig;
     public $Selezionato;

}

class StructStatus{

     public $Phase;
     public $Tolerance;
     public $Gauge;
     public $ExhaustiveLevel;
     public $SkipIndex;
     public $Solutions;
     public $SkipSolutions;
     public $MinSize;
     public $MaxSize;
     public $Timeout;
     public $MaxValore;
     public $LastLevel;
     public $LastIndici;
     
     function StructStatus(){
        $this->LastIndici=array_fill(1, MAX_LEVEL, 0);
        $this->Timeout=10;
        $this->Gauge=0;
     }
     
}

function zerosearch($RequestID, $Params=false, $Values=false, $Refs=false){

    global $path_cambusa;
	global $NameFileSTS;
	global $NameFileSTO;
	global $NameFileERR;
    global $Status;
    global $Valori;
    global $StatusErrNumber;
    global $StatusErrDescription;
    global $StatusRitorno;
    global $StatusStorico;
    global $StatusSkipUsed;
    global $MsgAvanzamento;
	global $LastControlloF;
    global $LastControlloS;
    global $LastControlloT;
	global $FaseRicerca;
	global $ContaSelez;
	global $TotaleSelez;

    $Valori=array();    // StructElemento
	$Status=new StructStatus();
	$StatusSkipUsed="";
	$StatusRitorno="";
	$StatusStorico="";
    $StatusErrNumber=0;
    $StatusErrDescription="";

	$NameFileSTS="";    // File status
	$NameFileSTO="";    // File storico
	$NameFileERR="";    // File errori
    $NameFileSTAR="";    // Tutti i file
	
	$LastControlloF=0;
	$LastControlloS=0;
	$LastControlloT=0;
	$FaseRicerca=0;
	$ContaSelez=0;
	$TotaleSelez=0;
	
	$MsgAvanzamento="";

	$FolderZero="";
	
	$Versione="";
	$Comando="";
	$NumFile=0;

	$NewLevel=0;
	
	$Esito=0;
	$Controllo=0;
	
    try{
		$FaseRicerca=zeroExhaustive;
		
		if($RequestID != ""){
		
            $LastControlloT=time();
			
			$FolderZero = $path_cambusa."ryque/requests";
			
			if(!is_dir($FolderZero)){
				GestioneErroreGrave("Protocol folder '" . $FolderZero . "' doesn't exist");
			}
			
			$NameFileSTS = $FolderZero . "/" . $RequestID . ".sts";		// File status
			$NameFileSTO = $FolderZero . "/" . $RequestID . ".sto";		// File storico
			$NameFileERR = $FolderZero . "/" . $RequestID . ".err";		// File errori
            $NameFileSTAR = $FolderZero . "/" . $RequestID . ".*";		// Tutti i file
			
			$TotaleSelez = 0;
			$ContaSelez = 0;
            
            if($Params && $Values){
                $Comando="init";
                
                if(isset($Params["version"]))
                    $Versione = $Params["version"];
                if(isset($Params["tolerance"]))
                    $Status->Tolerance = floatval($Params["tolerance"]);
                if(isset($Params["gauge"]))
                    $Status->Gauge = floatval($Params["gauge"]);
                if(isset($Params["exhaustive"]))
                    $Status->ExhaustiveLevel = intval($Params["exhaustive"]);
                if(isset($Params["skipsolutions"]))
                    $Status->SkipSolutions = intval($Params["skipsolutions"]);
                if(isset($Params["minsize"]))
                    $Status->MinSize = intval($Params["minsize"]);
                if(isset($Params["maxsize"]))
                    $Status->MaxSize = intval($Params["maxsize"]);
                if(isset($Params["timeout"]))
                    $Status->Timeout = intval($Params["timeout"]);
                if(isset($Params["newlevel"]))
                    $NewLevel = intval($Params["newlevel"]);
                
                $Status->MaxValore=count($Values);
                $Valori=array();
                
                if(!$Refs){
                    $Refs=range(0, $Status->MaxValore-1, 1);
                }

                array_multisort($Values, $Refs);

                for($i=1; $i<=$Status->MaxValore; $i++){
                    $Valori[$i]=new StructElemento();
                    $Valori[$i]->Valore = $Values[$i-1];
                    $Valori[$i]->IndiceOrig = $Refs[$i-1];
                }
            }
            else{
                $Comando="continue";
            }
			
			//---------------------------------------------------------
			// Gestione del comando init: inizializzazione dello stato
			//---------------------------------------------------------
			
			if($Comando == "init"){
			
				if($Status->Tolerance < 0.000001){
					$Status->Tolerance = 0.001;
				}
				
				$Status->SkipIndex = false;
				$Status->Solutions = 0;
				
				if($Status->SkipSolutions == 0){
					$Status->SkipSolutions = 10;
				}
				
                if($Status->ExhaustiveLevel < 0){
                    $Status->ExhaustiveLevel = 0;
                }
                else if($Status->ExhaustiveLevel == 0){
                    $Status->ExhaustiveLevel = 3;
                    $Status->SkipIndex = true;
                }
                else if($Status->ExhaustiveLevel > MAX_LEVEL){
                    $Status->ExhaustiveLevel = MAX_LEVEL;
                }
				
				/*
                if($Status->ExhaustiveLevel > 0)
					$Status->Phase = zeroExhaustive;
				else
					$Status->Phase = zeroMarkov;
                */
                if($Status->ExhaustiveLevel<3){
                    $Status->ExhaustiveLevel=3;
                }
                $Status->Phase = zeroExhaustive;
				
				$Status->LastLevel = 1;
				
				if($Status->MinSize <= 0){
					$Status->MinSize = 1;
				}
				
				if($Status->MaxSize <= 0){
					$Status->MaxSize = $Status->MaxValore;
				}
				
				if($Status->MinSize > $Status->MaxSize){
					$Status->MinSize = $Status->MaxSize;
				}
				
				if($Status->LastLevel < $Status->MinSize){
					$Status->LastLevel = $Status->MinSize;
				}
				
				if($Status->ExhaustiveLevel > $Status->MaxSize){
					$Status->ExhaustiveLevel = $Status->MaxSize;
				}
				
				$StatusSkipUsed = "|";
				
				if($NewLevel > 0){
					$Status->LastLevel = $NewLevel;
				}
				
				EliminaTemporanei();
				
				if($Status->MaxValore == 0){
				
					$StatusErrNumber = 5;
					$StatusErrDescription = "No values";

					throw new Exception( $StatusErrDescription );

				}
				
			}
			
			//-------------------------------------------------------
			// Gestione del comando continue: ripristino dello stato
			//-------------------------------------------------------
			
			if($Comando == "continue"){
			
				$s=file($NameFileSTS);
			
				if(count($s)>0){
                
                    $i=0;
                    $Status->Phase = intval($s[$i]); $i+=1;
                    $Status->LastLevel = intval($s[$i]); $i+=1;
                    
                    for($j=1; $j<=MAX_LEVEL; $j++){
                        $Status->LastIndici[$j] = intval($s[$i]); $i+=1;
                    }
                    
                    $Status->Tolerance = floatval($s[$i]); $i+=1;
                    $Status->Gauge = floatval($s[$i]); $i+=1;
                    $Status->ExhaustiveLevel = intval($s[$i]); $i+=1;
                    $Status->SkipIndex = intval($s[$i]); $i+=1;
                    $Status->Solutions = intval($s[$i]); $i+=1;
                    $Status->SkipSolutions = intval($s[$i]); $i+=1;
                    $StatusSkipUsed = trim($s[$i]); $i+=1;
                    $Status->MinSize = intval($s[$i]); $i+=1;
                    $Status->MaxSize = intval($s[$i]); $i+=1;
                    $Status->Timeout = intval($s[$i]); $i+=1;
                    $Status->MaxValore = intval($s[$i]); $i+=1;
                    
                    $Valori=array();
            
                    for($j=1; $j<=$Status->MaxValore; $j++){
                    
                        $v=explode("|", trim($s[$i]));
                        $i+=1;
                        
                        $Valori[$j]=new StructElemento();
                        $Valori[$j]->Valore = floatval($v[0]);
                        $Valori[$j]->IndiceOrig = $v[1];
                        $Valori[$j]->Selezionato = intval($v[2]);
                        
                         if($Valori[$j]->Selezionato){
                        
                             $TotaleSelez = $TotaleSelez + $Valori[$j]->Valore;
                             $ContaSelez+=1;
                             
                         }
                        
                    }
                
                    if($NewLevel > 0){
                        $Status->LastLevel = $NewLevel;
                    }
                    
                    //set_time_limit(3*$Status->Timeout);
                    
                }
                else{
                
                    // Imposto un risultato vuoto
                    $StatusRitorno="";
                    
                }
			}
			
            //---------------------------------------------------------------
            // Apro vuoto il file di stato in modo tale che la ControllaBeak
            // termina il programma su cancellazione del file stesso
            //---------------------------------------------------------------
            
            $NumFile=fopen($NameFileSTS, "w");
            fclose($NumFile);
                
            //-------------------
			// Ricerca soluzioni
			//-------------------
			
            $exitdo=false;
            
			do{
			
				switch($Status->Phase){
				
                case zeroExhaustive:
                
                    //-----------------------
                    // FASE: RICERCA ENNUPLE
                    //-----------------------
                    
                    for($i=$Status->LastLevel; $i<=$Status->ExhaustiveLevel; $i++){
                    
                        $Status->LastLevel = $i;
                        
                        $MsgAvanzamento = "Looking for small subsets (" . $i . " of " . $Status->ExhaustiveLevel . ")";
                        
                        $Esito = RicercaEnnuple(1, $i, $Status->Gauge);
                        
                        if($Status->Phase == zeroEnd){
                            // Voglio ottenere una exit do
                            $exitdo=true;
                            break;
                        }
                        
                        $Controllo = true;
                        
                        if($Esito){
                        
                            //-------------------
                            // Trovata soluzione
                            //-------------------
                            
                            // Abbasso tutti i flag selezionato
                            ResettaSelezione();
                            
                            // Alzo i flag selezionato corripondenti alla soluzione trovata
                            for($j=1; $j<=$Status->LastLevel; $j++){
                                $Valori[$Status->LastIndici[$j]]->Selezionato = true;
                            }
                            
                            // Serializzo la soluzione
                            SerializzaSoluzione();
            
                            // Controllo che non sia già stata trovata in fasi precedenti
                            if(ControllaStorico($StatusStorico)){
                                
                                // Gestione skip attivo quando la ricerca esaustiva non è specificata (=0)
                                if($Status->SkipIndex){

                                    if($Status->LastLevel == 3){

                                        if($Status->Solutions >= $Status->SkipSolutions - 1){

                                            //$Status->Phase = zeroMarkov;
                                            $Status->Phase = zeroEnd;

                                        }

                                    }

                                }
                                
                                // Salvo lo stato per poterlo ripristinare in seguito
                                SalvaStato();
                                
                                // Scrivo il file con lo storico
                                SalvaStorico();
            
                                // Voglio ottenere una exit for
                                break;
                            
                            }
                            else{
                            
                                $Controllo = false;
                            
                            }

                        }
                    
                        if(!$Esito){
                        
                            for($j=1; $j<=MAX_LEVEL; $j++){
                                $Status->LastIndici[$j] = 0;
                            }
                            
                        }
                        
                        if(!$Controllo){
                            $i-=1;
                        }
                        
                    }
                    
                    if($exitdo){
                        break;
                    }
                    
                    if($Esito){
                        // Voglio ottenere una exit for
                        $exitdo=true;
                        break;
                    }
                    else{
                        //$Status->Phase = zeroMarkov;
                        $Status->Phase = zeroEnd;
                    }
                    break;  // exit case
                    
                case zeroMarkov:
                
                    // MINUS NON HA MARKOV
                    $Status->Phase = zeroEnd;
                    
                    break;  // exit case
				}
				
                if($exitdo){
                    break;
                }
                    
				ControllaBreak();

			}while($Status->Phase != zeroEnd);
		}	
		else{
		
			GestioneErroreGrave("No requestID");
		
		}

	}
    catch(Exception $e){
	
		$StatusErrNumber = 5;
        $StatusErrDescription=$e->getMessage();
		
		GestioneErrore();
		
	}
    // GESTIONE DEL RITORNO
    if($StatusRitorno!=""){
        $v=explode("|", $StatusRitorno);
    }
    else{
        $v=array();
    }
    return $v;
}

function TrovaPrimo($Rif){

	global $Status, $Valori, $StatusErrNumber, $StatusErrDescription;

    $Ritorno=0;
    $CurrCnt=0;
     
	try{
	
		//-------------------------
		// Procedimento dicotomico
		//-------------------------
			
		//-------------------------------
		// Determino l'indice di ritorno
		//-------------------------------
		
		$Min = 1;
		$Max = $Status->MaxValore;
		
		while(true){
				
			if($Max - $Min < 2){
		
                if(abs($Rif - $Valori[$Max]->Valore) <= $Status->Tolerance)
                    $Ritorno = $Max;
                else if(abs($Rif - $Valori[$Min]->Valore) <= $Status->Tolerance)
                    $Ritorno = $Min;
                else
                    $Ritorno = 0;
				
				break;
		
			}
            else{
					
				$CurrCnt = floor($Min + ($Max - $Min) / 2);
		
                if(abs($Rif - $Valori[$CurrCnt]->Valore) <= $Status->Tolerance){
            
                    $Ritorno = $CurrCnt;
                    
                    while($CurrCnt > 1){
                        
                        $CurrCnt -= 1;
                    
                        if(abs($Rif - $Valori[$CurrCnt]->Valore) > $Status->Tolerance){
                            break;
                        }
                        
                        $Ritorno = $CurrCnt;
                        
                    }
                    
                    break;
            
                }
                else if($Rif < $Valori[$CurrCnt]->Valore){
            
                    $Max = $CurrCnt;
                    
                }
                else{
            
                    $Min = $CurrCnt;
                    
                }
			}
		
		}
     
	}
    catch(Exception $e){
	
		$StatusErrNumber = 5;
        $StatusErrDescription=$e->getMessage();
		
		GestioneErrore();
        
        $Ritorno=0;
		
	}
	
    return $Ritorno;
          
}

function SerializzaSoluzione(){

    global $StatusRitorno, $StatusStorico, $Valori, $Status;
	
	$Ritorno="";
    $Storico="";
	
    for($i=1; $i<=$Status->MaxValore; $i++){
     
          if($Valori[$i]->Selezionato){
          
               if($Storico != ""){
                    $Storico .= "|";
                    $Ritorno .= "|";
               }
               
               $Ritorno .= $Valori[$i]->IndiceOrig;
               $Storico .= $i;
          
          }
          
    }
     
     $StatusRitorno = $Ritorno;
     $StatusStorico = $Storico;
     
}

function SalvaStorico(){

    global $StatusStorico, $NameFileSTO;
	
    //-------------------------
    // Scrittura dello storico
    //-------------------------

    $NumFile=fopen($NameFileSTO, "a+");
    fwrite($NumFile, "§" . $StatusStorico . "§");
    fclose($NumFile);

}

function SalvaStato(){

	global $Status, $Valori, $NameFileSTS, $StatusSkipUsed;
     
    $Status->Solutions += 1;
     
    $NumFile=fopen($NameFileSTS, "w");
    fwrite($NumFile, $Status->Phase."\n");
    fwrite($NumFile, $Status->LastLevel."\n");
    
    for($i=1; $i<=MAX_LEVEL; $i++){
        fwrite($NumFile, $Status->LastIndici[$i]."\n");
    }
     
    fwrite($NumFile, $Status->Tolerance."\n");
	fwrite($NumFile, $Status->Gauge."\n");
    fwrite($NumFile, $Status->ExhaustiveLevel."\n");
     
	if($Status->SkipIndex)
		$s="-1";
	else
		$s="0";
    fwrite($NumFile, $s."\n");
	
    fwrite($NumFile, $Status->Solutions."\n");
    fwrite($NumFile, $Status->SkipSolutions."\n");
    fwrite($NumFile, $StatusSkipUsed."\n");
    fwrite($NumFile, $Status->MinSize."\n");
    fwrite($NumFile, $Status->MaxSize."\n");
    fwrite($NumFile, $Status->Timeout."\n");
    fwrite($NumFile, $Status->MaxValore."\n");
    
    for($i=1; $i<=$Status->MaxValore; $i++){
		
		if($Valori[$i]->Selezionato)
			$s="-1";
		else
			$s="0";
          
		$s=$Valori[$i]->Valore . "|" . $Valori[$i]->IndiceOrig . "|" . $s;
		
        fwrite($NumFile, $s."\n");
		
     }
     
    fclose($NumFile);
}

function ResettaSelezione(){

	global $Valori, $TotaleSelez, $ContaSelez;
	
    $TotaleSelez = 0;
    $ContaSelez = 0;

    foreach($Valori as &$sel){
        $sel->Selezionato = false;
    }
     
}

function ControllaStorico($Soluzione){ // As Boolean

    global $NameFileSTO;
	
    $Esito=false;
     
     if($Soluzione != ""){
     
		$Buffer="";
        $Buffer=@file_get_contents($NameFileSTO);
		
        $Soluzione = "§" . $Soluzione . "§";

        $Esito = (strpos($Buffer, $Soluzione) === false);
     
     }
     else{
          
          $Esito = false;
     
     }
     
     return $Esito;

}

function ControllaBreak(){

    global $Status, $NameFileSTS;
    global $NameFileSTO, $NameFileERR, $MsgAvanzamento;
	global $LastControlloF, $LastControlloS, $LastControlloT;
    global $StatusErrNumber, $StatusErrDescription;

	try{
	
        $CurrTimer=time();
		
		if(abs($CurrTimer - $LastControlloF) > 2){
		
			if(!is_file($NameFileSTS)){
				$Status->Phase = zeroEnd;
			}
			
			if($Status->Phase == zeroEnd){
			
				EliminaTemporanei();
			
			}
			
			if($Status->Timeout > 0){

				if($Status->Phase != zeroEnd){
				
					if(($CurrTimer - $LastControlloT) > $Status->Timeout){
	
						EliminaTemporanei();
						
						// Timeout scaduto: scrivo una risposta vuota
                        $StatusRitorno="";
						
						$Status->Phase = zeroEnd;
	
					}
				
				}
				
			}
		
			$LastControlloF = $CurrTimer;
			
		}
		
		if($CurrTimer - $LastControlloS > 0){
		
			$LastControlloS = $CurrTimer;
			
		}
     
	}
    catch(Exception $e){
	
		$StatusErrNumber = 5;
        $StatusErrDescription=$e->getMessage();
		
		GestioneErrore();
        
        $Status->Phase = zeroEnd;
		
	}

    return ($Status->Phase == zeroEnd);
     
}

function RicercaEnnuple($Livello, $MaxLivello, $Gauge){  // As Boolean

	global $Status, $Valori, $StatusErrNumber, $StatusErrDescription;

    $MinInd=0;
    $Ind=0;
    $Esito=0;
	
	try{

		//------------------------------------------------------------
		// MaxLivello è la cardinalità dell'insieme soluzione cercato
		// Livello è la profondità della ricorsione raggiunta
		//------------------------------------------------------------
		
		$Esito = false;
	
		if($Livello == $MaxLivello){
			
			//-----------------------------------------
			// Sono all'ultimo stadio della ricorsione
			//-----------------------------------------
			
			$MinInd = $Status->LastIndici[$MaxLivello];
			
			if($MinInd == 0){
			
				$Ind = TrovaPrimo($Gauge);
			
				if($Ind > 0){
                
                    $free=true;
                    for($i=1; $i<=$MaxLivello-1; $i++){
                        if($Status->LastIndici[$i]==$Ind){
                            $free=false;
                        }
                    }
	
                    if($free){
                    
                        //-------------------
                        // Trovata soluzione
                        //-------------------
                        
                        $Status->LastIndici[$MaxLivello] = $Ind;
                        
                        $Esito = true;
                    
                    }
                    else{
                    
                        $Status->LastIndici[$MaxLivello] = 0;
                    
                    }
				
				}
			
            }
			else{
			
				if($MinInd < $Status->MaxValore){
				
					$Status->LastIndici[$MaxLivello] = $MinInd + 1;
						
					if(abs($Gauge - $Valori[$MinInd + 1]->Valore) <= $Status->Tolerance){
					
						//-------------------
						// Trovata soluzione
						//-------------------
						
						$Esito = true;
					
					}
				
                }
				else{
				
					$Status->LastIndici[$MaxLivello] = 0;
				
				}
				
			}
		
		}
        else{
		
			//-----------------------
			// Eseguo una ricorsione
			//-----------------------
			
            if($Status->LastIndici[$Livello] == 0){
            
                if($Livello == 1)
                    $Status->LastIndici[$Livello] = 1;
                else
                    $Status->LastIndici[$Livello] = $Status->LastIndici[$Livello - 1] + 1;
                
            }
            
            for($i=$Status->LastIndici[$Livello]; $i<=$Status->MaxValore; $i++){
            
                $Status->LastIndici[$Livello] = $i;
                
                $Esito = RicercaEnnuple($Livello + 1, $MaxLivello, $Gauge - $Valori[$i]->Valore);
                
                if($Esito){
                    break;
                }
                
                $Status->LastIndici[$Livello] = 0;
                
            }
			
			if($Livello <= 2){
				if(ControllaBreak()){
					$Esito = true;
				}
			}
				
		}
     
	}
    catch(Exception $e){
	
		$StatusErrNumber = 5;
        $StatusErrDescription=$e->getMessage();
		
		GestioneErrore();
        
        $Esito=false;
		
	}
    
    return $Esito;

}

function GestioneErrore(){

    global $Status;
	global $StatusErrNumber;
	global $StatusErrDescription;
	global $NameFileERR;
     
    $NumFile=fopen($NameFileERR, "w");
    fwrite($NumFile, $StatusErrNumber . "|" . $StatusErrDescription);
    fclose($NumFile);

    // Imposto una risposta vuota
    $StatusRitorno="";

    // Mi metto nella fase di fine ricerca per uscire dal loop
    $Status->Phase = zeroEnd;
     
    return false;

}

function GestioneErroreGrave($Testo){

    $NumFile=fopen("ryzero.err", "w");
    fwrite($NumFile, $Testo);
    fclose($NumFile);
	
}

function EliminaTemporanei(){
    global $NameFileSTAR;
	try{
        $g=glob($NameFileSTAR);
        foreach($g as $path){
            @unlink($path);
        }
	}
    catch(Exception $e){}
}

function zerodispose($RequestID){
    global $path_cambusa, $NameFileSTAR;
    $NameFileSTAR = $path_cambusa."ryque/requests" . "/" . $RequestID . ".*";
    EliminaTemporanei();
}
?>