/****************************************************************************
* Name:            gmaps/gmaps.js                                           *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.69                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_gmaps(settings,missing){
    var formid=RYWINZ.addform(this, settings);
    var status=$.cookie("googlemaps");
    var objmap;
    if(status)
        status=$.parseJSON(status);
    else
        status={"zoom":_systeminfo.maps.zoom,"lat":_systeminfo.maps.lat,"lng":_systeminfo.maps.lng};
    status["click"]=function(d){
        showinfo(d);
    }
    if(window.console)console.log(status);
    this._resize=function(){
        if(objmap){
            status=objmap.status();
            status["click"]=function(d){
                showinfo(d);
            }
            objmap=null;
        }
        objmap=$("#"+formid+"map").gmap(status);
    }
    this._unload=function(){
        if(objmap){
            status=objmap.status();
            if(status){
                $.cookie("googlemaps", $.stringify(status), { expires : 10000 });
            }
        }
    }
    function showinfo(d){
        var info="";
        info+="<textarea rows='5' style='border:none;resize:none;width:450px;height:100px;'>"
        info+=d.addr+", "+d.num+"\n";
        info+=d.code+" "+d.city+" "+d.province+"\n";
        info+=d.country+"\n";
        info+="</textarea>";
        winzMessageBox(formid, 
            {
                height:230,
                message:info,
                ok:"Chiudi"
            }
        );
    }
}
