<?php namespace Muvuca\Util;


class HttpRequest
{

    protected $method;
    protected $uri;
    protected $vars;

    public function __get($name)
    {

        return $this->$name;

    }

    public function __construct()
    {

        $this->uri = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->method = 'POST';
            $this->vars = &$_POST;
        } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->method = 'GET';
            $this->vars = &$_GET;
        }

    }


}
