<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="chrome=1" />
	<title>Home Projects</title>
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

var avanzamento;

function cloneproject(){
    var counter=0;
    var prjname=$("#prjname").val();
    prjname=prjname.replace(/[^_a-z0-9]/gi, "");
    var prjpwd=$("#prjpwd").val();
    if(prjname!=""){
        $("#progresspercent").width(1);
        $("#progresspercent").html("Preparing&nbsp;to&nbsp;copy...");
        $("#progressbar").show();
        $.post("xcounter.php", {"project":prjname, "password":prjpwd},
            function(d){
                counter=parseInt(d);
                if(counter>0){
                    $.ajax({
                        type:"POST",
                        url:"xclone.php",
                        data:{"project":prjname, "password":prjpwd},
                        success: function(data){
                            clearInterval(avanzamento);
                            projectlist();
                            $("#progressbar").hide();
                        }
                    });
                }
                else{
                    $("#progresspercent").width(300);
                    setTimeout(
                        function(){
                            clearInterval(avanzamento);
                            projectlist();
                            $("#progressbar").hide();
                            if(counter==-1){
                                alert("Wrong password!");
                            }
                        }, 
                        1000
                    );
                }
            }
        );
        avanzamento=setInterval(function(){
            $.get("progress.txt")
            .done(function(d){
                $("#progresspercent").html("");
                $("#progresspercent").width(300*( parseFloat(d)/counter ));
            });
        }, 1000);
    }
    else{
        alert("Specify a valid project name!");
    }
}
function projectlist(){
    $("#projects").html("");
    $.post("xlist.php", {}, 
        function(d){
            var v=d.split("|");
            for(var i in v){
                $("#projects").append("<a href='"+v[i]+"/apps/home.php?project="+v[i]+"' target='_blank'>"+v[i]+"</a><br><br>");
            }
        }
    );
}
</script>

<body onload="projectlist()">

<div id="title">
Maintenance Projects
</div>

<div id="frame">

<table>
<tr><td><b>Project</b></td><td><input id="prjname" type="text"></td></tr>
<tr><td><b>Password<b></td><td><input id="prjpwd" type="password"></td></tr>
</table>

<a id="bottone" href="javascript:" onclick="cloneproject()">Create/Update</a>

<div id="progressbar">
    <div id="progresspercent" style="position:absolute;left:0px;top:0px;width:0px;height:20px;background:red;font-size:14px;">
    </div>
</div>

<div id="titleattivi">
Active Projects List
</div>

<div id="projects"></div>

</div>

</body>
</html>
