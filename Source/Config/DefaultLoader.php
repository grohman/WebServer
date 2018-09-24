<?php
namespace WebServer\Config;


use Skeleton\Skeleton;
use WebServer\Base\Config\IClassLoader;


class DefaultLoader implements IClassLoader
{
	/** @var Skeleton */
	private $skeleton;
	
	
	public function __construct(Skeleton $skeleton)
	{
		$this->skeleton = $skeleton;
	}
	
	
	/**
	 * @param string $action
	 * @return callable|null
	 */
	public function getAction(string $action)
	{
		return null;
	}
	
	/**
	 * @param string $name
	 * @return object|null
	 */
	public function getDecorator(string $name)
	{
		if (class_exists($name))
			return $this->skeleton->load($name);
		
		return null;
	}
	
	/**
	 * @param string $name
	 * @return object|null
	 */
	public function getResponseParser(string $name)
	{
		if (class_exists($name))
			return $this->skeleton->load($name);
		
		return null;
	}
	
	/**
	 * @param string $controller
	 * @param string $action
	 * @return object|null
	 */
	public function getController(string $controller, string $action)
	{
		if (!class_exists($controller))
			return null;
		
		$reflection = new \ReflectionClass($controller);
		
		if (!$reflection->hasMethod($action))
			return null;
		
		$method = $reflection->getMethod($action);
		
		if (!$method->isPublic())
			return null;
		
		return $this->skeleton->load($controller);
	}
}