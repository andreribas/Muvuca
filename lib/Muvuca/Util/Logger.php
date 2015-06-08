<?php namespace Muvuca\Util;

class Logger
{

	static $instance = null;
	protected $logs = [];


	private function __construct()
	{


	}

	public static function getLogger($where)
	{

		echo $where;


	}


	protected function addLog($message, $logLevel)
	{

		$logs[] = new Log($message, $logLevel);

	}


	public function error($message)
	{

		$this->addLog($message, Log::ERROR);

	}


	public function debug($message)
	{

		$this->addLog($message, Log::DEBUG);

	}


}
