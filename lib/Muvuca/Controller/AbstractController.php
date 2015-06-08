<?php namespace Muvuca\Controller;

abstract class AbstractController {

	protected function renderView($view) {
		$viewFile = implode(DIRECTORY_SEPARATOR, Array(SILVERADO_DIR, 'Silverado', 'Views', $view . '.php'));
		if (file_exists($viewFile)) {
			include_once($viewFile);
		}
	}


}
