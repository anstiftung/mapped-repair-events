// -----------------------------------
// JQuery Cookies Message Plugin
// Version 1.0.0 - 28th May 2015
// http://#
//
// Written by Giuseppe Garbin
// http://www.giuseppegarbin.com/
//
// Released under MIT License
// http://opensource.org/licenses/MIT
//
// ---------------------
// Index of JQuery.Cookies-Message.js
//
// 001 - Default settings
// 002 - Initialize
// 003 - Check user's technical cookie
// 004 - Get and Set cookie functions
// 005 - Accept link event handler



;( function ( $ ) {

    $.CookiesMessage = function ( options ) {


        // ----------------
        // 001 - Default settings

        var defaults = {
            messageText: "We use technical and analytics cookies to ensure that we give you the best experience on our website.",
            messageBg: "#151515",                               // Message box background color
            messageColor: "#FFFFFF",                        // Message box text color
            messageLinkColor: "#F0FFAA",                // Message box links color
            closeEnable: true,                                  // Show the close icon
            closeColor: "#444444",                          // Close icon color
            closeBgColor: "#000000",                        // Close icon background color
            acceptEnable: true,                                 // Show the Accept button
            acceptText: "Accept & Close",               // Accept button text
            infoEnable: true,                                       // Show the More Info button
            infoText: "More Info",                          // More Info button text
            infoUrl: "#",                                               // More Info button URL
            cookieExpire: 180                                       // Cookie expire time (days)
        };
        
        options = $.extend(defaults, options);

        var cookieName  = location.host;
        var cookieValue = "Cookies policy accepted";
        var cookiePath  = "/";



        // ----------------
        // 002 - Initialize

    function inizialize(options) {
        //== Message composition
        var partClose = '';
        if (options.closeEnable == true) { partClose += '<a href="#" id="band-cookies-close" style="background-color:'+ options.closeBgColor +';"><svg version="1.1" id="band-cookies-close-icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="16px" height="16px" viewBox="248.5 248.5 15 15" enable-background="new 248.5 248.5 15 15" xml:space="preserve" ><polygon id="x-mark-icon" points="263.5,260.876 258.621,255.999 263.499,251.121 260.876,248.5 256,253.377 251.122,248.5 248.5,251.121 253.378,255.999 248.5,260.878 251.121,263.5 256,258.62 260.879,263.499" style="fill:'+ options.closeColor +';"/></svg></a>'; };
            var partLinks = '';
            if (options.acceptEnable == true) { partLinks += '<a href="#" id="band-cookies-ok">'+ options.acceptText +'</a>'; };
            if (options.infoEnable == true) { partLinks += '<a href="'+ options.infoUrl +'" id="band-cookies-info">'+ options.infoText +'</a>'; };
            var displayMessage = '<div id="band-cookies"><p>'+ options.messageText + partLinks +'</p>'+ partClose +'</div>';
            $("body").prepend(displayMessage);
            $("#band-cookies").hide().slideDown();

            //== Custom style
            $("#band-cookies").css({ "background-color":options.messageBg, "color":options.messageColor });
            $("#band-cookies p a").css({ "color":options.messageLinkColor });
    }



        // ----------------
        // 003 - Check user's technical cookie

        var mycookie = getCookie(cookieName);
        if(!mycookie) {
           inizialize(options);
        }



        // ----------------
        // 004 - Get and Set cookie functions


        function Trim(strValue) {
          return strValue.replace(/^\s+|\s+$/g, '');
        }


        function getCookie(cookieName) {
          var result = false;
          if(document.cookie) {
            var mycookieArray = document.cookie.split(';');
            for(i=0; i<mycookieArray.length; i++) {
              var mykeyValue = mycookieArray[i].split('=');
              if(Trim(mykeyValue[0]) == cookieName) result = mykeyValue[1];
            }
          }
          return result;
        }


        function setCookie(cname, cvalue, cexpire, cpath) {
            var d = new Date();
        d.setTime(d.getTime() + (cexpire*24*60*60*1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + "; " + expires + "; path="+ cpath +";";
        }



        // ----------------
        // 005 - Accept link event handler

        $('#band-cookies-ok').on( 'click', function ( event ) {
        event.preventDefault();
        setCookie(cookieName, cookieValue, options.cookieExpire, cookiePath);
            $("#band-cookies").slideToggle();
        });

        $('#band-cookies-close').on( 'click', function ( event ) {
        event.preventDefault();
            $("#band-cookies").slideToggle();
        });

  
  };


})(jQuery);