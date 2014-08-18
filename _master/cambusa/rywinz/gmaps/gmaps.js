/****************************************************************************
* Name:            gmaps/gmaps.js                                           *
* Project:         Cambusa/ryWinz                                           *
* Version:         1.00                                                     *
* Description:     Multiple Document Interface                              *
* Copyright (C):   2013  Rodolfo Calzetti                                   *
* License GNU GPL: http://www.rudyz.net/cambusa/license.html                *
* Contact:         faustroll@tiscali.it                                     *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
function class_gmaps(settings,missing){
    var formid=RYWINZ.addform(this);
    var status=$.cookie("googlemaps");
    var objmap;
    if(status)
        status=$.parseJSON(status);
    else
        status={"zoom":_googleZoom,"lat":_googleLat,"lng":_googleLng};
    status["click"]=function(d){
        showinfo(d);
    }
    if(window.console)console.log(status);
    this._resize=function(w,h){
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
        status=objmap.status();
        if(status){
            $.cookie("googlemaps", _stringify(status), { expires : 10000 });
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
