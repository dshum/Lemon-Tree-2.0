<?php
/***************************************************************************
 *   Copyright Denis Shumeev 2008-2010                                     *
 *   denis@lemon-tree.ru                                                   *
 ***************************************************************************/
/* $Id$ */

	define('LT_NAME', 'Lemon Tree 2.0');
	define('LT_VERSION', '2.2.2.new');

	// Environment varyables

	if(!defined('HTTP_HOST')) {
		define('HTTP_HOST', $_SERVER['HTTP_HOST']);
	}

	if(!defined('HTTP_REFERER')) {
		if(isset($_SERVER['HTTP_REFERER'])) {
			define('HTTP_REFERER', $_SERVER['HTTP_REFERER']);
		} else {
			define('HTTP_REFERER', null);
		}
	}

	if(!defined('DOCUMENT_ROOT')) {
		define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR);
	}

	// Paths

	define('PATH_LT_BASE', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);
	define('PATH_ONPHP', PATH_LT_BASE.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'onphp'.DIRECTORY_SEPARATOR);

	define('PATH_ADMIN_SRC', PATH_LT_BASE.'admin'.DIRECTORY_SEPARATOR);
	define('PATH_ADMIN_CLASSES', PATH_ADMIN_SRC.'classes'.DIRECTORY_SEPARATOR);
	define('PATH_ADMIN_CONTROLLERS', PATH_ADMIN_SRC.'controllers'.DIRECTORY_SEPARATOR);
	define('PATH_ADMIN_TEMPLATES', PATH_ADMIN_SRC.'templates'.DIRECTORY_SEPARATOR);
	define('PATH_ADMIN_MAIL_TEMPLATES', PATH_ADMIN_TEMPLATES.'mail'.DIRECTORY_SEPARATOR);

	if(!defined('PATH_BASE')) {
		define('PATH_BASE', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);
	}

	if(!defined('PATH_META')) {
		define('PATH_META', PATH_BASE.'..'.DIRECTORY_SEPARATOR.'meta'.DIRECTORY_SEPARATOR);
	}

	if(!defined('PATH_USER_CLASSES')) {
		define('PATH_USER_CLASSES', PATH_BASE.'user'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR);
	}

	if(!defined('PATH_USER_CLASSES_AUTO')) {
		define('PATH_USER_CLASSES_AUTO', PATH_USER_CLASSES.'Auto'.DIRECTORY_SEPARATOR);
	}

	if(!defined('PATH_USER_CONTROLLERS')) {
		define('PATH_USER_CONTROLLERS', PATH_BASE.'user'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR);
	}

	if(!defined('PATH_USER_FILTERS')) {
		define('PATH_USER_FILTERS', PATH_USER_CONTROLLERS.'filters'.DIRECTORY_SEPARATOR);
	}

	if(!defined('PATH_USER_PLUGINS')) {
		define('PATH_USER_PLUGINS', PATH_USER_CONTROLLERS.'plugins'.DIRECTORY_SEPARATOR);
	}

	if(!defined('PATH_USER_TEMPLATES')) {
		define('PATH_USER_TEMPLATES', PATH_BASE.'user'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR);
	}

	if(!defined('PATH_USER_PLUGIN_TEMPLATES')) {
		define('PATH_USER_PLUGIN_TEMPLATES', PATH_USER_TEMPLATES.'plugins'.DIRECTORY_SEPARATOR);
	}

	if(!defined('PATH_USER_MAIL_TEMPLATES')) {
		define('PATH_USER_MAIL_TEMPLATES', PATH_USER_TEMPLATES.'mail'.DIRECTORY_SEPARATOR);
	}

	// Web paths

	if(!defined('PATH_WEB')) {
		define('PATH_WEB', 'http://'.HTTP_HOST.'/');
	}

	if(!defined('FOLDER_LT')) {
		define('FOLDER_LT', 'LT'.'/');
	}

	if(!defined('FOLDER_LTDATA')) {
		define('FOLDER_LTDATA', 'LT-data'.'/');
	}

	define('PATH_ADMIN', PATH_WEB.FOLDER_LT);
	define('PATH_ADMIN_BROWSE', PATH_WEB.FOLDER_LT.'browse.php');
	define('PATH_ADMIN_TREE', PATH_WEB.FOLDER_LT.'tree.php');
	define('PATH_LTDATA', DOCUMENT_ROOT.FOLDER_LTDATA);
	define('PATH_WEB_LTDATA', PATH_WEB.FOLDER_LTDATA);

	// Everything else

	define('LT_SECRET_WORD', 'Jim_JArmuSCH');
	define('EOL', "\n");
	define('CONFIG_ADMIN_XML', 'config.admin.xml');
	define('CONFIG_USER_XML', 'config.user.xml');

	if(!defined('DEFAULT_ENCODING')) {
		define('DEFAULT_ENCODING', 'UTF8');
	}

	if(!defined('DEFAULT_CHARSET')) {
		define('DEFAULT_CHARSET', 'UTF-8');
	}

	if(!defined('DEFAULT_MAIL_ENCODING')) {
		define('DEFAULT_MAIL_ENCODING', 'Windows-1251');
	}

	if(!defined('BUGLOVERS')) {
		define('BUGLOVERS', 'bugs@lr1.ru');
//		define('BUGLOVERS', 'denis-shumeev@yandex.ru');
	}

	if(!defined('FEEDBACK_EMAIL')) {
		define('FEEDBACK_EMAIL', 'info@lemon-tree.ru');
	}

	// Include onPHP

	require PATH_ONPHP.'global.inc.php.tpl';

	define('ONPHP_META_BUILDERS', ONPHP_META_PATH.'builders'.DIRECTORY_SEPARATOR);
	define('ONPHP_META_PATTERNS', ONPHP_META_PATH.'patterns'.DIRECTORY_SEPARATOR);
	define('ONPHP_META_TYPES', ONPHP_META_PATH.'types'.DIRECTORY_SEPARATOR);

	// Include paths

	ini_set(
		'include_path',
		get_include_path().PATH_SEPARATOR

		.PATH_ADMIN_CLASSES.PATH_SEPARATOR
		.PATH_ADMIN_CLASSES.'Auto'.DIRECTORY_SEPARATOR.'Business'.PATH_SEPARATOR
		.PATH_ADMIN_CLASSES.'Auto'.DIRECTORY_SEPARATOR.'DAOs'.PATH_SEPARATOR
		.PATH_ADMIN_CLASSES.'Auto'.DIRECTORY_SEPARATOR.'Proto'.PATH_SEPARATOR
		.PATH_ADMIN_CLASSES.'Business'.PATH_SEPARATOR
		.PATH_ADMIN_CLASSES.'DAOs'.PATH_SEPARATOR
		.PATH_ADMIN_CLASSES.'Proto'.PATH_SEPARATOR
		.PATH_ADMIN_CLASSES.'Properties'.PATH_SEPARATOR
		.PATH_ADMIN_CLASSES.'Properties'.DIRECTORY_SEPARATOR.'Parameters'.PATH_SEPARATOR
		.PATH_ADMIN_CLASSES.'Flow'.PATH_SEPARATOR
		.PATH_ADMIN_CLASSES.'Utils'.PATH_SEPARATOR

		.PATH_USER_CLASSES.PATH_SEPARATOR
		.PATH_USER_CLASSES_AUTO.'Business'.PATH_SEPARATOR
		.PATH_USER_CLASSES_AUTO.'DAOs'.PATH_SEPARATOR
		.PATH_USER_CLASSES_AUTO.'Proto'.PATH_SEPARATOR
		.PATH_USER_CLASSES.'Business'.PATH_SEPARATOR
		.PATH_USER_CLASSES.'DAOs'.PATH_SEPARATOR
		.PATH_USER_CLASSES.'Proto'.PATH_SEPARATOR
		.PATH_USER_CLASSES.'Flow'.PATH_SEPARATOR
		.PATH_USER_CLASSES.'Utils'.PATH_SEPARATOR

		.PATH_ADMIN_CONTROLLERS.PATH_SEPARATOR
		.PATH_USER_CONTROLLERS.PATH_SEPARATOR
		.PATH_USER_FILTERS.PATH_SEPARATOR
		.PATH_USER_PLUGINS.PATH_SEPARATOR

		.ONPHP_META_BUILDERS.PATH_SEPARATOR
		.ONPHP_META_PATTERNS.PATH_SEPARATOR
		.ONPHP_META_TYPES.PATH_SEPARATOR
	);

	include PATH_ADMIN_CLASSES.'Utils'.DIRECTORY_SEPARATOR.'RussianTypograph.class.php';
	include PATH_ADMIN_CLASSES.'Utils'.DIRECTORY_SEPARATOR.'RussianTextUtils.class.php';

	mb_internal_encoding(DEFAULT_ENCODING);
	mb_regex_encoding(DEFAULT_ENCODING);

	function fatalErrorHandler($buffer)
	{
		if(isset($GLOBALS['tmp_buf'])) unset($GLOBALS['tmp_buf']);

		if(preg_match('@(Fatal error</b>:)(.+)(<br)@', $buffer, $matches) ) {

			$message = trim($matches[2]);

			ErrorMessageUtils::sendFatalError($message);

			header('Content-type:  text/html; charset='.DEFAULT_CHARSET);

			$html =
				defined('__LOCAL_DEBUG__')
				? $buffer
				: 'На сайте ведутся технические работы.'
					.' Обновите страницу, пожалуйста.'
					.' Если это не поможет, зайдите на сайт через 15 минут.<br>'
					.'Приносим извинения за возможные неудобства.';

			return $html;

		}

		return $buffer;
	}
?>