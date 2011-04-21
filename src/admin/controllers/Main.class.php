<?php
	final class Main
	{
		public function handleRequest(HttpRequest $request)
		{
			$model = Model::create();

			return
				ModelAndView::create()->
				setModel($model)->
				setView('Main');
		}
	}
?>