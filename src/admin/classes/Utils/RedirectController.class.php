<?php
	final class RedirectController
	{
		private $redirectUrl = PATH_WEB;

		public function handleRequest(HttpRequest $request)
		{
			return
				ModelAndView::create()->
				setModel(Model::create())->
				setView('redirect:'.$this->redirectUrl);
		}

		public function setRedirectUrl($redirectUrl = PATH_WEB)
		{
			$this->redirectUrl = $redirectUrl;

			return $this;
		}

		public function getRedirectUrl()
		{
			return $this->redirectUrl;
		}
	}
?>