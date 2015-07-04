<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=EmulateIE9, chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Demo ryQue</title>
</head>

<style>
button{width:120px}
</style>

<link type='text/css' href='../ryque/ryque.css' rel='stylesheet' />

<script type='text/javascript' src='../jquery/jquery.js' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.core.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.widget.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.mouse.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.draggable.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.mousewheel.js'></script>
<script type='text/javascript' src='../rygeneral/rygeneral.js' ></script>
<script type='text/javascript' src='../ryque/ryque.js' ></script>

<link type='text/css' href='../jquery/css/jquery.ui.theme.css' rel='stylesheet' />
<link type='text/css' href='../jquery/css/jquery.ui.datepicker.css' rel='stylesheet' />
<link type='text/css' href='../jquery/css/jquery.ui.tabs.css' rel='stylesheet' />
<style>div.ui-datepicker{font-size:11px;}</style>
<style>
input,select,a:focus{outline:none;border:none;}
.contextMenu{position:absolute;display:none;}
.contextMenu>ul>li{font-family:verdana;font-size:12px;text-align:left;}
.contextMenu>ul>li>a{color:black;}
.contextMenu>ul>li>a:focus{outline:1px dotted;color:black;}
.contextDisabled>a{color:silver !important;}
</style>
<link rel='stylesheet' href='../rybox/rytools.css' />
<script type='text/javascript' src='../rybox/rybox.js' ></script>

<script language="JavaScript">

var Obj;
_sessioninfo.sessionid="ZZZZZZZZZZZZZZZZZZZZ";
function init(){

    Obj = $("#gotha").ryque({
        left:30,
        top:80,
        width:700,
        height:400,
        numbered:true,
        checkable:true,
        environ: "sqlite",
        from: "Movimenti",
        where: "SYSID<='C00000100000'",
        columns:[
            {id:"Descrizione",caption:"Descrizione",width:200,code:"C00000000001"},
            {id:"Importo",caption:"Importo",width:180,type:"2",code:"C00000000002"},
            {id:"DataBan",caption:"Data Ban.",width:120,type:"/"},
            {id:"DataVal",caption:"Data Val.",width:120,type:"/"},
            {id:"Uno",caption:"Flag",width:70,type:"?",formula:"[:BOOL(Importo>1000)]"},
            {id:"Futuro",caption:"Futuro",width:120,type:"/",formula:"[:date(DataBan,2 day)]"}
        ]
    });
}

function term(){
    Obj.dispose();
}

</script>

<body onload="init()" onunload="term()" spellcheck="false">

Demo: elenco con caricamento di circa 40.000 righe.

<div id="gotha"></div>

</body>
</html>
