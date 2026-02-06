//  WP Copysafe Web 
//  Copyright (c) 1998-2025 ArtistScope. All Rights Reserved.
//  artistscope.com
//
// Special JS version for Wordpress

// Debugging outputs the generated html into a textbox instead of rendering
// option has been moved to wp-copysafe-web.php

//===========================
//   DO NOT EDIT BELOW 
//===========================

var wpcsw_agent = navigator.userAgent.toLowerCase();
var wpcsw_platform = navigator.platform.toLowerCase();

var wpcsw_is_windows = (wpcsw_agent.indexOf("windows nt")!=-1);
var wpcsw_is_mac = wpcsw_platform.indexOf('mac') >= 0;
var wpcsw_is_ios = /(iphone|ipod|ipad)/i.test(wpcsw_platform);
var wpcsw_is_linux = wpcsw_platform.indexOf('linux') >= 0;
var wpcsw_is_android = wpcsw_agent.indexOf('android') >= 0;

function wpcswCheckAccess() {
	let canAccess = false;
	
	if( ! wpcsw_debugging)
	{
		let versionRequired = '';

		if(wpcsw_is_windows) {
			versionRequired = wpcsw_version_windows;
			canAccess = true;
		}
		else if(wpcsw_is_mac && wpcsw_allow_mac)
		{
			versionRequired = wpcsw_version_mac;
			canAccess = true;
		}
		else if(wpcsw_is_ios && wpcsw_allow_ios)
		{
			versionRequired = wpcsw_version_ios;
			canAccess = true;
		}
		else if(wpcsw_is_linux && wpcsw_allow_linux)
		{
			versionRequired = wpcsw_version_linux;
			canAccess = true;
		}
		else if(wpcsw_is_android && wpcsw_allow_android)
		{
			versionRequired = wpcsw_version_android;
			canAccess = true;
		}

		if(canAccess && versionRequired.length && wpcswVersionCompare(versionRequired, wpcsw_version_artisbrowser) > 0) {
			canAccess = false;
		}
	} else {
		canAccess = true;
	}

	if( ! canAccess) {
		window.location = wpcsw_download_url;
	}
}

function wpcswParamValue(szValue, szDefault)
{
	if (szValue.toString().length > 0) {
		return szValue;
	}
	else {
		return szDefault;
	}
}

function wpcswVersionCompare(v1, v2) {
	const v1parts = v1.split('.').map(Number);
	const v2parts = v2.split('.').map(Number);

	const maxLength = Math.max(v1parts.length, v2parts.length);

	for (let i = 0; i < maxLength; i++) {
		const p1 = v1parts[i] || 0; // Treat missing parts as 0
		const p2 = v2parts[i] || 0; // Treat missing parts as 0

		if (p1 > p2) {
			return 1; // v1 is greater
		}
		if (p1 < p2) {
			return -1; // v1 is smaller
		}
	}
	return 0; // Versions are equal
}

// The copysafe-insert functions
function insertCopysafeWeb(params)
{
	wpcswCheckAccess();

	// Extract the image width and height from the image name (example name: zulu580_0580_0386_C.class)
	var nIndex = params.name.lastIndexOf('_C.');
	if (nIndex == -1 && ! wpcsw_debugging)
	{
		// Strange filename that doesn't conform to the copysafe standard. Can't render it.
		return;
	}

	if( ! params.width) {
		var szWidth = params.name.substring(nIndex - 9, nIndex - 5);
	} 
	else {
		var szWidth = params.width;
	}
	if( ! params.height) {
		var szHeight = params.name.substring(nIndex - 4, nIndex);
	} 
	else {
		var szHeight = params.height;
	}

	var nWidth = szWidth * 1;
	var nHeight = szHeight * 1;

	// Expand width and height to allow for border

	let border = params.border * 1;
	nWidth = nWidth + (border * 2);
	nHeight = nHeight + (border * 2);

	insertCopysafeImage(
		params.file_url,
		nWidth,
		nHeight,
		params.text_color,
		params.border_color,
		border,
		params.loading_message,
		params.hyperlink,
		params.target
	);
}

function insertCopysafeImage(file_url, nWidth, nHeight, text_color, border_color, border, loading_message, hyperlink, target)
{
	if (wpcsw_debugging) {
		document.writeln("<textarea rows='27' cols='80'>");
	}

	let szObjectInsert = "type='application/x-artistscope-firefox5' codebase='" + wpcsw_download_url + "' ";

	document.writeln("<ob" + "ject " + szObjectInsert + " class='wpcsw-object' data-width='" + nWidth + "' data-height='" + nHeight + "' width='" + nWidth + "' height='" + nHeight + "'>");
	document.writeln("<param name='Style' value='ImageLink' />");
	document.writeln("<param name='TextColor' value='" + text_color + "' />");
	document.writeln("<param name='BorderColor' value='" + border_color + "' />");
	document.writeln("<param name='Border' value='" + border + "' />");
	document.writeln("<param name='Loading' value='" + loading_message + "' />");
	document.writeln("<param name='Label' value='' />");
	document.writeln("<param name='Link' value='" + hyperlink + "' />");
	document.writeln("<param name='TargetFrame' value='" + target + "' />");
	document.writeln("<param name='Message' value='' />");
	document.writeln("<param name='FrameDelay' value='2000' />");
	document.writeln("<param name='FrameCount' value='1' />");
	document.writeln("<param name='Frame000' value='" + file_url + "' />");

	document.writeln("</ob" + "ject />");

	if (wpcsw_debugging) {
		document.writeln("</textarea />");
	}

	wpcswResize();
}

var wpcswResizeTimeout = null;
addEventListener("resize", (event) => {
	if(wpcswResizeTimeout) {
		clearTimeout(wpcswResizeTimeout);
	}

	wpcswResizeTimeout = setTimeout(function() {
		wpcswResize();
	}, 50);
});

function wpcswResize()
{
	const nodes = document.querySelectorAll('.wpcsw-object');

	nodes.forEach(node => {
		const defaultWidth = parseInt(node.getAttribute('data-width'));
		const defaultHeight = parseInt(node.getAttribute('data-height'));
		const currentWidth = parseInt(node.offsetWidth);
		const currentHeight = parseInt(node.offsetHeight);

		if(( ! isNaN(defaultWidth) && defaultWidth > 0) &&
			( ! isNaN(defaultHeight) && defaultHeight > 0) &&
			( ! isNaN(currentWidth) && currentWidth > 0) &&
			( ! isNaN(currentHeight) && currentHeight > 0))
		{
			const newHeight = parseInt((defaultHeight / defaultWidth) * currentWidth);
			node.style.height = newHeight + 'px';
		}
	});
}