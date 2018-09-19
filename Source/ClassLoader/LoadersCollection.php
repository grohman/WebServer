<?php
namespace WebServer\ClassLoader;


use Structura\Arrays;
use WebServer\Base\IClassLoader;


class LoadersCollection implements IClassLoader
{
	/** @var IClassLoader[] */
	private $loaders = [];
	
	
	private function getElement(string $method, ...$args)
	{
		foreach ($this->loaders as $loader)
		{
			$result = $loader->$method(...$args);
			
			if ($result)
			{
				return $result;
			}
		}
		
		return null;
	}
	
	
	/**
	 * @param IClassLoader|IClassLoader[] 
	 */
	public function add($loaders): void
	{
		$loaders = Arrays::toArray($loaders);
		$this->loaders = array_merge($this->loaders, $loaders);
	}
	
	
	/**
	 * @param string $action
	 * @return callable|null
	 */
	public function getAction(string $action)
	{
		return $this->getElement(__FUNCTION__, $action);
	}
	
	/**
	 * @param string $name
	 * @return object|null
	 */
	public function getDecorator(string $name)
	{
		return $this->getElement(__FUNCTION__, $name);
	}
	
	/**
	 * @param string $name
	 * @return object|null
	 */
	public function getResponseParser(string $name)
	{
		return $this->getElement(__FUNCTION__, $name);
	}
	
	
	/**
	 * @param string $controller
	 * @param string $action
	 * @return object|null
	 */
	public function getController(string $controller, string $action)
	{
		return $this->getElement(__FUNCTION__, $controller, $action);
	}
}