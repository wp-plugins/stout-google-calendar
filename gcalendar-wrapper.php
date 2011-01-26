<?php

/**
 * Embedded Google Calendar customization wrapper script created by:
 * @author      Chris Dornfeld <dornfeld (at) unitz.com>
 * @version     $Id: gcalendar-wrapper.php 1571 2010-11-15 07:08:05Z dornfeld $
 * @link        http://www.unitz.com/gcalendar-wrapper/
 *
 * Extended and adapted for the Stout Google Calendar WordPress plugin by Matt McKenny <sgc (at) stoutdesign.com>
 * Applies a custom color scheme to an embedded Google Calendar.
 *
 */

/**
 * For normal use, no changes are needed below this line
 */

define('GOOGLE_CALENDAR_BASE', 'https://www.google.com/');
define('GOOGLE_CALENDAR_EMBED_URL', GOOGLE_CALENDAR_BASE . 'calendar/embed');

/**
 * Construct calendar URL
 */

$calQuery = '';
if (isset($_SERVER['QUERY_STRING'])) {
	$calQuery = $_SERVER['QUERY_STRING'];
} else if (isset($HTTP_SERVER_VARS['QUERY_STRING'])) {
	$calQuery = $HTTP_SERVER_VARS['QUERY_STRING'];
}
$calUrl = GOOGLE_CALENDAR_EMBED_URL.'?'.$calQuery;


/**
 * Set your color scheme below
 */

preg_match('/sgc0=(\w+)/',$calQuery,$color0);
preg_match('/sgc1=(\w+)/',$calQuery,$color1);
preg_match('/sgc2=(\w+)/',$calQuery,$color2);
preg_match('/sgc3=(\w+)/',$calQuery,$color3);
preg_match('/sgc4=(\w+)/',$calQuery,$color4);
preg_match('/sgc5=(\w+)/',$calQuery,$color5);
preg_match('/sgc6=(\w+)/',$calQuery,$color6);
preg_match('/sgcBkgrdTrans=(\d)/',$calQuery,$bkgrdTrans);
preg_match('/sgcImage=(\d+)/',$calQuery,$sgcImage);
preg_match('/wpurl=(.+)/',$calQuery,$wpurl);

($bkgrdTrans[1] == 0)  ? $calBkgrd = "#".$color0[1] : $calBkgrd = 'transparent';
($color1[1] != '') ? $calColorBgDark = "#".$color1[1] : $calColorBgDark = '#c3d9ff';
($color2[1] != '') ? $calColorTextOnDark = "#".$color2[1] : $calColorTextOnDark = '#000000';
($color3[1] != '') ? $calColorBgLight = "#".$color3[1] : $calColorBgLight = '#e8eef7';
($color4[1] != '') ? $calColorTextOnLight = "#".$color4[1] : $calColorTextOnLight = '#000000';
($color5[1] != '') ? $calColorBgToday = "#".$color5[1] : $calColorBgToday = '#ffffcc';
($color6[1] != '') ? $calBkgrdText = "#".$color6[1] : $calBkgrdText = '#000000';
$wpurl = $wpurl[1];

switch ($sgcImage[1]) {
	case 0 :
		$sgcImage = 'https://calendar.google.com/googlecalendar/images/combined_v18.png';
		break;
	case 1 :
		//gray
		$sgcImage = $wpurl.'/stout-google-calendar/images/sgc_gray_combined_v18.png';
		break;
	case 2 :
		//50% black
		$sgcImage = $wpurl.'/stout-google-calendar/images/sgc_50black_combined_v18.png';
		break;
	case 3 :
		//50% white
		$sgcImage = $wpurl.'/stout-google-calendar/images/sgc_50white_combined_v18.png';
		break;
}


/**
 * Prepare stylesheet customizations
 */

$calCustomStyle =<<<EOT

