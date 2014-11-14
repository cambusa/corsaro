<br/>
<div style="font-size:16px">Nuovo account</div>
<div style="position:relative">

    <div id="lbregemail"></div><div id="txregemail"></div>
    <div id="lbregname"></div><div id="txregname"></div>
    <div id="lbregsurname"></div><div id="txregsurname"></div>
    <div id="actionRegister"></div>

</div>

<script>
var _regappname="corsaro";
var _regenvname="crediti";
var _regrolename="admin";

$("#lbregemail").rylabel({left:0, top:20, caption:"Email"});
var txregemail=$("#txregemail").rytext({ 
    left:80,
    top:20, 
    maxlen:50
});

$("#lbregname").rylabel({left:0, top:50, caption:"Nome"});
var txregname=$("#txregname").rytext({ 
    left:80,
    top:50, 
    maxlen:50
});

$("#lbregsurname").rylabel({left:0, top:80, caption:"Cognome"});
var txregsurname=$("#txregsurname").rytext({ 
    left:80,
    top:80, 
    maxlen:50
});

$("#actionRegister").rylabel({
    left:300,
    top:82,
    caption:"Registrazione",
    button:true,
    flat:true,
    click:function(o){
        if(txregemail.value().replace(/ /,"")==""){
            alert("Email obbligatoria!");
        }
        else if(txregname.value().replace(/ /,"")=="" || txregsurname.value().replace(/ /,"")==""){
            alert("Inserire nome e cognome!");
        }
        else{
            if(confirm(RYBOX.babels("EGO_NEWACCOUNT"))){
                $.post("egorequest_new.php", 
                    {
                        "email":txregemail.value(),
                        "appname":_regappname,
                        "envname":_regenvname,
                        "rolename":_regrolename,
                        "custom":{
                            "nome":txregname.value(),
                            "cognome":txregsurname.value()
                        }
                    },
                    function(d){
                        try{
                            var v=$.parseJSON(d);
                            sysmessage(v.description,v.success);
                        }
                        catch(e){
                            alert(d);
                        }
                    }
                );
            }
        }
    }
});
</script>

