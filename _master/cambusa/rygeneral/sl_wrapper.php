<?php
/****************************************************************************
* Name:            sl_wrapper.php                                           *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
// SOURCE SILVERLIGHT
if(isset($_GET["source"]))
    $source=$_GET["source"];
else
    $source="";

// FORMID
if(isset($_GET["formid"]))
    $formid=$_GET["formid"];
else
    $formid="";

// ROOT
if(isset($_GET["root"]))
    $root=$_GET["root"];
else
    $root="";

// ENVIRON
if(isset($_GET["env"]))
    $environ=$_GET["env"];
else
    $environ="";

// USERID
if(isset($_GET["userid"]))
    $userid=$_GET["userid"];
else
    $userid="";

// SESSIONID
if(isset($_GET["sessionid"]))
    $sessionid=$_GET["sessionid"];
else
    $sessionid="";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<!-- saved from url=(0014)about:internet -->
<head>
    <title>SilverlightApplication1</title>
    <style type="text/css">
    html, body {
	    height: 100%;
	    overflow: auto;
    }
    body {
	    padding: 0;
	    margin: 0;
    }
    #silverlightControlHost {
	    height: 100%;
	    text-align:center;
    }
    </style>
    
    <script type="text/javascript">
        var _formid="<?php print $formid ?>";
        function _winzBringToFront(){
            window.parent.winzBringToFront(_formid);
        }
        function _winzGetUnsaved(){
            return window.parent.RYWINZ.modified(_formid);
        }
        function _winzSetUnsaved(v){
            window.parent.RYWINZ.modified(_formid, v);
        }
        function _winzProgress(){
            window.parent.winzProgress(_formid);
        }
        function _winzTimeoutMess(mess, type){
            window.parent.winzTimeoutMess(_formid, type, mess);
        }
        function _winzClearMess(){
            window.parent.winzClearMess(_formid);
        }
        function _winzMereMessage(mess, col){
            window.parent.winzMereMessage(_formid, mess, col);
        }
        function _winzWriteConsole(text){
            if(window.console){console.log(text)}
        }
        function _winzBaseURL(){
            return "<?php print $root ?>";
        }
        function _winzEnviron(){
            return "<?php print $environ ?>";
        }
        function _winzUserID(){
            return "<?php print $userid ?>";
        }
        function _winzSessionID(){
            return "<?php print $sessionid ?>";
        }
        function onSilverlightError(sender, args) {
            var appSource = "";
            if (sender != null && sender != 0) {
              appSource = sender.getHost().Source;
            }
            
            var errorType = args.ErrorType;
            var iErrorCode = args.ErrorCode;

            if (errorType == "ImageError" || errorType == "MediaError") {
              return;
            }

            var errMsg = "Unhandled Error in Silverlight Application " +  appSource + "\n" ;

            errMsg += "Code: "+ iErrorCode + "    \n";
            errMsg += "Category: " + errorType + "       \n";
            errMsg += "Message: " + args.ErrorMessage + "     \n";

            if (errorType == "ParserError") {
                errMsg += "File: " + args.xamlFile + "     \n";
                errMsg += "Line: " + args.lineNumber + "     \n";
                errMsg += "Position: " + args.charPosition + "     \n";
            }
            else if (errorType == "RuntimeError") {           
                if (args.lineNumber != 0) {
                    errMsg += "Line: " + args.lineNumber + "     \n";
                    errMsg += "Position: " +  args.charPosition + "     \n";
                }
                errMsg += "MethodName: " + args.methodName + "     \n";
            }

            throw new Error(errMsg);
        }
    </script>
</head>
<body onmousedown="_winzBringToFront()">
    <form id="form1" runat="server" style="height:100%">
    <div id="silverlightControlHost">
        <object data="data:application/x-silverlight-2," type="application/x-silverlight-2" width="100%" height="100%">
		  <param name="source" value="<?php print $source ?>"/>
		  <param name="onError" value="onSilverlightError" />
		  <param name="background" value="white" />
		  <param name="minRuntimeVersion" value="5.0.61118.0" />
		  <param name="autoUpgrade" value="true" />
          <param name="windowless" value="true" />
		  <a href="http://go.microsoft.com/fwlink/?LinkID=149156&v=5.0.61118.0" style="text-decoration:none">
 			  <img src="http://go.microsoft.com/fwlink/?LinkId=161376" alt="Get Microsoft Silverlight" style="border-style:none"/>
		  </a>
	    </object><iframe id="_sl_historyFrame" style="visibility:hidden;height:0px;width:0px;border:0px"></iframe></div>
    </form>
</body>
</html>
