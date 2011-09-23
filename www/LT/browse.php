<?php
	require $_SERVER['DOCUMENT_ROOT'].'/../src/config.inc.php';

	ob_start('fatalErrorHandler');

	define('PATH_IMG', DOCUMENT_ROOT.FOLDER_LT.'img/');

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

		if(Session::get(User::LABEL) instanceof User) {

			$loggedUser = Session::get(User::LABEL);

			LoggedUser::setLabel(User::LABEL);
			LoggedUser::setClass('User');
			LoggedUser::setUser($loggedUser);

			$module = $request->hasGetVar('module') ? $request->getGetVar('module') : 'ViewBrowse';

			$controllerName =
				ClassUtils::isClassName($module)
				&& defined('PATH_ADMIN_CONTROLLERS')
				&& is_readable(PATH_ADMIN_CONTROLLERS.$module.EXT_CLASS)
				? $module
				: 'ViewBrowse';

		} else {

			$loggedUser = null;

			$controllerName = 'Prompt';

		}

		$controller = new $controllerName;

		$modelAndView = $controller->handleRequest($request);
		$view = $modelAndView->getView();
		$model = $modelAndView->getModel();

		$prefix = PATH_ADMIN_BROWSE.'?module=';

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
					: PATH_ADMIN_BROWSE;
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
				addPrefix(PATH_ADMIN_TEMPLATES)->
				addPrefix(PATH_USER_PLUGIN_TEMPLATES);

			$view = $viewResolver->resolveViewName($viewName);
		}

		if(!$view instanceof RedirectView) {
			$model->
			set('selfUrl', PATH_ADMIN_BROWSE.'?module='.$controllerName)->
			set('baseUrl', PATH_ADMIN_BROWSE)->
			set('loggedUser', $loggedUser);
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