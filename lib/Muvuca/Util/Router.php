<?php namespace Muvuca\Util;


class Router
{

	protected $routes = [];


	public function __construct($routes = [])
	{

		$this->addRoutes($routes);

	}


	public function addRoutes($routes)
	{

		foreach ($routes as $route) {
			$this->addRoute($route);
		}

	}


	public function addRoute($route)
	{

		if ($this->validateRoute($route)) {
			$this->routes[] = $route;
		}

	}


	protected function validateRoute($route)
	{

		if (!in_array($route->method, ['GET', 'POST'])) {
			echo('Trying to add a route with an invalid http method:' . $route . '<br>');
			return false;
		}
		if (class_exists($route->controller)) {
			if ($route->action != null && !method_exists($route->controller, $route->action)) {
				echo('Trying to add a route with an invalid action: ' . $route . '<br>');
				return false;
			}
		} else {
			echo('Trying to add an invalid route: ' . $route . '<br>');
			return false;
		}

		return true;

	}


	public function getRoute($httpRequest)
	{

		foreach ($this->routes as $route) {
			if ($route->validateHttpRequest($httpRequest)) {
				return $route;
			}
		}
		return null;

	}

}
