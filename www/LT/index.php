<?php
	require $_SERVER['DOCUMENT_ROOT'].'/../src/config.inc.php';

	ob_start('fatalErrorHandler');

	define('PATH_WEB_CSS', PATH_ADMIN.'css/');
	define('PATH_WEB_IMG', PATH_ADMIN.'img/');
	define('PATH_WEB_JS', PATH_ADMIN.'js/');

	try {

		$request =
			HttpRequest::create()->
			setGet($_GET)->
			setPost($_POST)->
			setCookie($_COOKIE)->
			setServer($_SERVER)->
			setFiles($_FILES);

		session_name('LT');

		if(!Session::isStarted()) {
			Session::start();
		}

		LoggedUser::setLabel(User::LABEL);
		LoggedUser::setClass('User');

		Session::assign('showSQLReport', isset($_GET['sqlreport']));

		$module = $request->hasGetVar('module') ? $request->getGetVar('module') : 'Prompt';

		if(
			Session::get(User::LABEL) instanceof User
			&& $module != 'Prompt'
		) {
			$controllerName = 'Main';
		} else {
			$controllerName = $module == 'Restore' ? 'Restore' : 'Prompt';
		}

		$controller = new $controllerName;

		$modelAndView = $controller->handleRequest($request);
		$view = $modelAndView->getView();
		$model = $modelAndView->getModel();

		$prefix = PATH_ADMIN.'?module=';

		if(!$view) {
			$view = $controllerName;
		} elseif(is_string($view)) {
			if(strpos($view, 'redirect:') !== false) {
				list(, $module) = explode(':', $view, 2);
				$view = new RedirectView(PATH_ADMIN.'?module='.$module);
			} elseif($view == 'redirectBack') {
				$href =
					isset($_SERVER['HTTP_REFERER'])
					? $_SERVER['HTTP_REFERER']
					: PATH_ADMIN;
				$view = new RedirectView($href);
			}
		} elseif($view instanceof RedirectToView) {
			$view->setPrefix($prefix);
		}

		if(!$view instanceof View) {
			$viewName = $view;

			$viewResolver =
				MultiPrefixPhpViewResolver::create()->
				setViewClassName('SimplePhpView')->
				addPrefix(PATH_ADMIN_TEMPLATES);

			$view = $viewResolver->resolveViewName($viewName);
		}

		if(!$view instanceof RedirectView) {
			$model->
			set('selfUrl', PATH_ADMIN.'index.php?module='.$controllerName)->
			set('baseUrl', PATH_ADMIN.'index.php')->
			set('controllerName', $controllerName);
		}

		$view->render($model);

	} catch (Exception $e) {

		if(defined('__LOCAL_DEBUG__')) {

			ErrorMessageUtils::sendMessage($e);
			echo ErrorMessageUtils::printMessage($e);

		} else {

			ErrorMessageUtils::sendMessage($e);
			DBPool::me()->shutdown();
			header('HTTP/1.1 500 Internal Server Error');
			$model = Model::create()->set('e', $e);
			$viewResolver =
				MultiPrefixPhpViewResolver::create()->
				setViewClassName('SimplePhpView')->
				addPrefix(PATH_ADMIN_TEMPLATES);
			$view = $viewResolver->resolveViewName('Error500');
			$view->render($model);

		}
	}
?>