body {
	background-color: {$calBkgrd}  !important;
}
.navBack, .navForward {
	background-image: url({$sgcImage}) !important;
}
#currentDate1, .tab-name {
	color: {$calBkgrdText} !important;
}
#calendarTitle {
	display:none;
}
/* misc interface */
.cc-titlebar {
	background-color: {$calColorBgLight} !important;
}
.date-picker-arrow-on,
.drag-lasso,
.agenda-scrollboxBoundary {
	background-color: {$calColorBgDark} !important;
}
td#timezone {
	color: {$calColorTextOnDark} !important;
}

/* ensures more info bubble display in their entirety in calendars smaller than 400px wide */
div.bubble {width: 80% !important;} 

/* tabs */
td#calendarTabs1 div.ui-rtsr-selected,
div.view-cap,
div.view-container-border {
	background-color: {$calColorBgDark} !important;
}
td#calendarTabs1 div.ui-rtsr-selected {
	color: {$calColorTextOnDark} !important;
}
td#calendarTabs1 div.ui-rtsr-unselected {
	background-color: {$calColorBgLight} !important;
	color: {$calColorTextOnLight} !important;
}

/* week view */
table.wk-weektop,
th.wk-dummyth {
	/* days of the week */
	background-color: {$calColorBgDark} !important;
}
div.wk-dayname {
	color: {$calColorTextOnDark} !important;
}
div.wk-today {
	background-color: {$calColorBgLight} !important;
	border: 1px solid #EEEEEE !important;
	color: {$calColorTextOnLight} !important;
}
td.wk-allday {
	background-color: #EEEEEE !important;
}
td.tg-times {
	background-color: {$calColorBgLight} !important;
	color: {$calColorTextOnLight} !important;
}
div.tg-today {
	background-color: {$calColorBgToday} !important;
}
td.tg-times-pri, td.tg-times-sec {
	background-color: {$calColorBgLight} !important;
	color: {$calColorTextOnLight}  !important;
}

/* month view */
table.mv-daynames-table {
	background-color: {$calColorBgDark} !important;
	/* days of the week */
	color: {$calColorTextOnDark} !important;
}
td.st-bg,
td.st-dtitle {
	/* cell borders */
	border-left: 1px solid {$calColorBgDark} !important;
}
td.st-dtitle {
	/* days of the month */
	background-color: {$calColorBgLight} !important;
	color: {$calColorTextOnLight} !important;
	/* cell borders */
	border-top: 1px solid {$calColorBgDark} !important;
}
td.st-bg-today {
	background-color: {$calColorBgToday} !important;
	border-right: {$calColorBgToday} !important;
}
td.st-dtitle-today {
	border:none;
}

/* agenda view */
div.scrollbox {
	border-top: 1px solid {$calColorBgDark} !important;
	border-left: 1px solid {$calColorBgDark} !important;
}
div.underflow-top {
	border-bottom: 1px solid {$calColorBgDark} !important;
}
div.date-label {
	background-color: {$calColorBgLight} !important;
	color: {$calColorTextOnLight} !important;
}
div.event {
	border-top: 1px solid {$calColorTextOnLight} !important;
}
div.day {
	border-bottom: 1px solid {$calColorTextOnLight} !important;
}
.mv-event-container {
	border-top:1px solid {$calColorBgDark} !important;
	border-bottom:1px solid {$calColorBgDark} !important;
}
.agenda .event-links a:link {
	color: {$calColorBgDark} !important;
}

/* Popup calendar * /
td.dp-cell, td.dp-weekday-selected, td.dp-onmonth-selected {
	background-color: {$calColorTextOnDark} !important;
}
#dpPopup1 #dpPopup1_header {
	background-color: {$calColorTextOnDark} !important;
}

EOT;

$calCustomStyle = '<style type="text/css">'.$calCustomStyle.'</style>';


/**
 * Retrieve calendar embedding code
 */

