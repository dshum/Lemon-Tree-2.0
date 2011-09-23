<?php
	die('Comment die()');

	require $_SERVER['DOCUMENT_ROOT'].'/../src/config.inc.php';

	try {
		Site::initMicroTime();

		Item::dao()->setItemList();
		Property::dao()->setPropertyList();

		/*
		Site::generateAutoAdmin();
		echo 'Generate classes OK<br>';
		exit();
		*/

		/*
		$itemList = Item::dao()->getDefaultItemList();

		$offset = 0;
		$step = 1000;
		$count = 0;

		foreach($itemList as $item) {

			$count++;

			if($count < $offset) continue;
			if($count >= $offset + $step) break;

			if($item->getIsUpdatePath()) continue;

			$dao = $item->getClass()->dao();

			$db = DBPool::me()->getByDao($dao);

			$query = 'ALTER TABLE '.$dao->getTable().' CHANGE element_path element_path VARCHAR(255) NULL';
			$db->queryRaw($query);
			echo $count.'. '.$query.'<br>';

			$query = 'UPDATE '.$dao->getTable().' SET element_path = NULL';
			$db->queryRaw($query);
			echo $count.'. '.$query.'<br>';
		}
		*/

	} catch (BaseException $e) {
		echo ErrorMessageUtils::printMessage($e);
	}
?>