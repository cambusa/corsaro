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
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta http-equiv="x-ua-compatible" content="ie=EmulateIE9, chrome=1" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Manutenzione Script</title>

<style>
.mirror-conteiner{position:relative;display:none;}
.mirror-tab{position:absolute;top:100px;left:20px;display:none;}
.mirror-title{font-size:18px;height:25px;}
.mirror-tabtitle{font-size:18px;height:40px;}
.mirror-button{font-size:12px}
.mirror-label{font-size:12px}
.mirror-selected{font-weight:bold;}
.mirror-list{width:150px;}
.mirror-count{width:15px;}
.mirror-result,td{white-space:nowrap}

body{font-family:verdana,sans-serif; font-size:10px; margin:0px; overflow:hidden;}

</style>

<script type='text/javascript' src='../jquery/jquery.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.core.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.datepicker.js' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.contextmenu.js?ver=<?php print $cacheversion ?>' ></script>
<script type='text/javascript' src='../jquery/jquery.ui.widget.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.button.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.mouse.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.draggable.js'></script>
<script type='text/javascript' src='../jquery/jquery.ui.mousewheel.js'></script>
<script type='text/javascript' src='../jquery/jquery.cookie.js' ></script>

<script type='text/javascript' src='../rygeneral/rygeneral.js?ver=<?php print $cacheversion ?>' ></script>

<link rel="stylesheet" href="../codemirror/doc/docs.css">
<link rel="stylesheet" href="../codemirror/lib/codemirror.css">
<link rel="stylesheet" href="../codemirror/addon/fold/foldgutter.css">
<link rel="stylesheet" href="../codemirror/addon/dialog/dialog.css">
<link rel="stylesheet" href="../codemirror/theme/monokai.css">
<link rel="stylesheet" href="../codemirror/hint/show-hint.css">

<script src="../codemirror/lib/codemirror.js"></script>
<script src="../codemirror/addon/search/searchcursor.js"></script>
<script src="../codemirror/addon/search/search.js"></script>
<script src="../codemirror/addon/dialog/dialog.js"></script>
<script src="../codemirror/addon/edit/matchbrackets.js"></script>
<script src="../codemirror/addon/edit/closebrackets.js"></script>
<script src="../codemirror/addon/comment/comment.js"></script>
<script src="../codemirror/addon/wrap/hardwrap.js"></script>
<script src="../codemirror/addon/fold/foldcode.js"></script>
<script src="../codemirror/addon/fold/brace-fold.js"></script>
<script src="../codemirror/addon/hint/show-hint.js"></script>

<!-- <script src="../codemirror/addon/hint/xml-hint.js"></script>
<script src="../codemirror/mode/xml/xml.js"></script> -->

<script src="../codemirror/addon/hint/javascript-hint.js"></script>
<script src="../codemirror/mode/javascript/javascript.js"></script>

<!-- <script src="../codemirror/mode/css/css.js"></script> -->
<!-- <script src="../codemirror/mode/htmlmixed/htmlmixed.js"></script> -->
<!-- <script src="../codemirror/mode/clike/clike.js"></script> -->
<!-- <script src="../codemirror/mode/php/php.js"></script> -->
<script src="../codemirror/keymap/sublime.js"></script>

<style type="text/css">
.CodeMirror {border-top: 1px solid #eee; border-bottom: 1px solid #eee; line-height: 1.3; height:600px;}
.CodeMirror-linenumbers { padding: 0 8px; }
.cm-s-monokai span{font-family:monospace; font-size:16px}
</style>

<script>

var objmirror;
var propindent=4;

$(document).ready(function(){
    makeeditor();
});

function makeeditor(missing){
    try{

      var dummy = {
        attrs: {
          color: ["red", "green", "blue", "purple", "white", "black", "yellow"],
          size: ["large", "medium", "small"],
          description: null
        },
        children: []
      };
    
        var tags = {
            "!top": ["top"],
            "!attrs": {
              id: null,
              class: ["A", "B", "C"]
            },
            top: {
              attrs: {
                lang: ["en", "de", "fr", "nl"],
                freeform: null
              },
              children: ["animal", "plant"]
            },
            animal: {
              attrs: {
                name: null,
                isduck: ["yes", "no"]
              },
              children: ["wings", "feet", "body", "head", "tail"]
            },
            plant: {
              attrs: {name: null},
              children: ["leaves", "stem", "flowers"]
            },
            wings: dummy, feet: dummy, body: dummy, head: dummy, tail: dummy,
            leaves: dummy, stem: dummy, flowers: dummy
        };        
        
        
        function completeAfter(cm, pred) {
            var cur = cm.getCursor();
            if (!pred || pred()) setTimeout(function() {
              if (!cm.state.completionActive)
                cm.showHint({completeSingle: false});
            }, 100);
            return CodeMirror.Pass;
        }

        function completeIfAfterLt(cm) {
            return completeAfter(cm, function() {
              var cur = cm.getCursor();
              return cm.getRange(CodeMirror.Pos(cur.line, cur.ch - 1), cur) == "<";
            });
        }

        function completeIfInTag(cm) {
            return completeAfter(cm, function() {
              var tok = cm.getTokenAt(cm.getCursor());
              if (tok.type == "string" && (!/['"]/.test(tok.string.charAt(tok.string.length - 1)) || tok.string.length == 1)) return false;
              var inner = CodeMirror.innerMode(cm.getMode(), tok.state).state;
              return inner.tagName;
            });
        }
        
        objmirror=CodeMirror(document.getElementById("codescript"), {
            lineNumbers:false,
            mode:"javascript",
            indentUnit:propindent,
            indentWithTabs:true,
            tabSize:propindent,
            keyMap:"sublime",
            autoCloseBrackets:true,
            matchBrackets:true,
            showCursorWhenSelecting:true,
            theme:"monokai",
            extraKeys: {
                "'<'": completeAfter,
                "'/'": completeIfAfterLt,
                "' '": completeIfInTag,
                "'='": completeIfInTag,            
                "Ctrl-Space": "autocomplete"
            },
            hintOptions: {schemaInfo: tags}
        });
        
        bodyresize();
        
        objmirror.setValue("");
        objmirror.setOption("mode", "javascript");
        objmirror.refresh();
        objmirror.doc.clearHistory();
        
    }
    catch(e){
        if(window.console){console.log(e.message)}
    }
}

function bodyresize(){
    var w=$("body").width()-1;
    $("#codescript").width(w);
}

function getvalue(v){
    return objmirror.getValue().replace(/\t/g, "          ".subright(propindent));
}

function setvalue(v){
    objmirror.setValue(v);
    objmirror.refresh();
    objmirror.doc.clearHistory();
}

function setmode(v){
    objmirror.setOption("mode", v);
    objmirror.refresh();
}

function setindent(v){
    propindent=v;
    objmirror.setOption("indentUnit", v);
    objmirror.setOption("tabSize", v);
    objmirror.refresh();
}

</script>

</head>

<body onresize="bodyresize()">

<div id="codescript"></div>

</body>
</html>
