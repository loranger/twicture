<?php

error_reporting(E_ALL);

// Force data refresh instead of submitting the same datas again on page reload
/*
header("Cache-Control: must-revalidate");
$offset = 60 * 60 * 24 * -1;
$expire = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
header($expire);
*/

define( 'LIBPATH',	realpath( dirname( __FILE__ ) ) );
define( 'ROOTPATH',	dirname( LIBPATH ) );
define( 'DATAPATH',	realpath( ROOTPATH . DIRECTORY_SEPARATOR . 'data' ) );
define( 'DBPATH',	DATAPATH . DIRECTORY_SEPARATOR . 'twicture.db');
define( 'PDO_DSN',	'sqlite:' . DBPATH);

require_once( LIBPATH . DIRECTORY_SEPARATOR . 'Auth.php' );
require_once( LIBPATH . DIRECTORY_SEPARATOR . 'Page.php' );
require_once( LIBPATH . DIRECTORY_SEPARATOR . 'PageHelp.php' );
require_once( LIBPATH . DIRECTORY_SEPARATOR . 'PageAdmin.php' );
require_once( LIBPATH . DIRECTORY_SEPARATOR . 'Database.php' );
require_once( LIBPATH . DIRECTORY_SEPARATOR . 'Install.php' );
require_once( LIBPATH . DIRECTORY_SEPARATOR . 'InstallLog.php' );
require_once( LIBPATH . DIRECTORY_SEPARATOR . 'Twicture.php' );
require_once( LIBPATH . DIRECTORY_SEPARATOR . 'Picture.php' );
require_once( LIBPATH . DIRECTORY_SEPARATOR . 'Upload.php' );
require_once( LIBPATH . DIRECTORY_SEPARATOR . 'Admin.php' );

$domain = 'Twicture';
$locales = array('fr_FR', 'fr_FR.utf8');

putenv('LANG='.implode(',', $locales));
setlocale(LC_ALL, $locales);
bindtextdomain($domain, LIBPATH . DIRECTORY_SEPARATOR . 'i18n');
bind_textdomain_codeset($domain, 'UTF8');
textdomain($domain);

function getParam($name)
{
	if( array_key_exists($name, $_POST) )
	{
		return $_POST[$name];
	}
	else if( array_key_exists($name, $_GET) )
	{
		return $_GET[$name];
	}
	return false;
}

function redirect( $url )
{
	header("Location:$url");
	die();
}

function getRelativeDate($time)
{
	$after           = strtotime("+7 day 00:00");
	$afterTomorrow   = strtotime("+2 day 00:00");
	$tomorrow        = strtotime("+1 day 00:00");
	$today           = strtotime("today 00:00");
	$yesterday       = strtotime("-1 day 00:00");
	$beforeYesterday = strtotime("-2 day 00:00");
	$before          = strtotime("-7 day 00:00");

	if ($time < $after && $time > $before) {
		if ($time >= $after) {
			$relative = sprintf( _('next %s'), strftime("%A", $time) );
		} else if ($time >= $afterTomorrow) {
			$relative = _('the day after tomorrow');
		} else if ($time >= $tomorrow) {
			$relative = _('tomorrow');
		} else if ($time >= $today) {
			$relative = _('today');
		} else if ($time >= $yesterday) {
			$relative = _('yesterday');
		} else if ($time >= $beforeYesterday) {
			$relative = _('the day before yesterday');
		} else if ($time >= $before) {
			$relative = sprintf( _('last %s'), strftime("%A", $time) );
		}
	} else {
		$relative = strftime( _('%A %d %B %Y'), $time);
	}

	$relative .= _(' at ').date('H:i', $time);
	return $relative;
}

?>