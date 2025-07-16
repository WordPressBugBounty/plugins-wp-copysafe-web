//  WP Copysafe Web 
//  Copyright (c) 1998-2022 ArtistScope. All Rights Reserved.
//  www.artistscope.com
//
// The Copysafe Web Plugin is supported across all Windows OS since XP.
//
// Special JS version for Wordpress

// Debugging outputs the generated html into a textbox instead of rendering
// option has been moved to wp-copysafe-web.php

// REDIRECTS

var m_szLocation = document.location.href.replace(/&/g,'%26');	
var m_szDownloadNo = wpcsw_plugin_url + "download_no.html";
var m_szDownload = wpcsw_plugin_url + "download.html";
var m_szDownloadUpdate = wpcsw_plugin_url + "download-update.html";


//===========================
//   DO NOT EDIT BELOW 
//===========================

var m_szAgent = navigator.userAgent.toLowerCase();
var m_szBrowserName = navigator.appName.toLowerCase();
var m_szPlatform = navigator.platform.toLowerCase();
var m_bNetscape = false;
var m_bMicrosoft = false;
var m_szPlugin = "";

var m_bWin64 = ((m_szPlatform == "win64") || (m_szPlatform.indexOf("win64")!=-1) || (m_szAgent.indexOf("win64")!=-1));
var m_bWin32 = ((m_szPlatform == "win32") || (m_szPlatform.indexOf("win32")!=-1));
var m_bWindows = (m_szAgent.indexOf("windows nt")!=-1);

var m_bASPS = ((m_szAgent.indexOf("artisreader")!=-1) && (m_bpASPS));
var m_bFirefox = ((m_szAgent.indexOf("firefox") != -1) && (m_bpFx));
var m_bChrome = ((m_szAgent.indexOf("chrome") != -1) && !(window.chrome && chrome.webstore && chrome.webstore.install) && (m_bpChrome));

var m_bNetscape = ((m_bASPS) || (m_bChrome) || (m_bFirefox));

if((m_bWindows) && (m_bNetscape) > 0)
{
	if( !m_bASPS && !m_bpDebugging ){
		window.location=unescape(m_szDownload);
		document.MM_returnValue=false;
	}
	else{
		m_szPlugin = "DLL";
	}
}
else if( !m_bWindows )
{
	window.location=unescape(m_szDownloadNo);
	document.MM_returnValue=false;
}
else
{
	window.location=unescape(m_szDownload);
	document.MM_returnValue=false;
}

function bool2String(bValue)
{
	if (bValue == true) {
		return "1";
	}
	else {
		return "0";
	}
}

function paramValue(szValue, szDefault)
{
	if (szValue.toString().length > 0) {
		return szValue;
	}
	else {
		return szDefault;
	}
}

function expandNumber(nValue, nLength)
{
    var szValue = nValue.toString();
    while(szValue.length < nLength)
        szValue = "0" + szValue;
    return szValue;
}

// The copysafe-insert functions

function insertCopysafeWeb(szImageName, szcWidth, szcHeight)
{
    // Extract the image width and height from the image name (example name: zulu580_0580_0386_C.class)

    var nIndex = szImageName.lastIndexOf('_C.');
    if (nIndex == -1 && !m_bpDebugging)
    {
        // Strange filename that doesn't conform to the copysafe standard. Can't render it.
        return;
    }

	if (!szcWidth) {
		var szWidth = szImageName.substring(nIndex - 9, nIndex - 5);
	} 
	else {
		var szWidth = szcWidth;
	}
	if (!szcHeight) {
		var szHeight = szImageName.substring(nIndex - 4, nIndex);
	} 
	else {
		var szHeight = szcHeight;
	}

    var nWidth = szWidth * 1;
    var nHeight = szHeight * 1;

    // Expand width and height to allow for border

    var nBorder = m_szDefaultBorder * 1;
    nWidth = nWidth + (nBorder * 2);
    nHeight = nHeight + (nBorder * 2);

    insertCopysafeImage(nWidth, nHeight, "", "", nBorder, "", "", "", [szImageName]);

}

function insertCopysafeImage(nWidth, nHeight, szTextColor, szBorderColor, nBorder, szLoading, szLink, szTargetFrame, arFrames)
{
	if (m_bpDebugging == true)
	{ 
        document.writeln("<textarea rows='27' cols='80'>"); 
	} 

    var szObjectInsert = "";
    
    if (m_szPlugin == "DLL")
    {
    	szObjectInsert = "type='application/x-artistscope-firefox5' codebase='" + wpcsw_plugin_url + "download-update.html' ";
        document.writeln("<ob" + "ject " + szObjectInsert + " width='" + nWidth + "' height='" + nHeight + "'>");

		document.writeln("<param name='KeySafe' value='" + bool2String(m_bpKeySafe) + "' />");
		document.writeln("<param name='CaptureSafe' value='" + bool2String(m_bpCaptureSafe) + "' />");
		document.writeln("<param name='MenuSafe' value='" + bool2String(m_bpMenuSafe) + "' />");
		document.writeln("<param name='RemoteSafe' value='" + bool2String(m_bpRemoteSafe) + "' />");
		
		document.writeln("<param name='Style' value='ImageLink' />");
		document.writeln("<param name='TextColor' value='" + paramValue(szTextColor, m_szDefaultTextColor) + "' />");
		document.writeln("<param name='BorderColor' value='" + paramValue(szBorderColor, m_szDefaultBorderColor) + "' />");
		document.writeln("<param name='Border' value='" + paramValue(nBorder, m_szDefaultBorder) + "' />");
		document.writeln("<param name='Loading' value='" + paramValue(szLoading, m_szDefaultLoading) + "' />");
		document.writeln("<param name='Label' value='' />");
		document.writeln("<param name='Link' value='" + paramValue(szLink, m_szDefaultLink) + "' />");
		document.writeln("<param name='TargetFrame' value='" + paramValue(szTargetFrame, m_szDefaultTargetFrame) + "' />");
		document.writeln("<param name='Message' value='' />");   
		document.writeln("<param name='FrameDelay' value='2000' />");
		document.writeln("<param name='FrameCount' value='1' />");
		document.writeln("<param name='Frame000' value='" + m_szImageFolder + m_szClassName + "' />");

		document.writeln("</ob" + "ject />"); 

		if (m_bpDebugging == true)
		{
			document.writeln("</textarea />");
		}
    }
}

