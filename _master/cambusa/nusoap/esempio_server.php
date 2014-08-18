<?
//includiamo la libreria NuSOAP
require_once("nusoap.php");

//definiamo il nostro namespace privato come abbiamo fatto anche nel capitolo WSDL
define("_NAMESPACE", "http://www.html.it/guide/esempi/guida_ws/");

//creiamo un istanza del server fornito da nusoap
$server = new soap_server;

//disattiviamo il debug
$server->debug_flag=false;

//cominciamo a configurare un documento WSDL (che sarà elaborato da NuSOAP) per il nostro Web service
$server->configureWSDL('GestioneUtenti', _NAMESPACE); // <-- diamo un nome al webservice ed impostiamo il nostro namespace
$server->wsdl->schemaTargetNamespace = _NAMESPACE; // <-- impostiamo il nostro namespace anche come target dello schema WSDL (come abbiamo fatto nell'esempio WSDL)

//adesso che abbiamo definito le parti personali del WSDL passiamo a definire i tipi, di tutto il resto si occuperà la libreria NuSOAP
$server->wsdl->addComplexType( // <-- aggiungiamo al WSDL un tipo complesso (li abbiamo già visti in precedenza)
    'utente', // <- con il primo argomento impostiamo il nome
    'complexType', // <-- con il secondo il tipo, naturalmente complesso ;)
    'struct', // <-- indichiamo a NuSOAP il tipo php che useremo per questo elemento
    'all', //<-- qui impostiamo l'indicatore di ordine
    '', // <-- attraverso questo argomento si può impostare una restrizione ma noi non ne abbiamo bisogno
    array( // <-- con questo array inseriamo gli elementi child (figli) che faranno parte dell'elemento utente
    'nickname' => array('name'=>'nickname','type'=>'xsd:string'), // <- sono elementi di tipo semplice, impostiamo per loro il nome ed il tipo
    'nome' => array('name'=>'nome','type'=>'xsd:string'), // N.B. in NuSOAP il namespace per i tipi base è xsd, nei nostri esempi precedenti noi avevamo usato xs
    'cognome' => array('name'=>'cognome','type'=>'xsd:string'),
    'email' => array('name'=>'email','type'=>'xsd:string'),
    )
);

//ora abbiamo definito il tipo utente di cui avevamo bisogno
//andiamo a definire qualche operazione da rendere disponibile tramite il webservice che stiamo sviluppando o, ancora meglio, li registriamo nel WSDL che stiamo creando con NuSOAP
$server->register( 
    'getUserById', //<- decidiamo il nome da dare all'operazione
    array('id'=>'xsd:int'), //<-questo array contiene gli elementi da ricevere in input per l'operazione, come chiave il nome dell'elemento da ricevere ed il suo tipo come valore
    array('return'=>'tns:utente'), //<-- stessa cosa per questo array che invece rappresenta l'output dell'operazione. n.b. in NuSOAP i tipi da noi creati vengono inseriti nel namespace "tns"
    _NAMESPACE // <-- ancora una volta specifichiamo il namespace
);

//ora che abbiamo registrato un operazione per il webservice bisogna anche implementarla ;) è estramamente semplice!
function getUserById($id) { //<- n.b. il nome della funzione dev'essere uguale al nome che abbiamo registrato in precedenza per l'operazione
    //qui per questo esempio rimane poco da fare... all'interno della funzione puoi fare quello che ti serve, a seconda dello scopo che devi raggiungere
    //Per questo esempio è sufficiente far ritornare qualcosa alla funzione ;)
		$value = Array(
			    'nickname' => 'ironoxide',
			    'nome' => 'giulio',
			    'cognome' => 'giulietti',
			    'email' => 'ironoxid@libero.it'
    );
    return $value;
}

//A questo punto non c'è più nulla da fare per noi... lasciamo fare a NuSOAP ;)
$HTTP_RAW_POST_DATA = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
$server->service($HTTP_RAW_POST_DATA);
exit(); // <--  a cosa serve qui!? Ma non avevamo detto di far fare tutto a NuSOAP!? Meglio esserne certi! :D

?>