<?php
	final class Main extends MethodMappedController
	{
		public function __construct()
		{
			$this->
			setMethodMapping('logout', 'logout')->
			setMethodMapping('show', 'show')->
			setDefaultAction('show');
		}

		public function handleRequest(HttpRequest $request)
		{
			return parent::handleRequest($request);
		}

		public function logout(HttpRequest $request)
		{
			LoggedUser::dropUser();

			Session::drop(User::LABEL);

			return
				ModelAndView::create()->
				setView(new RedirectToView('Prompt'));
		}

		public function show(HttpRequest $request)
		{
			$model = Model::create();

			return
				ModelAndView::create()->
				setModel($model)->
				setView('Main');
		}
	}
?>