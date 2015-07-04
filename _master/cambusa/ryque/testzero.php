<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=EmulateIE9, chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Algoritmo Zero</title>
</head>

<style>
.slender{font-style:italic;}
</style>

<script type='text/javascript' src='../jquery/jquery.js' ></script>

<script>

var reqid;
var values=[];
var refs=[];
var prima=true;
var listcount=0;
function createid(){
    var date = new Date();
    var components = [
        date.getYear(),
        date.getMonth(),
        date.getDate(),
        date.getHours(),
        date.getMinutes(),
        date.getSeconds(),
        date.getMilliseconds()
    ];
    return components.join("");
}

function init(){
    reqid=createid();
    caricamento();
}

function caricamento(){
    prima=true;
    listcount=1000+Math.floor(5000*Math.random());
    values=[];
    refs=[];
    var i=0;
    var value=0;
    while(i<listcount){
        if(Math.random()<0.5)
            value=Math.floor(1000000*Math.random())-500000;
        else
            value=Math.floor(100000*Math.random())-50000;
        if($.inArray(value, values)==-1){
            values[i]=value;
            refs[i]=i;
            i+=1;
        }
    }
    
    var t="";
    t+="<table>";
    for(var i=0; i<listcount; i++){
        t+="<tr><td style='padding-right:10px;'>"+i+"</td><td id='cell"+i+"'>"+values[i]+"</td></tr>";
    }
    t+="</table>";
    $("#lista").html(t);
    $("#trovati").html("");
    $("#btricerca").removeAttr("disabled");
    try{
        var frameWindow = document.parentWindow || document.defaultView;
        var objframe=$(frameWindow.frameElement); 
        objframe.height(objframe.contents().height()+20);
    }catch(e){}
}

function ricerca(){
    $("#btricerca").attr("disabled",true);
    var params;
    if(prima){
        $("#trovati").html("<span class='slender'>Invio lista e ricerca in corso...</span>");
        params={
            "reqid":reqid,
            "gauge":0,
            "values":values,
            "refs":refs
        };
        prima=false;
    }
    else{
        $("#trovati").html("<span class='slender'>Ricerca in corso...</span>");
        params={
            "reqid":reqid
        };
    }
    $.post("ryzero.php", params, 
        function(d){
            try{
                var v=$.parseJSON(d);
                var b="";
                var t="";
                for(var i=0; i<listcount; i++){
                    if($.inArray(i.toString(),v)>=0){
                        b="#ffaaaa";
                        t+=values[i]+"<br>";
                    }
                    else{
                        b="#ffffff";
                    }
                    $("#cell"+i).css({"background":b});
                }
                if(t!=""){
                    t="<span class='slender'>Trovato!</span><br><br>"+t;
                    $("#trovati").html(t);
                }
                else{
                    $("#trovati").html("<span class='slender'>Nessun sottoinsieme trovato :( </span>");
                }
                try{
                    var frameWindow = document.parentWindow || document.defaultView;
                    var objframe=$(frameWindow.frameElement); 
                    objframe.height(objframe.contents().height()+20);
                }catch(e){}
            }
            catch(e){
                alert(d);
            }
            $("#btricerca").removeAttr("disabled");
        }
    );
}

</script>

<body onload="init()" spellcheck="false" style="font-family:sans-serif;font-size:100%;text-align:justify;">
<span style="font-size:18px;">Demo Algoritmo Zero</span>
<br>
<br>
Il componente che implementa l'algoritmo zero &egrave; in grado di ovviare al 
"Subset sum problem" (problema delle somme parziali), 
classificato come NP-completo: dato un insieme numerico con migliaia 
di elementi, esso &egrave; in grado di trovare sottoinsiemi di somma 
data in pochi istanti.
<br>
<br>
Per fare un esempio della complessit&agrave; del problema: un insieme di <b>1&#x02D9;000</b> elementi possiede<br>
<br>
<b>1&#x02D9;368&#x02D9;173&#x02D9;298&#x02D9;991&#x02D9;500</b> sottoinsiemi con 6 elementi,<br>
<br>
<b>194&#x02D9;280&#x02D9;608&#x02D9;456&#x02D9;793&#x02D9;000</b> sottoinsiemi con 7 elementi,<br>
<br>
<b>24&#x02D9;115&#x02D9;080&#x02D9;524&#x02D9;699&#x02D9;431&#x02D9;125</b> sottoinsiemi con 8 elementi, ...<br>
<br>
<br>
Dimostrazione di come utilizzare l'algoritmo per cercare sottoinsiemi di somma zero.<br>
<br>
I primi tentativi potrebbero dare soluzioni banali: continuate a cercare e troverete soluzioni inaspettate!<br>
<br>
<span class="slender">(La prima ricerca potrebbe impiegare del tempo per inviare la lista al server)</span><br>
<br>
<br>
<input type="button" id="btcaricamento" onclick="caricamento()" value="Nuova lista"></input>
<input type="button" id="btricerca" onclick="ricerca()" value="Cerca"></input>
<br>
<br>
<div id="trovati"></div>
<br>
<hr>
<br>
<div id="lista"></div>

</body>
</html>
