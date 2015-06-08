<?php namespace Muvuca\Util;

class Route
{

	protected $method;
	protected $path;
	protected $controller;
	protected $action;
	protected $args = [];


	public function __construct($method, $path, $controller, $action = null)
	{

		$this->method = $method;
		$this->path = $path;
		$this->controller = $controller;
		$this->action = $action;

	}


	public function __get($name)
	{

		return $this->$name;

	}


	public function __toString()
	{

		return $this->method . ' ' . $this->path . ': ' . $this->controller . '->' . $this->action;

	}

	public function validateHttpRequest($httpRequest)
	{

		$validUri = preg_match('@'.$this->path.'@', $httpRequest->uri, $this->args);
		$validMethod = $this->method === $httpRequest->method;

		return ($validUri && $validMethod);

	}

	public function callController($httpRequest)
	{

		$controller = new $this->controller($httpRequest, $this->args);
		if ($this->action != null){
			$controller->{$this->action}($httpRequest, $this->args);
		}

	}


}
