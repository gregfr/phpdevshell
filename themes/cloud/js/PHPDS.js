/**
 * PHPDevShell / Opensource PHP framework
 */

/*! These are the JS parts of PHPDevShell
 *
 * version 1.1.1
 *
 * @author greg <greg@phpdevshell.org>
*/





/**
 * This serves a namespace for PHPDevShell javascript utilities
 */
PHPDS = {};
PHPDS.remoteCallURL = document.URL;


/* this is "aload" by Pazguile (https://github.com/pazguille/aload) */
PHPDS.aload = function(t)
{
    "use strict";
    t = t || window.document.querySelectorAll("[data-aload]"), void 0 === t.length && (t = [t]);
    var a, e = 0, r = t.length;
    for (e; r > e; e += 1)a = t[e], a["LINK" !== a.tagName ? "src" : "href"] = a.getAttribute("data-aload"), a.removeAttribute("data-aload");
    return t
}

/**
 * An exception-like class to handle RemoteCall situations
 *
 * @date 20130413 (1.0) (greg) added
 *
 * @version 1.0
 * @author greg <greg@phpdevshell.org>
 *
 * @since 3.5 (PHPDevShell version 3.5)
 *
 * @param deferred
 * @param ajax
 * @constructor
 */
PHPDS.RemoteCallException = function (deferred, ajax)
{
    this.deferred = deferred;
    this.ajax = ajax;
    this.handled = false;
};

//PHPDS.RemoteCallException.prototype.

/**
 * a few URL-related utilities.
 *
 * based on code from Stack Overflow (http://stackoverflow.com/questions/901115/how-can-i-get-query-string-values/9402569#9402569)
 * @type {{}}
 */
PHPDS.url = {};

/**
 * Parse an URL to fetch the "search" (GET) parameters
 *
 * @version 1.0.1
 *
 * @version 1.0.1 (20140507) (greg) url defaults to current document url
 *
 * @param url
 * @returns {{}}
 */
PHPDS.url.decodeParams = function (url) {
    var args_enc, el, i, nameval, ret;
    if (!url) {
        url = document.URL;
    }
    ret = {};
    // use the DOM to parse the URL via an 'a' element
    el = document.createElement("a");
    el.href = url;
    // strip off initial ? on search and split
    args_enc = el.search.substring(1).split('&');
    for (i = 0; i < args_enc.length; i++) {
        // convert + into space, split on =, and then decode
        args_enc[i].replace(/\+/g, ' ');
        nameval = args_enc[i].split('=', 2);
        if (nameval[0]) {
            ret[decodeURIComponent(nameval[0])] = decodeURIComponent(nameval[1]);
        }
    }
    return ret;
};

/**
 * Inject (ie. add or overrides) "search" (GET) parameters into a URL
 *
 * @param params object of parameters
 * @param url string (optional) if empty is current URL is used
 * @returns string
 */
PHPDS.url.encodeParams = function (params, url) {
    var args_enc, el, name;
    if (!url) {
        url = document.URL;
    }
    el = document.createElement("a");
    el.href = url;
    args_enc = PHPDS.url.decodeParams(url);
    for (name in params) {
        if (params.hasOwnProperty(name)) {
            name = encodeURIComponent(name);
            params[name] = encodeURIComponent(params[name]);
            args_enc[name] = params[name];
        }
    }
    el.search = '?' + $.param(args_enc);
    return el.href;
};

PHPDS.url.relocate = function(url, params) {
    if ((params === undefined) && (url !== null) && (typeof url == "object")) {
        params = url;
        url = null;
    }
    document.location = PHPDS.url.encodeParams(params, url);
};


/**
 * Call a PHP function
 *
 * The function must be handled by the current controller (method name is "ajax" + functionName)
 *
 * Parameters are preferably passed through POST for two reasons:
 * - GET data maybe polluted for other reasons (sessions handling, ...) where POST are always under control
 * - GET data appear in URL therefore are limited in size and charset
 * @see http://www.cs.tut.fi/~jkorpela/forms/methods.html
 *
 * Note: only application parameters are sent through GET/POST, handling data such as function name sent though headers
 *
 * From version 2.0 (PHPDevShell 3.5), the failure callback of the deferred is passed a PHPDS.RemoteCallException object ;
 *      it can set this object's field to "true" to prevent the top-level exception handler to kick in
 *
 * The resolve callback of the deferred is passed:
 *      - the result *data* returned by the ajax call
 *      - the textual state
 *      - the ajax object
 *
 * Caution: prior to PHP 5 the parameters fed to the PHP function are given IN ORDER, NOT BY NAME
 *
 * @version 2.0.1
 *
 * @date 20130418 (2.0.1) (greg) using our own URL parser to avoid dependency
 * @date 20130413 (2.0) (greg) using double deferred ; moved to own namespace
 *
 * @author greg <greg@phpdevshell.org>
 *
 * @param functionName string, the name of the function to call (ie. method "ajax"+functionName of the controller)
 * @param params array, data to be serialized and sent via POST
 * @param extParams array (optional), data to be serialized and sent via GET
 *
 * @return deferred
 *
 * TODO: possibility of calling a method from another controller
 *
 */
