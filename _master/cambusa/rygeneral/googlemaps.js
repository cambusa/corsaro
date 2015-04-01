/****************************************************************************
* Name:            googlemaps.js                                            *
* Project:         Cambusa/ryGeneral                                        *
* Version:         1.69                                                     *
* Description:     Global functions and variables                           *
* Copyright (C):   2015  Rodolfo Calzetti                                   *
*                  License GNU LESSER GENERAL PUBLIC LICENSE Version 3      *
* Contact:         https://github.com/cambusa                               *
*                  postmaster@rudyz.net                                     *
****************************************************************************/
(function($,missing) {
    $.extend(true,$.fn, {
		gmap:function(settings){
            var propmap;
            var propzoom=3;
            var proplat=0;
            var proplng=0;
			var propobj=this;
			var propname=$(this).attr("id");
            
            if(settings.zoom!=missing){propzoom=settings.zoom}
            if(settings.lat!=missing){proplat=settings.lat}
            if(settings.lng!=missing){proplng=settings.lng}
            
            gmap_init();
            
            this.status=function(){
                var c=propmap.getCenter();
                return {"zoom":propmap.getZoom(),"lat":c.lat(),"lng":c.lng()};
            }
            
            function gmap_init(){
                var mapOptions={
                    zoom: propzoom,
                    center: new google.maps.LatLng(proplat,proplng),
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
                $("#"+propname).css({"width":"100%","height":"100%"});
                propmap=new google.maps.Map(document.getElementById(propname), mapOptions);
                google.maps.event.addListener(propmap, "rightclick", function(event) {
                    var geocoder = new google.maps.Geocoder();
                    geocoder.geocode({'latLng': event.latLng}, function(results, status) {
                        if(status == google.maps.GeocoderStatus.OK) {
                            var d={num:"",addr:"",city:"",code:"",province:"",region:"",country:""};
                            var comp=results[0]['address_components'];
                            for(var i in comp){
                                var types=comp[i]["types"];
                                for(var j in types){
                                    switch(types[j]){
                                    case "street_number":
                                        d.num=comp[i]['short_name'];
                                        break;
                                    case "route":
                                        d.addr=comp[i]['short_name'];
                                        break;
                                    case "postal_code":
                                        d.code=comp[i]['short_name'];
                                        break;
                                    case "locality":
                                        d.city=comp[i]['short_name'];
                                        break;
                                    case "administrative_area_level_1":
                                        d.region=comp[i]['short_name'];
                                        break;
                                    case "administrative_area_level_2":
                                        d.province=comp[i]['short_name'];
                                        break;
                                    case "country":
                                        d.country=comp[i]['short_name'];
                                        break;
                                    }
                                }
                            }
                            if(settings.click!=missing){settings.click(d)}
                        }
                    });
                });
            }
			return this;
		}
	});
})(jQuery);