$calRaw = '';
if (in_array('curl', get_loaded_extensions())) {
	$curlObj = curl_init();
	curl_setopt($curlObj, CURLOPT_URL, $calUrl);
	curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
	// trust any SSL certificate (we're only retrieving public data)
	curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curlObj, CURLOPT_SSL_VERIFYHOST, FALSE);
	if (function_exists('curl_version')) {
		$curlVer = curl_version();
		if (is_array($curlVer)) {
			if (!in_array('https', $curlVer['protocols'])) {
				trigger_error("Can't use https protocol with cURL to retrieve Google Calendar", E_USER_ERROR);
			}
			if (!empty($curlVer['version']) &&
				version_compare($curlVer['version'], '7.15.2', '>=') &&
				!ini_get('open_basedir') && !ini_get('safe_mode')) {
				// enable HTTP redirect following for cURL:
				// - CURLOPT_FOLLOWLOCATION is disabled when PHP is in safe mode
				// - cURL versions before 7.15.2 had a bug that lumped
				//   redirected page content with HTTP headers
				// http://simplepie.org/support/viewtopic.php?id=1004
				curl_setopt($curlObj, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($curlObj, CURLOPT_MAXREDIRS, 5);
			}
		}
	}
	$calRaw = curl_exec($curlObj);
	curl_close($curlObj);
} else if (ini_get('allow_url_fopen')) {
	if (function_exists('stream_get_wrappers')) {
		if (!in_array('https', stream_get_wrappers())) {
			trigger_error("Can't use https protocol with fopen to retrieve Google Calendar", E_USER_ERROR);
		}
	} else if (!in_array('openssl', get_loaded_extensions())) {
		trigger_error("Can't use https protocol with fopen to retrieve Google Calendar", E_USER_ERROR);
	}
	// fopen should follow HTTP redirects in recent versions
	$fp = fopen($calUrl, 'r');
	while (!feof($fp)) {
		$calRaw .= fread($fp, 8192);
	}
	fclose($fp);
} else {
	trigger_error("Can't use cURL or fopen to retrieve Google Calendar", E_USER_ERROR);
}

/**
 * Insert BASE tag to accommodate relative paths
 */

$titleTag = '<title>';
$baseTag = '<base href="'.GOOGLE_CALENDAR_EMBED_URL.'">';
$calCustomized = preg_replace("/".preg_quote($titleTag,'/')."/i", $baseTag.$titleTag, $calRaw);

/**
 * Insert custom styles
 */

$headEndTag = '</head>';
$calCustomized = preg_replace("/".preg_quote($headEndTag,'/')."/i", $calCustomStyle.$headEndTag, $calCustomized);

/**
 * Extract and modify calendar setup data
 */

$calSettingsPattern = "(\{\s*window\._init\(\s*)(\{.+\})(\s*\)\;\s*\})";

if (preg_match("/$calSettingsPattern/", $calCustomized, $matches)) {
	$calSettingsJson = $matches[2];

	$pearJson = null;
	if (!function_exists('json_encode')) {
		// no built-in JSON support, attempt to use PEAR::Services_JSON library
		if (!class_exists('Services_JSON')) {
			require_once('JSON.php');
		}
		$pearJson = new Services_JSON();
	}

	if (function_exists('json_decode')) {
		$calSettings = json_decode($calSettingsJson);
	} else {
		$calSettings = $pearJson->decode($calSettingsJson);
	}

	// set base URL to accommodate relative paths
	$calSettings->baseUrl = GOOGLE_CALENDAR_BASE;

	// splice in updated calendar setup data
	if (function_exists('json_encode')) {
		$calSettingsJson = json_encode($calSettings);
	} else {
		$calSettingsJson = $pearJson->encode($calSettings);
	}
	// prevent unwanted variable substitutions within JSON data
	// preg_quote() results in excessive escaping
	$calSettingsJson = str_replace('$', '\\$', $calSettingsJson);
	$calCustomized = preg_replace("/$calSettingsPattern/", "\\1$calSettingsJson\\3", $calCustomized);
}

/**
 * Show output
 */

header('Content-type: text/html');
print $calCustomized;

?>
