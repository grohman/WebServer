<?php
namespace WebServer\Config;


use Narrator\Narrator;
use Narrator\INarrator;
use Skeleton\Skeleton;

use WebCore\IWebRequest;
use WebServer\Base\Engine\IRouterConfig;
use WebServer\Engine\Routing\RouterConfig;
use WebServer\Exceptions\WebServerException;

use WebServer\Base\Config\IClassLoader;
use WebServer\Base\Config\IServerConfig;
use WebServer\Base\Config\IRoutesConfigLoader;
use WebServer\Base\Routing\ICostumeMatcher;

use WebServer\Config\Routes\ConfigLoader;
use WebServer\Engine\Routing\CostumeMatchersCollection;
use WebServer\ClassLoader\LoadersCollection;


class ServerConfig implements IServerConfig
{
	/** @var Narrator */
	private $narrator;
	
	/** @var ConfigLoader */
	private $configLoader;
	
	/** @var CostumeMatchersCollection */
	private $costumeMatchers;
	
	/** @var IClassLoader */
	private $actualClassLoader = null;
	
	/** @var LoadersCollection|null */
	private $classLoader = null;
	
	/** @var Skeleton */
	private $skeleton;
	
	
	public function __construct()
	{
		$this->skeleton = new Skeleton();
		$this->narrator = new Narrator();
		
		$this->narrator->params()->fromSkeleton($this->skeleton);
		
		$this->costumeMatchers = new CostumeMatchersCollection();
	}
	
	
	public function setConfigDirectory(string $directory): IServerConfig
	{
		$this->configLoader = new ConfigLoader($directory);
		return $this;
	}
	
	/**
	 * @param IClassLoader|IClassLoader[] $loaders
	 * @return IServerConfig
	 */
	public function addLoader($loaders): IServerConfig
	{
		if (!$this->classLoader)
			$this->classLoader = new LoadersCollection();
		
		$this->actualClassLoader = null;
		$this->classLoader->add($loaders);
		
		return $this;
	}
	
	/**
	 * @param ICostumeMatcher|ICostumeMatcher[] $matcher
	 * @return IServerConfig
	 */
	public function addCostumeMatcher($matcher): IServerConfig
	{
		$this->costumeMatchers->add($matcher);
		return $this;
	}
	
	public function addCostumeMatcherForKey(string $key, ICostumeMatcher $matcher): IServerConfig
	{
		$this->costumeMatchers->addForKeys($key, $matcher);
		return $this;
	}
	
	public function getNarrator(): INarrator
	{
		return $this->narrator;
	}
	
	public function getSkeleton(): Skeleton
	{
		return $this->skeleton;
	}
	
	
	public function getLoader(): IRoutesConfigLoader
	{
		return $this->configLoader;
	}
	
	public function getCostumeMatchers(): CostumeMatchersCollection
	{
		return $this->costumeMatchers;
	}
	
	public function getClassLoader(): IClassLoader
	{
		if (!$this->actualClassLoader)
		{
			$default = new DefaultLoader($this->skeleton);
			
			if (!$this->classLoader)
			{
				$this->actualClassLoader = $default;
			}
			else
			{
				$this->actualClassLoader = clone $this->classLoader;
				$this->actualClassLoader->add($default);
			}
		}
		
		return $this->actualClassLoader;
	}
	
	public function getRouterConfig(IWebRequest $request): IRouterConfig
	{
		$config = new RouterConfig();
		
		$config->setRequest($request);
		$config->setMatcherCollection($this->getCostumeMatchers());
		$config->setConfigLoader($this->configLoader);
		$config->setClassLoader($this->getClassLoader());
		
		return $config;
	}
	
	
	public function validate(): void
	{
		if (!$this->configLoader)
		{
			throw new WebServerException(
				'setConfigDirectory method must be called on the config() of the server object'
			);
		}
	}
}