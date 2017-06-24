/****************************************************************************
* Name:            jquery.desktop.ry.js                                     *
* Original Name:   jquery.desktop.js                                        *
* Copyright (C):   Nathan Smith                                             *
*                                                                           *
* Modified by Rodolfo Calzetti and renamed to prevent overwriting           *
****************************************************************************/
var JQD = (function($, window, document, undefined) {
  // Expose innards of JQD.
  return {
    go: function() {
      for (var i in JQD.init) {
        JQD.init[i]();
      }
    },
    init: {
      frame_breaker: function() {
        if (window.location !== window.top.location) {
          window.top.location = window.location;
        }
      },
      //
      // Initialize the desktop.
      //
      desktop: function() {
        // Alias to document.
        var d = $(document);

        // Cancel mousedown.
        d.mousedown(function(ev) {
          var tags = ['a', 'button', 'input', 'select', 'textarea', 'tr'];

          if (!$(ev.target).closest(tags).length) {
            JQD.util.clear_active();
            ev.preventDefault();
            ev.stopPropagation();
          }
        });

        // Cancel right-click.
        d.on('contextmenu', function(ev) {
          // rudyz
          //return false;
          if(ev)
            return $(ev.target).is("input");
          else
            return false;
        });

        /* Rudyz
        // Relative or remote links?
        d.on('click', 'a', function(ev) {
          var url = $(this).attr('href');
          this.blur();

          if (url.match(/^#/)) {
            ev.preventDefault();
            ev.stopPropagation();
          }
          else {
            $(this).attr('target', '_blank');
          }
        });
        */

        // Make top menus active.
        d.on('mousedown', 'a.menu_trigger', function() {
          if ($(this).next('ul.menu').is(':hidden')) {
            JQD.util.clear_active();
            $(this).addClass('active').next('ul.menu').show();
          }
          else {
            JQD.util.clear_active();
          }
        });

        // Transfer focus, if already open.
        d.on('mouseenter', 'a.menu_trigger', function() {
          if ($('ul.menu').is(':visible')) {
            JQD.util.clear_active();
            $(this).addClass('active').next('ul.menu').show();
          }
        });

        // Cancel single-click.
        d.on('mousedown', 'a.icon', function() {
          // Highlight the icon.
          JQD.util.clear_active();
          $(this).addClass('active');
        });

        // Respond to double-click.
        d.on('dblclick', 'a.icon', function() {
          // Get the link's target.
          var x = $(this).attr('href');
		  var y="#window_"+x.substr(11);

          // Show the taskbar button.
          if ($(x).is(':hidden')) {
            $(x).remove().appendTo('#dock');
            $(x).show('fast');
          }

          // Bring window to front.
          JQD.util.window_flat();
          $(y).addClass('window_stack').show();
          
          // Title management
          JQD.util.window_title(y.substr(8));
        });

        // Hide menù on tools click
		d.on('click', 'a.winz-menu', function() {
		  JQD.util.clear_active();
        });


        // Make icons draggable.
        d.on('mouseenter', 'a.icon', function() {
          $(this).off('mouseenter').draggable({
            revert: true,
            containment: 'parent'
          });
        });

        // Taskbar buttons.
        //d.on('click', '#dock a', function() { // Rudyz
        d.on('mousedown', '#dock a', function() {
          // Get the link's target.
          var x = $($(this).attr('hrefx'));

          // Hide, if visible.
          //if (x.is(':visible')) {   // Rudyz
          //  x.hide();
          //}
          //else {
            // Bring window to front.
            JQD.util.window_flat();
            x.show().addClass('window_stack');

            // mosca
            var h=$(this).attr('hrefx');
            JQD.util.window_title(h.substr(8));
          //}
        });

        // Focus active window.
        d.on('mousedown', 'div.window', function() {
          // Bring window to front.
          if(!$(this).hasClass('window_stack')){     // Rudyz
              JQD.util.window_flat();
              $(this).addClass('window_stack');
              
              // mosca
              JQD.util.window_title(this.id.substr(7));
          }
        });

        // Make windows draggable.
        d.on('mouseenter', 'div.window', function() {
          $(this).off('mouseenter').draggable({
            // Confine to desktop.
            // Movable via top bar only.
            cancel: 'a',
            containment: 'parent',
            handle: 'div.window_top'
          }).resizable({
            containment: 'parent',
            minWidth: 400,
            minHeight: 200
          });
        });

        // Double-click top bar to resize, ala Windows OS.
        d.on('dblclick', 'div.window_top', function() {
          JQD.util.window_resize(this);
          // Gestione resize
          raiseResize(this.id.substr(4));
        });

        // Double click top bar icon to close, ala Windows OS.
        d.on('dblclick', 'div.window_top img', function() {
          // Traverse to the close button, and hide its taskbar button.
          $($(this).closest('div.window_top').find('a.window_close').attr('href')).hide('fast');

          // Close the window itself.
          $(this).closest('div.window').hide();

          // Stop propagation to window's top bar.
          return false;
        });

        // Minimize the window.
        d.on('click', 'a.window_min', function() {
          $(this).closest('div.window').hide();
        });

        // Maximize or restore the window.
        d.on('click', 'a.window_resize', function() {
          JQD.util.window_resize(this);
          // Gestione resize
          raiseResize(this.id.substr(7));
        });

        // Close the window.
        d.on('click', 'a.window_close', function() {
          // Rudyz
          //$(this).closest('div.window').hide();
          //$($(this).attr('href')).hide('fast');
          var h=$(this).attr('href');
          var id=h.substr(11);
          // mosca
          if(window.console&&_sessioninfo.debugmode)console.log("Chiusura "+id);
          RYWINZ.FormClose(id);
          /*
          var ret=raiseUnload(id);
          if(ret!==false){
            $(this).closest('div.window').hide();
            $(h).hide('fast');
            if($("#icon_desk_"+id).length == 0){ // Se non ha icona sul desktop, lo rimuovo totalmente
                if(window.console&&_sessioninfo.debugmode)console.log("Rimozione "+id);
                $("#window_"+id).remove();
                $("#icon_dock_"+id).remove();
                RYWINZ.removeform(id);
            }
          }
          */
        });

        // Show desktop button, ala Windows OS.
        d.on('mousedown', '#show_desktop', function() {
          // If any windows are visible, hide all.
          if ($('div.window:visible').length) {
            $('div.window').hide();
          }
          else {
            // Otherwise, reveal hidden windows that are open.
            $('#dock li:visible a').each(function() {
              $($(this).attr('hrefx')).show();
            });
          }
        });

        $('table.data').each(function() {
          // Add zebra striping, ala Mac OS X.
          $(this).find('tbody tr:odd').addClass('zebra');
        });

        d.on('mousedown', 'table.data tr', function() {
          // Clear active state.
          JQD.util.clear_active();

          // Highlight row, ala Mac OS X.
          $(this).closest('tr').addClass('active');
        });
      },
      wallpaper: function() {
        // Add wallpaper last, to prevent blocking.
        // Rudyz
        if ($('#desktop').length) {
          if(_wallpaper)
            $('body').prepend('<img id="wallpaper" class="abs" src="_images/wallpaper.jpg" />');
        }
      }
    },
    util: {
      //
      // Clear active states, hide menus.
      //
      clear_active: function() {
        $('a.active, tr.active').removeClass('active');
        $('ul.menu').hide();
      },
      //
      // Zero out window z-index.
      //
      window_flat: function() {
        $('div.window').removeClass('window_stack');
      },
      //
      // Resize modal window.
      //
      // mosca
      // Title.
      //
      window_title: function(id){
        var o=_globalforms[id].options;
        var t=o.title;
        if(o.controls.actualBoolean() || id=="rudder")
          $("#WINZ_TITLE").html("");
        else
          $("#WINZ_TITLE").html(t);
      },
      window_resize: function(el) {
        // Nearest parent window.
        var win = $(el).closest('div.window');

        // Is it maximized already?
        if (win.hasClass('window_full')) {
          // Restore window position.
          win.removeClass('window_full').css({
            'top': win.attr('data-t'),
            'left': win.attr('data-l'),
            'right': win.attr('data-r'),
            'bottom': win.attr('data-b'),
            'width': win.attr('data-w'),
            'height': win.attr('data-h')
          });
        }
        else {
          win.attr({
            // Save window position.
            'data-t': win.css('top'),
            'data-l': win.css('left'),
            'data-r': win.css('right'),
            'data-b': win.css('bottom'),
            'data-w': win.css('width'),
            'data-h': win.css('height')
          }).addClass('window_full').css({
            // Maximize dimensions.
            'top': '0',
            'left': '0',
            'right': '0',
            'bottom': '0',
            'width': '100%',
            'height': '100%'
          });
        }

        // Bring window to front.
        JQD.util.window_flat();
        win.addClass('window_stack');
      }
    }
  };
// Pass in jQuery.
})(jQuery, this, this.document);

//
// Kick things off.
//
jQuery(document).ready(function() {
  JQD.go();
});
