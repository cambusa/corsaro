/*
 * ContextMenu - jQuery plugin for right-click context menus
 *
 * Author: Chris Domigan
 * Contributors: Dan G. Switzer, II
 *               Rodolfo Calzetti
 * Parts of this plugin are inspired by Joern Zaefferer's Tooltip plugin
 *
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * Version: r2
 * Date: 16 July 2007
 *
 * For documentation visit http://www.trendskitchens.co.nz/jquery/contextmenu/
 *
 */

(function($) {
    var menu, shadow, trigger, content, hash, currentTarget;
    var currobj;
    var defaults = {
    menuStyle: {
        listStyle: 'none',
        padding: '1px',
        margin: '0px',
        backgroundColor: '#fff',
        border: '1px solid #999',
        width: '100px'
    },
    itemStyle: {
        margin: '0px',
        color: '#000',
        display: 'block',
        cursor: 'default',
        padding: '3px',
        border: '1px solid #fff',
        backgroundColor: 'transparent'
    },
    itemHoverStyle: {
        border: '1px solid #0a246a',
        backgroundColor: '#b6bdd2'
    },
    eventPosX: 'pageX',
    eventPosY: 'pageY',
    shadow : true,
    onContextMenu: null,
    onShowMenu: null
 	};

    $.fn.contextMenu = function(id, options) {
        var propobj=this;
        this.propid="jqContextMenu";
        this.curritem=0;
        if (!menu) {                                      // Create singleton menu
            menu = $('<div id="'+this.propid+'" class="contextMenu"></div>')
            .hide()
            .css({position:'absolute', zIndex:'500'})
            .appendTo('body')
            .bind('click', function(e) {
                e.stopPropagation();
            });
            $(menu).keydown(
                function(k){
                    var list=$("#"+currobj.propid+" a[href]");
                    switch(k.which){
                    case 38:
                        if(currobj.curritem>0){
                            currobj.curritem-=1;
                            list[currobj.curritem].focus();
                        }
                        k.stopPropagation();
                        break;
                    case 40:
                        if(currobj.curritem<list.length-1){
                            currobj.curritem+=1;
                            list[currobj.curritem].focus();
                        }
                        k.stopPropagation();
                        break;
                    case 13:

                        break;
                    case 27:
                        castFocus(currobj.selector.substr(1));
                        hide();
                        k.preventDefault();
                        k.stopPropagation();
                    }
                }
            );
        }
        if (!shadow) {
            shadow = $('<div class="contextMenu"></div>')
            .css({backgroundColor:'#000',position:'absolute',opacity:0.2,zIndex:499})
            .appendTo('body')
            .hide();
        }
        hash = hash || [];
        hash.push({
            id : id,
            menuStyle: $.extend({}, defaults.menuStyle, options.menuStyle || {}),
            itemStyle: $.extend({}, defaults.itemStyle, options.itemStyle || {}),
            itemHoverStyle: $.extend({}, defaults.itemHoverStyle, options.itemHoverStyle || {}),
            bindings: options.bindings || {},
            shadow: options.shadow || options.shadow === false ? options.shadow : defaults.shadow,
            onContextMenu: options.onContextMenu || defaults.onContextMenu,
            onShowMenu: options.onShowMenu || defaults.onShowMenu,
            eventPosX: options.eventPosX || defaults.eventPosX,
            eventPosY: options.eventPosY || defaults.eventPosY
        });

        var index = hash.length - 1;
        $(this).bind('contextmenu', function(e) {
            // Check if onContextMenu() defined
            $("#"+hash[index].id+">ul>li").removeClass("contextDisabled");
            var bShowContext = (!!hash[index].onContextMenu) ? hash[index].onContextMenu(e) : true;
            if (bShowContext) display(index, this, e, options);
            return false;
        });

        function display(index, trigger, e, options) {
            var cur = hash[index];
            currobj=propobj;
            content = $('#'+cur.id).find('ul:first').clone(true);
            content.css(cur.menuStyle).find('li:not(.contextSeparator,.contextDisabled)').css(cur.itemStyle).hover(
                function() {
                    $(this).css(cur.itemHoverStyle);
                },
                function(){
                    $(this).css(cur.itemStyle);
                }
            ).find('img').css({verticalAlign:'middle',paddingRight:'2px'});

            content.css(cur.menuStyle).find('li.contextSeparator').css(cur.itemStyle).html('<div style="border-top:1px solid silver;height:1px;overflow:hidden;margin:2px 0px 0px"></div>');
            content.css(cur.menuStyle).find('li.contextDisabled').css(cur.itemStyle).find("a").removeAttr("href");

            // Send the content to the menu
            menu.html(content);

            // if there's an onShowMenu, run it now -- must run after content has been added
            // if you try to alter the content variable before the menu.html(), IE6 has issues
            // updating the content
            if(!!cur.onShowMenu){
                if(typeof e.pageX=="undefined"){
                    // ALT-2
                    try{
                        var p=$(propobj.selector).offset();
                        var w=$(propobj.selector).width();
                        var h=$(propobj.selector).height();
                        var l=(w-$(menu).width())/2;
                        var t=(h-$(menu).height())/2;
                        $(menu).css({left:p.left+l, top:p.top+t});
                        $(shadow).css({left:p.left+l+2, top:p.top+t+2});
                        propobj.curritem=0;
                        setTimeout(function(){
                            $("#"+propobj.propid+" a:first[href]").focus();
                        }, 500);
                    }catch(e){}
                }
                menu=cur.onShowMenu(e, menu);
            }
            $.each(cur.bindings, function(id, func) {
                $('#'+id, menu).bind('click', function(e) {
                    if(!$('#'+id).hasClass("contextDisabled")){
                        if(RYBOX)
                            RYBOX.setfocus(propobj.selector.substr(1));
                        hide();
                        func(trigger, currentTarget);
                    }
                });
            });
            // Nascondo tutti i menu aperti
            $('div.contextMenu').hide();
            // Correttore per menù fuori bordo
            var dx=0, dy=0;
			var w=$(window).width();
            var h=$(window).height();
			var x=e[cur.eventPosX]+menu.width();
            var y=e[cur.eventPosY]+menu.height();
            if(w<x+5)
                dx=x-w+5;
            if(h<y+5)
                dy=y-h+5;
			var l=e[cur.eventPosX]-dx;
			var t=e[cur.eventPosY]-dy;
            menu.css({'left':l,'top':t}).show();
            if(cur.shadow){
                shadow.css({width:menu.width(), height:menu.height(), left:l+2, top:t+2}).show();
            }
            $(document).one('click', hide);
        }
        function hide() {
            menu.hide();
            shadow.hide();
        }
        return this;
    };
    // Apply defaults
    $.contextMenu = {
        defaults : function(userDefaults) {
            $.each(userDefaults, function(i, val) {
                if (typeof val == 'object' && defaults[i]) {
                    $.extend(defaults[i], val);
                }
                else defaults[i] = val;
            });
        }
    };
})(jQuery);

$(function() {
    $('div.contextMenu').hide();
});