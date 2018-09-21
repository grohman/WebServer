<?php
namespace WebServer\Base\Engine;


use WebCore\IWebRequest;
use WebServer\Base\IClassLoader;
use WebServer\Base\Config\IRoutesConfigLoader;
use WebServer\Base\Routing\ICostumeMatcher;


interface IRouterConfig
{
	public function getRequest(): IWebRequest;
	public function getClassLoader(): IClassLoader;
	public function getConfigLoader(): IRoutesConfigLoader;
	public function getCostumeMatcher(string $key): ICostumeMatcher;
}