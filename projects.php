<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="chrome=1" />
	<title>Portale Progetti</title>
</head>

<style>
body{font-family:sans-serif;font-size:12px;}
a{color: maroon;text-decoration:none;font-weight:bold;}
a:hover{text-decoration:underline;}
table{font-family:sans-serif;font-size:12px;}
#frame{position:absolute;left:50px;top:100px;}
#prjname{width:200px;}
#prjpwd{width:200px;}
#title{position:absolute;left:50px;top:40px;font-size:24px;}
#bottone{position:absolute;left:0px;top:70px;font-size:14px;}
#progressbar{position:absolute;left:0px;top:100px;width:300px;height:20px;background:#f4f4f4;display:none;border:1px solid silver;}
#titleattivi{position:absolute;left:0px;top:150px;font-size:18px;}
#projects{position:absolute;left:0px;top:190px;font-size:14px;}
</style>

<script type='text/javascript' src='_master/cambusa/jquery/jquery.js' ></script>

<script>

function cloneproject(){
    var counter=0;
    var prjname=$("#prjname").val();
    prjname=prjname.replace(/[^_a-z0-9]/gi, "");
    var prjpwd=$("#prjpwd").val();
    if(prjname!=""){
        $("#progresspercent").width(1);
        $("#progresspercent").html("Preparazione&nbsp;copia...");
        $("#progressbar").show();
        $.post("xcounter.php", {"project":prjname, "password":prjpwd},
            function(d){
                counter=parseInt(d);
                if(counter>0){
                    $.ajax({
                        xhr: function(){
                            var xhr=null;
                            if(window.XMLHttpRequest){
                                xhr=new window.XMLHttpRequest();
                                //Download progress
                                xhr.addEventListener("progress", function(evt){
                                    $("#progresspercent").html("");
                                    $("#progresspercent").width(300*( evt.loaded/1000/counter ));
                                }, false);
                            } 
                            else{ 
                                try{  
                                    xhr=new ActiveXObject("MSXML2.XMLHTTP");
                                    //Download progress
                                    xhr.attachEvent("progress", function(evt) {
                                        try{  
                                            $("#progresspercent").html("");
                                            $("#progresspercent").width(300*( evt.loaded/1000/counter ));
                                        } 
                                        catch(e){} 
                                    });
                                } 
                                catch(e){} 
                            }                        
                            return xhr;
                        },
                        type:"POST",
                        url:"xclone.php",
                        data:{"project":prjname, "password":prjpwd},
                        success: function(data){
                            projectlist();
                            $("#progressbar").hide();
                        }
                    });
                }
                else{
                    $("#progresspercent").width(300);
                    setTimeout(
                        function(){
                            projectlist();
                            $("#progressbar").hide();
                            if(counter==-1){
                                alert("Password errata!");
                            }
                        }, 
                        1000
                    );
                }
            }
        );
    }
    else{
        alert("Specificare un nome di progetto valido!");
    }
}
function projectlist(){
    $("#projects").html("");
    $.post("xlist.php", {}, 
        function(d){
            var v=d.split("|");
            for(var i in v){
                $("#projects").append("<a href='"+v[i]+"/apps/home.php/?project="+v[i]+"' target='_blank'>"+v[i]+"</a><br><br>");
            }
        }
    );
}
</script>

<body onload="projectlist()">

<div id="title">
Manutenzione Progetti
</div>

<div id="frame">

<table>
<tr><td><b>Progetto</b></td><td><input id="prjname" type="text"></td></tr>
<tr><td><b>Password<b></td><td><input id="prjpwd" type="password"></td></tr>
</table>

<a id="bottone" href="javascript:" onclick="cloneproject()">Crea/Aggiorna</a>

<div id="progressbar">
    <div id="progresspercent" style="position:absolute;left:0px;top:0px;width:0px;height:20px;background:red;font-size:14px;">
    </div>
</div>

<div id="titleattivi">
Elenco Progetti Attivi
</div>

<div id="projects"></div>

</div>

</body>
</html>
