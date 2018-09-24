<?php
namespace WebServer;


use WebServer\Base\Config\IServerConfig;
use WebServer\Config\ServerConfig;


class Server
{
	private $config;
	
	
	public function __construct()
	{
		$this->config = new ServerConfig();
	}
	
	
	/**
	 * @param string|string[] $config
	 */
	private function executeUnsafe($config): void
	{
		$this->config->validate();
		
		$engine = new Engine($this->config);
		$engine->execute($config);
	}
	
	
	public function config(): IServerConfig
	{
		return $this->config;
	}
	
	/**
	 * @param string|string[] $config
	 * @param callable|null $exceptionHandler
	 */
	public function execute($config, ?callable $exceptionHandler = null)
	{
		if ($exceptionHandler)
		{
			try
			{
				$this->executeUnsafe($config);
			}
			catch (\Throwable $t)
			{
				$narrator = $this->config->getNarrator();
				$narrator = clone $narrator;
				
				$narrator->params()->first($t);
				$narrator->invoke($exceptionHandler);
			}
		}
		else
		{
			$this->executeUnsafe($config);
		}
	}
}