PHPDS.remoteCall = function (functionName, params, extParams)
{
    var url = PHPDS.remoteCallURL;
    if (extParams) {
        //url += ((url.indexOf('?') >= 0) ? '&' : '?') + $.param(extParams);
//        url = URI(url).addQuery(extParams).href();
        url = PHPDS.url.encodeParams(extParams, url);
    }
    return new $.Deferred(function () {
        var self_deferred = this;
        $.when(
            $.ajax({
                url: url,
                dataType: 'json',
                data: params,
                type: 'POST',
                headers: {'X-Requested-Type': 'json', 'X-Remote-Call': functionName},
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-Requested-Type', 'json');
                    xhr.setRequestHeader('X-Remote-Call', functionName);
                }
            }).done(function(result, state, self_ajax) {
               self_deferred.resolve(result, state, self_ajax);
            }).fail(function (self_ajax) {
                PHPDS.errorHandler(new PHPDS.RemoteCallException(self_deferred, self_ajax));
            })
        );
    });
};

/**
 * This is the top-level exception handler, it can be called in several ways
 *
 * First, with no parameter, it installs itself - you MUST do that
 * Second, with a single function, it install this function as an user exception handler
 * Third, with single custom exception (currently only RemoteCall), it deals with it
 *
 * Else it's the actual error handler, called when an error occurs
 *
 * @version 1.0
 *
 * @date 20130413 (1.0) (greg) added
 *
 * @author greg <greg@phpdevshell.org>
 *
 * @since 3.5
 *
 * TODO: fetch backtrace, if possible
 * TODO: send feedback to the server, if possible
 *
 * @param message
 * @param url
 * @param line
 * @param object
 * @returns true
 */
PHPDS.errorHandler = function (message, url, line, object)
{
    // first case, no parameter, initial setup
    if (!message) {
        window.onerror = function (message, url, line) {
            return PHPDS.errorHandler(message, url, line);
        };
        return true;
    }
    // second case, a user error handling function
    else if (typeof message == 'function') {
        this.userErrorHandler = message;
        return true;
    }
    // third case, a an exception from RemoteCall
    else if (message instanceof PHPDS.RemoteCallException) {
        /* @var PHPDS.RemoteCallException message */
        var r = message.deferred.reject(message);
        if (message.handled) {
            return true;
        } else {
            object = message;
            message = 'Unhandled RemoteCall exception: ';
        }
    }

    if (this.userErrorHandler) {
        return this.userErrorHandler(message, url, line, object);
    } else {
        if (console && console.log) {
            console.log('PHPDS.errorHandler ', message, ' / ', url, ' / ', line, ' / ', object);
        }
    }
    return true;
};


/**
 * Apply default formatting to the objects inside the given root element (root element is optional, defaults to BODY)
 */
PHPDS.documentReady = function (root) {
    PHPDS.errorHandler();

    if (!root) {
        root = $('BODY');
    }

    PHPDS.aload();

    /* Navigation
     *************************************************/
    /* Hover over selectors */
    $("#nav li a, #bread li a, .cp-selector, .hover, .loginlink", root).hover(
        function () {
            $(this).addClass("ui-state-hover");
        },
        function () {
            $(this).removeClass("ui-state-hover");
        });
    /* General theming. */
    $(".active", root).addClass("ui-state-active");
    $(".cp-selector, .hover, .loginlink", root).addClass("ui-state-default ui-corner-all");

    /* Navigation. */
    $("#nav > li > a, ul#bread > li > a", root).addClass("ui-state-default ui-corner-all");
    $("#nav li a, #bread li a", root).hover().addClass("ui-corner-all");
    $("#nav ul, #bread ul, fieldset", root).addClass("ui-widget-content ui-corner-all");
    $("#nav > .current a, #bread > .current a", root).addClass("ui-state-active ui-corner-all");
    $("#nav .grandparent .nav-grand, #bread .grandparent .nav-grand", root).addClass("ui-icon ui-icon-triangle-1-s left");
    $("#nav ul .parent .nav-parent, #bread ul .parent .nav-parent", root).addClass("ui-icon ui-icon-triangle-1-e right");
    $("#bread .jump span", root).addClass("ui-icon ui-icon-calculator left");
    $("#bread .home span", root).addClass("ui-icon ui-icon-home left");
    $("#bread .up span", root).addClass("ui-icon ui-icon-arrowreturnthick-1-w left");

    /* Login */
    $("#logged-in span", root).addClass("ui-icon ui-icon-power left");
    $("#logged-out span", root).addClass("ui-icon ui-icon-key left");
};


/*
 Compatibility methods (global space)
 */

function PHPDS_remoteCall(functionName, params, extParams) {
    return PHPDS.remoteCall(functionName, params, extParams);
}

function PHPDS_documentReady(root) {
    return PHPDS.documentReady(root);
}

