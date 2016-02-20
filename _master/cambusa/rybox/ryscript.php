<?php 
/****************************************************************************
* Name:            ryscript.php                                             *
* Project:         Cambusa/ryBox                                            *
* Version:         1.70                                                     *
* Description:     Masked input and other form controls                     *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/

if(isset($_GET["mode"]))
    $mode=$_GET["mode"];
else
    $mode="javascript";

if(isset($_GET["indent"]))
    $indent=intval($_GET["indent"]);
else
    $indent=4;    

if(isset($_GET["name"]))
    $name=$_GET["name"];
else
    $name="";

?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=EmulateIE9, chrome=1" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Manutenzione Script</title>

<script type='text/javascript' src='../jquery/jquery.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.core.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.datepicker.js' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.contextmenu.js' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.widget.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.button.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.mouse.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.draggable.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.mousewheel.js'></script>
<script type='text/javascript' src='../jquery/jquery.cookie.js' ></script>
<script type='text/javascript' src='../rygeneral/rygeneral.js'></script>

<link rel="stylesheet" href="../codemirror/doc/docs.css">
<link rel="stylesheet" href="../codemirror/lib/codemirror.css">
<link rel="stylesheet" href="../codemirror/theme/monokai.css">
<link rel="stylesheet" href="../codemirror/addon/hint/show-hint.css">
<link rel="stylesheet" href="../codemirror/addon/dialog/dialog.css">

<script src="../codemirror/lib/codemirror.js"></script>
<script src="../codemirror/addon/hint/show-hint.js"></script>
<script src="../codemirror/addon/search/searchcursor.js"></script>
<script src="../codemirror/addon/search/search.js"></script>
<script src="../codemirror/addon/dialog/dialog.js"></script>
<script src="../codemirror/addon/edit/matchbrackets.js"></script>
<!-- <script src="../codemirror/addon/edit/closebrackets.js"></script> -->
<script src="../codemirror/addon/comment/comment.js"></script>
<script src="../codemirror/addon/wrap/hardwrap.js"></script>
<script src="../codemirror/addon/fold/foldcode.js"></script>
<script src="../codemirror/addon/fold/brace-fold.js"></script>
<script src="../codemirror/addon/hint/show-hint.js"></script>
<script src="../codemirror/addon/hint/anyword-hint.js"></script>

<?php 
    if($mode=="xml"){
?>
<script src="../codemirror/addon/hint/xml-hint.js"></script>
<script src="../codemirror/mode/xml/xml.js"></script>
<?php 
    }
?>

<?php 
    if($mode=="javascript"){
?>
<script src="../codemirror/addon/hint/javascript-hint.js"></script>
<script src="../codemirror/mode/javascript/javascript.js"></script>
<?php 
    }
?>

<?php 
    if($mode=="php"){
?>
<script src="../codemirror/mode/php/php.js"></script>
<?php 
    }
?>

<?php 
    if($mode=="vbscript"){
?>
<script src="../codemirror/mode/vbscript/vbscript.js"></script>
<?php 
    }
?>

<?php 
    if($mode=="css"){
?>
<script src="../codemirror/mode/css/css.js"></script>
<?php 
    }
?>

<?php 
    if($mode=="html"){
?>
<script src="../codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script src="../codemirror/addon/hint/xml-hint.js"></script>
<script src="../codemirror/mode/xml/xml.js"></script>
<?php 
    }
?>

<?php 
    if($mode=="sql"){
?>
<script src="../codemirror/mode/sql/sql.js"></script>
<script src="../codemirror/addon/hint/sql-hint.js"></script>
<?php 
    }
?>

<style type="text/css">
body{background-color:#272822; font-family:monospace; font-size:16px;}
.CodeMirror {line-height: 1.3; height:100%}
.CodeMirror-linenumbers {padding: 0 8px;}
div.CodeMirror-code{bottom:0px;}
</style>

<script>

var objmirror;
var propindent=<?php print $indent ?>;
var propmode="<?php print $mode ?>";
var propname="<?php print $name ?>";

$(document).ready(function(){
    makeeditor();
});

function makeeditor(missing){
    try{
        function completeAfter(cm, pred) {
            var cur=cm.getCursor();
            if (!pred || pred()) setTimeout(function() {
              if (!cm.state.completionActive)
                cm.showHint({completeSingle: false});
            }, 100);
            return CodeMirror.Pass;
        }

        objmirror=CodeMirror(document.getElementById("codescript"), {
            lineNumbers: false,
            indentUnit:propindent,
            indentWithTabs:true,
            tabSize:propindent,
            autoCloseBrackets:true,
            matchBrackets:true,
            showCursorWhenSelecting:true,
            theme:"monokai",
            extraKeys: {
                "'.'": completeAfter,
                "'('": completeAfter,
                "Ctrl-Space": "autocomplete"
            },
            mode: {name: propmode, globalVars: true}
        });
      
        objmirror.on("change",
            function(){
                window.parent.globalobjs[propname].raisechanged();
            }
        );

        bodyresize();
        
        $("body").click(function(){
            objmirror.focus();
        });
        
        window.parent.globalobjs[propname].raiseload();
    }
    catch(e){
        if(window.console){console.log(e.message)}
    }
}

function bodyresize(){
    var w=$("body").width();
    var h=$("body").height();
    $("#codescript").width(w).height(h);
}

function getvalue(v){
    return objmirror.getValue().replace(/\t/g, "          ".subright(propindent));
}

function setvalue(v){
    objmirror.setValue(v);
    objmirror.refresh();
    objmirror.doc.clearHistory();
}

function setindent(v){
    propindent=v;
    objmirror.setOption("indentUnit", v);
    objmirror.setOption("tabSize", v);
    objmirror.refresh();
}

function setintellisense(v){
    CodeMirror.registerHelper("hint", propmode, function(cm, options){
        var cur=cm.getCursor();
        var curLine=cm.getLine(cur.line);
        var end=cur.ch;
        var start=end;
        var line=cur.line;
        
        var ws=[];
        var e=true;
        var pos=end;
        var w="";
        var ok=true;
        
        do{
            if(pos<=0){
                e=false;
                if(w!="")
                    ws.unshift([w, 0]);
            }
            else{
                var k=curLine.charAt(pos - 1);
                if(/\w/.test(k)){
                    // Carattere alpanumerico
                    w=k+w;
                    if(pos>1){
                        // Controllo se il carettere che precede crea spazi
                        k=curLine.charAt(pos - 2);
                        if(/\s/.test(k)){
                            // Aggiungo al percorso
                            ws.unshift([w, pos-1]);
                            w="";
                        }
                    }
                }
                else if(/[.(]/.test(k)){
                    if(w!=""){
                        ws.unshift([w, pos]);
                        w="";
                    }
                }
                else if(/[\s]/.test(k)){
                    if(w!=""){
                        ws.unshift([w, pos]);
                        w="";
                    }
                    e=false;
                    break;
                }
            }
            --pos;
        }while(e)
        
        var list = [];
        
        if(ok){
            var b=CopyLower(v);
            var curWord="";
            
            for(var i in ws){
                w=ws[i][0];
                if($.isset(b[ w.toLowerCase()])){
                    b=b[w.toLowerCase()][0];
                }
                else{
                    curWord=w;
                    start=ws[i][1];
                    break;
                }
            }
            if(typeof(b)=="object"){
                for(var n in b){
                    if(!curWord || n.substr(0, curWord.length)==curWord.toLowerCase()){
                        list.push(b[n][1]);
                    }
                }
            }
            else if(b!=""){
                list.push(b);
            }
        }

        return {
            list: list,
            from: CodeMirror.Pos(line, start),
            to: CodeMirror.Pos(line, end)
        }
    });    
}

function CopyLower(v){
    var c={};
    for(var n in v){
        if(typeof(v[n])=="object")
            c[n.toLowerCase()]=[CopyLower(v[n]), n];
        else
            c[n.toLowerCase()]=[v[n], n];
    }
    return c;
}

function setenabled(v){
    if(v)
        objmirror.setOption("readOnly", false);
    else
        objmirror.setOption("readOnly", true);
}

function setfocus(v){
    objmirror.focus();
}

</script>

</head>

<body onresize="bodyresize()">

<div id="codescript"></div>

</body>
</html>
