<?php
	final class Prompt extends MethodMappedController
	{
		public function __construct()
		{
			$this->
				setMethodMapping('login', 'login')->
				setMethodMapping('logout', 'logout')->
				setMethodMapping('form', 'form')->
				setDefaultAction('form');
		}

		public function handleRequest(HttpRequest $request)
		{
			return parent::handleRequest($request);
		}

		public function login(HttpRequest $request)
		{
			$model = Model::create();

			$form =
				Form::create()->
				add(Primitive::string('UserName')->required())->
				add(Primitive::string('UserPassword')->required())->
				import($request->getPost());

			if(!$form->getErrors()) {
				try {
					$user = User::dao()->getByLogic(
						Expression::eq('user_name', $form->getValue('UserName'))
					);
					if($user->getUserPassword() == md5($form->getValue('UserPassword'))) {

						$user->setLoginDate(Timestamp::makeNow());
						$user = User::dao()->save($user);

						LoggedUser::setUser($user);

						Session::assign(User::LABEL, $user);

						Cookie::create('UserName')->
						setValue($user->getUserName())->
						setMaxAge(3600 * 24 * 365)->
						setPath('/')->
						httpSet();

						return
							ModelAndView::create()->
							setView(new RedirectToView('Main'));
					} else {
						$form->markWrong('UserPassword');
					}
				} catch (ObjectNotFoundException $e) {
					$form->markWrong('UserName');
				}
			}

			$model->
			set('UserName', $form->getValue('UserName'))->
			set('UserPassword', $form->getValue('UserPassword'))->
			set('form', $form);

			return
				ModelAndView::create()->
				setModel($model)->
				setView('Prompt');
		}

		public function logout(HttpRequest $request)
		{
			LoggedUser::dropUser();

			Session::drop(User::LABEL);

			return
				ModelAndView::create()->
				setView(new RedirectToView('Prompt'));
		}

		public function form(HttpRequest $request)
		{
			$model = Model::create();

			$userName =
				isset($_COOKIE['UserName'])
				? $_COOKIE['UserName']
				: '';

			$model->
			set('form', Form::create())->
			set('UserName', $userName)->
			set('UserPassword', '');

			return
				ModelAndView::create()->
				setModel($model)->
				setView('Prompt');
		}
	}
?>