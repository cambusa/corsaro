<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Demo ryGauge Javascript</title>
    <script type='text/javascript' src='rygauge.js' ></script>
</head>

<script language="JavaScript">

var z;

function init(){
    // LET'S LOAD A RANDOM ARRAY
    var listcount=1000+Math.floor(5000*Math.random());
    var values=[];
    var i=1;
    var value=0;
    while(i<listcount){
        if(Math.random()<0.5)
            value=Math.floor(1000000*Math.random())-500000;
        else
            value=Math.floor(100000*Math.random())-50000;
        if(values.indexOf(value)==-1){
            values[i]={value:value, ref:i};  // FIRST METHOD
            //values[i]=value;  // SECOND METHOD
            i+=1;
        }
    }
    z=new rygauge({
        haystack:values,
        gauge:1000,
        progress:function(mess){
            document.getElementById("progress").innerHTML=mess;
        },
        issue:function(needle){
            if(needle){
                for(var j in needle){
                    console.log(values[needle[j]]);
                }
            }
        }
    });
}

function search(){
    z.search();
}

</script>

<body onload="init()">

<div id="search" style="position:absolute;left:20px;top:10px;cursor:pointer;" onclick="search()">Search</div>
<div id="progress" style="position:absolute;left:20px;top:50px;"></div>

</body>
</html>
