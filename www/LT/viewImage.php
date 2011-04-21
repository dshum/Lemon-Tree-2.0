<?php
	require $_SERVER['DOCUMENT_ROOT'].'/../src/config.inc.php';

	$form = Form::create();

	$form->
	add(
		Primitive::string('title')
	)->
	add(
		Primitive::string('path')
	)->
	import($_GET);

	$title = $form->getValue('title');
	$path = $form->getValue('path');
?>
<html>
<head>
<title><?=$title?></title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
</head>
<body style="margin: 0; padding: 0;">
<img src="<?=$path?>" border="0" onclick="window.close();" style="cursor: pointer; cursor: hand;" alt="<?=$title?>" title="<?=$title?>">
</body>
</html>