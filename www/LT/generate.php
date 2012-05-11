<?php
//	die('Comment die()');

	require $_SERVER['DOCUMENT_ROOT'].'/../src/config.inc.php';

	try {
		Site::initMicroTime();

		Item::dao()->uncacheLists();
		Property::dao()->uncacheLists();

		Item::dao()->setItemList();
		Property::dao()->setPropertyList();

		Site::generateAuto();
		echo 'Generate classes OK<br>';
		exit();

	} catch (BaseException $e) {
		echo ErrorMessageUtils::printMessage($e);
	}
?>