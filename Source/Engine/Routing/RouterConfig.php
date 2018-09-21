<?php
namespace WebServer\Engine\Routing;


use WebCore\IWebRequest;
use WebServer\Base\Config\IRoutesConfigLoader;
use WebServer\Base\Engine\IRouterConfig;
use WebServer\Base\IClassLoader;
use WebServer\Base\Routing\ICostumeMatcher;


class RouterConfig implements IRouterConfig
{
	/** @var IWebRequest */
	private $request;
	
	/** @var IClassLoader */
	private $classLoaders;
	
	/** @var IRoutesConfigLoader */
	private $configLoader;
	
	/** @var CostumeMatchersCollection */
	private $matchers;
	
	
	public function setRequest(IWebRequest $request): void
	{
		$this->request = $request;
	}
	
	public function setClassLoader(IClassLoader $loader): void
	{
		$this->classLoaders = $loader;
	}
	
	public function setConfigLoader(IRoutesConfigLoader $loader): void
	{
		$this->configLoader = $loader;
	}
	
	public function setMatcherCollection(CostumeMatchersCollection $collection): void
	{
		$this->matchers = $collection;
	}
	
	
	public function getRequest(): IWebRequest
	{
		return $this->request;
	}
	
	public function getClassLoader(): IClassLoader
	{
		return $this->classLoaders;
	}
	
	public function getConfigLoader(): IRoutesConfigLoader
	{
		return $this->configLoader;
	}
	
	public function getCostumeMatcher(string $key): ICostumeMatcher
	{
		return $this->matchers->get($key);
	}
}