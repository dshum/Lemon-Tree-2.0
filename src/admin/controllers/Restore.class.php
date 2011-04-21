<?php
	final class Restore
	{
		public function handleRequest(HttpRequest $request)
		{
			$form =
				Form::create()->
				add(Primitive::string('someaction'))->
				add(Primitive::string('UserName'))->
				add(Primitive::string('email'))->
				add(Primitive::string('code'))->
				add(Primitive::string('status'))->
				import($request->getGet())->
				importMore($request->getPost());

			switch($form->getValue("someaction"))
			{
				case "send":
					if($form->primitiveExists("UserName")) {
						try {
							$user = User::dao()->getByLogic(
								Expression::eq('user_name', $form->getValue('UserName'))
							);
						} catch (ObjectNotFoundException $e) {
							$user = null;
						}
						if($user) {
							if($user->getUserEmail()) {
								$code = substr(md5(LT_SECRET_WORD.$user->getUserName()), 0, 16);
								MailUtils::create()->
								setModel(
									Model::create()->
									set('login', $user->getUserName())->
									set('email', $user->getUserEmail())->
									set('url', PATH_ADMIN.'?module=Restore&login='.$user->getUserName().'&code='.$code)
								)->
								setView('mail/restoreLink')->
								setEncoding(DEFAULT_MAIL_ENCODING)->
								send();
								$status = 'link_sent';
								HeaderUtils::redirectRaw(PATH_ADMIN.'?module=Restore&UserName='.$user->getUserName().'&email='.$user->getUserEmail().'&status='.$status);
								exit;
							} else {
								$status = 'user_has_no_email';
							}
						} else {
							$status = 'user_not_found';
						}
					} else {
						$status = 'login_empty';
					}
					HeaderUtils::redirectRaw(PATH_ADMIN.'?module=Restore&UserName='.$form->getValue("UserName").'&status='.$status);
					exit;
					break;
				case "restore":
					HeaderUtils::redirectRaw(PATH_ADMIN.'?module=Restore&UserName='.$form->getValue("UserName").'&status='.$status);
					break;
				default:
					$UserName =
						$form->primitiveExists("UserName") ?
						$form->getValue("UserName") :
						(isset($_COOKIE["UserName"]) ? $_COOKIE["UserName"] : "");
					$model = Model::create();
					$model->set("UserName", $UserName);
					$model->set("email", $form->getValue("email"));
					$model->set("status", $form->getValue("status"));
					return ModelAndView::create()->setModel($model);
					break;
			}
		}
	}
?>