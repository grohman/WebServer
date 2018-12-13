<?php
namespace WebServer\Engine\Routing;


use Structura\Arrays;
use WebServer\Base\Engine\IRouteCursor;
use WebServer\Exceptions\WebServerFatalException;


class RouteCursor implements IRouteCursor
{
	private $depth = 0;
	
	
	private $actions		= [];
	private $controllers	= [];
	private $decorators		= [];
	private $parsers		= [];
	
	private $routePath		= [];
	private $routeParams	= [];
	
	
	private function getFirstValue(array $from)
	{
		for ($i = count($from) - 1; $i >= 0; $i--)
		{
			if (!is_null($from[$i] ?? null))
				return $from[$i];
		}
		
		return null;
	}
	
	/**
	 * @param array $target
	 * @param string|string[] $values
	 * @return array
	 */
	private function addBulkValuesToArray($target, $values): array
	{
		if (!$values)
			return $target;
		
		$values = Arrays::toArray($values);
		
		if (count($target) < $this->depth)
		{
			if ($this->depth > 1)
			{
				$target = array_pad($target, $this->depth - 1, null);
			}
			
			$target[] = $values;
		}
		else
		{
			$target[$this->depth - 1] = array_merge($target[$this->depth - 1] ?? [], $values);
		}
		
		return $target;
	}
	
	
	public function __construct(string $routePath)
	{
		if ($routePath)
		{
			if ($routePath[0] == '/')
				$routePath = substr($routePath, 1);
			
			if ($routePath && $routePath[strlen($routePath) - 1] == '/')
				$routePath = substr($routePath, 0, strlen($routePath) - 1);
		}
			
		$this->routePath = [$routePath];
	}
	
	
	public function getController(): ?string
	{
		return $this->getFirstValue($this->controllers);
	}
	
	/**
	 * @return string|callable
	 */
	public function getAction()
	{
		return $this->getFirstValue($this->actions);
	}
	
	public function isSet(): bool
	{
		$action = $this->getAction();
		$controller = $this->getController();
		
		return is_callable($action) || ($controller && $action);
	}
	
	/**
	 * @return string[]
	 */
	public function getParsers(): array
	{
		$parsers = array_filter($this->parsers);
		return $parsers ? array_merge(...$parsers) : [];
	}
	
	/**
	 * @return string[]|callable[]
	 */
	public function getDecorators(): array
	{
		$decorators = array_filter($this->decorators);
		return $decorators ? array_merge(...$decorators) : [];
	}
	
	public function setAction($action): void
	{
		$this->actions = array_pad($this->actions, $this->depth, null);
		$this->actions[$this->depth - 1] = $action;
	}
	
	public function setController(string $controller): void
	{
		$this->controllers = array_pad($this->controllers, $this->depth, null);
		$this->controllers[$this->depth - 1] = $controller;
	}
	
	/**
	 * @param string|string[] $decorators
	 */
	public function addDecorators($decorators): void
	{
		$this->decorators = $this->addBulkValuesToArray($this->decorators, $decorators);
	}
	
	/**
	 * @param string|string[] $parsers
	 */
	public function addResponseParsers($parsers): void
	{
		$this->parsers = $this->addBulkValuesToArray($this->parsers, $parsers);
	}
	
	
	public function push(): void
	{
		$this->depth++;
	}
	
	public function pop(bool $cleanChildren = false)
	{
		if ($this->depth == 0)
			throw new WebServerFatalException('Can not pop when depth is zero. ' .  
				'Make sure that push and pop are called the same number of times!');
		
		$this->depth--;
		
		if ($cleanChildren)
		{
			$this->removeChildren();
		}
	}
	
	public function removeChildren(): void
	{
		if (count($this->actions) > $this->depth)		array_splice($this->actions,		$this->depth);
		if (count($this->controllers) > $this->depth)	array_splice($this->controllers,	$this->depth);
		if (count($this->decorators) > $this->depth)	array_splice($this->decorators,		$this->depth);
		if (count($this->parsers) > $this->depth)		array_splice($this->parsers,		$this->depth);
		if (count($this->routePath) > $this->depth)		array_splice($this->routePath,		$this->depth);
		if (count($this->routeParams) > $this->depth)	array_splice($this->routeParams,	$this->depth);
	}
	
	
	public function getRoutePath(): string
	{
		return $this->getFirstValue($this->routePath) ?: '';
	}
	
	public function hasRouteParams(): bool
	{
		return (bool)array_filter($this->routeParams);
	}
	
	public function getRouteParams(): array
	{
		$params = array_filter($this->routeParams);
		return $params ? array_merge(...$params) : [];
	}
	
	public function setRoutePath(string $path): void
	{
		$this->routePath = array_pad($this->routePath, $this->depth, null);
		$this->routePath[$this->depth - 1] = $path;
	}
	
	public function setRouteParams(array $params): void
	{
		$this->routeParams = array_pad($this->routeParams, $this->depth, null);
		$this->routeParams[$this->depth - 1] = $params;
	}
	
	public function addRouteParam(string $key, string $value): void
	{
		$index = $this->depth - 1;
		
		if (count($this->routeParams) < $this->depth)
			$this->routeParams = array_pad($this->routeParams, $this->depth, []);
		
		if (!is_array($this->routeParams[$index]))
		{
			$this->routeParams[$index] = [$key => $value];
		}
		else
		{
			$this->routeParams[$index][$key] = $value;
		}
	}
}