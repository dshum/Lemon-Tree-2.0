<?php
	// die('Comment die()');

	require $_SERVER['DOCUMENT_ROOT'].'/../src/config.inc.php';

	try {
		Site::init();
		Site::generateAuto();
		echo 'Generate classes OK<br>';
	} catch (BaseException $e) {
		echo ErrorMessageUtils::printMessage($e);
	}
?>