shortcut = {
    all_shortcuts: {},
      add: function (e, t, n) {
        var r = {
          type: "keydown",
          propagate: !1,
          disable_in_input: !1,
          target: document,
          keycode: !1
        };
        if (n) for (var i in r) "undefined" == typeof n[i] && (n[i] = r[i]);
        else n = r;
        r = n.target, "string" == typeof n.target && (r = document.getElementById(n.target)), e = e.toLowerCase(), i = function (r) {
          r = r || window.event;
          if (n.disable_in_input) {
            var i;
            r.target ? i = r.target : r.srcElement && (i = r.srcElement), 3 == i.nodeType && (i = i.parentNode);
            if ("INPUT" == i.tagName || "TEXTAREA" == i.tagName) return
          }
          r.keyCode ? code = r.keyCode : r.which && (code = r.which), i = String.fromCharCode(code).toLowerCase(), 188 == code && (i = ","), 190 == code && (i = ".");
          var s = e.split("+"),
            o = 0,
            u = {
              "`": "~",
              1: "!",
              2: "@",
              3: "#",
              4: "$",
              5: "%",
              6: "^",
              7: "&",
              8: "*",
              9: "(",
              0: ")",
              "-": "_",
              "=": "+",
              ";": ":",
              "'": '"',
              ",": "<",
              ".": ">",
              "/": "?",
              "\\": "|"
            }, f = {
              esc: 27,
              escape: 27,
              tab: 9,
              space: 32,
              "return": 13,
              enter: 13,
              backspace: 8,
              scrolllock: 145,
              scroll_lock: 145,
              scroll: 145,
              capslock: 20,
              caps_lock: 20,
              caps: 20,
              numlock: 144,
              num_lock: 144,
              num: 144,
              pause: 19,
              "break": 19,
              insert: 45,
              home: 36,
              "delete": 46,
              end: 35,
              pageup: 33,
              page_up: 33,
              pu: 33,
              pagedown: 34,
              page_down: 34,
              pd: 34,
              left: 37,
              up: 38,
              right: 39,
              down: 40,
              f1: 112,
              f2: 113,
              f3: 114,
              f4: 115,
              f5: 116,
              f6: 117,
              f7: 118,
              f8: 119,
              f9: 120,
              f10: 121,
              f11: 122,
              f12: 123
            }, l = !1,
            c = !1,
            h = !1,
            p = !1,
            d = !1,
            v = !1,
            m = !1,
            y = !1;
          r.ctrlKey && (p = !0), r.shiftKey && (c = !0), r.altKey && (v = !0), r.metaKey && (y = !0);
          for (var b = 0; k = s[b], b < s.length; b++) "ctrl" == k || "control" == k ? (o++, h = !0) : "shift" == k ? (o++, l = !0) : "alt" == k ? (o++, d = !0) : "meta" == k ? (o++, m = !0) : 1 < k.length ? f[k] == code && o++ : n.keycode ? n.keycode == code && o++ : i == k ? o++ : u[i] && r.shiftKey && (i = u[i], i == k && o++);
          if (o == s.length && p == h && c == l && v == d && y == m && (t(r), !n.propagate)) return r.cancelBubble = !0, r.returnValue = !1, r.stopPropagation && (r.stopPropagation(), r.preventDefault()), !1
        }, this.all_shortcuts[e] = {
          callback: i,
          target: r,
          event: n.type
        }, r.addEventListener ? r.addEventListener(n.type, i, !1) : r.attachEvent ? r.attachEvent("on" + n.type, i) : r["on" + n.type] = i
      },
      remove: function (e) {
        var e = e.toLowerCase(),
          t = this.all_shortcuts[e];
        delete this.all_shortcuts[e];
        if (t) {
          var e = t.event,
            n = t.target,
            t = t.callback;
          n.detachEvent ? n.detachEvent("on" + e, t) : n.removeEventListener ? n.removeEventListener(e, t, !1) : n["on" + e] = !1
        }
      }
    },
     shortcut.add("Ctrl+U",function(){
     alert('Sorry\nNo CTRL+U is allowed. Be creative!')
    }),
     shortcut.add("Meta+Alt+U",function(){
     alert('Sorry\nNo Command+Option+U is allowed. Be creative!')
    }),
    shortcut.add("Ctrl+C",function(){
     alert('Sorry\nNever duplicate this article...')
    }),
    shortcut.add("Meta+C",function(){
     alert('Sorry\nNever duplicate this article...')
    });