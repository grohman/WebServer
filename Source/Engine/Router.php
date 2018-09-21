<?php
namespace WebServer\Engine;


use WebCore\IWebRequest;
use WebServer\Base\Engine\IRouteCursor;
use WebServer\Base\Engine\IRouterConfig;
use WebServer\Engine\Routing\RouteCursor;
use WebServer\Engine\Routing\Matchers\RouteSetup;
use WebServer\Engine\Routing\Matchers\RouteMatcher;
use WebServer\Engine\Routing\Matchers\StandardMatcher;
use WebServer\Exceptions\RoutingException;
use WebServer\Exceptions\MissingMatcherException;


class Router
{
	/** @var IRouterConfig */
	private $config;
	
	/** @var IWebRequest */
	private $request;
	
	/** @var RouteCursor */
	private $cursor;
	
	
	private function parseRoutePath(array $route): bool
	{
		if ($route['route'] ?? false)
			return true;
		
		return RouteMatcher::match($this->request, $this->cursor, $route['route']);
	}
	
	private function parseStandardParams(array $route): bool
	{
		return StandardMatcher::match($this->request, $this->cursor, $route);
	}
	
	private function parseExtraMatchers(array $route): bool
	{
		$extraValues = array_diff_key($route, StandardMatcher::KEYS);
		
		foreach ($extraValues as $key => $value)
		{
			try
			{
				$matcher = $this->config->getCostumeMatcher($key);
			}
			catch (MissingMatcherException $e)
			{
				throw new RoutingException("Unexpected key $key in router config");
			}
			
			$matcher->setTargetCursor($this->cursor);
			
			if (!$matcher->match($this->request, $value))
			{
				return false;
			}
		}
		
		return true;
	}
	
	private function parseExtraConfig(array $route): bool
	{
		$config = $route['config'] ?? null;
		
		if (!$config)
			return true;
		
		$this->config->getConfigLoader()->load($config, 
			function($config)
				use (&$result)
			{
				$result = $this->parseSingleRoute($config);
				return $result;
			});
		
		return $result;
	}
	
	private function parseIncludes(array $route): bool
	{
		$includes = array_merge($route['include'] ?? [], $route['inc'] ?? [], $route['_'] ?? []);
		$requires = array_merge($route['require'] ?? [], $route['req'] ?? [], $route['*'] ?? []);
		
		$isMatched = false;
		$isRequired = (bool)$requires;
		
		$subRoutes = array_merge(array_values($includes), array_values($requires));
		
		foreach ($subRoutes as $route)
		{
			$this->cursor->push();
			
			$result = $this->parseSingleRoute($route);
			
			$this->cursor->pop();
			
			if ($result)
			{
				break;
			}
			else
			{
				$this->cursor->removeChildren();
			}
		}
		
		return $isRequired ? $isMatched : true;
	}
	
	private function parseSetup(array $route): void
	{
		RouteSetup::setup($this->cursor, $route);
	}
	
	
	private function parseSingleRoute(array $route): bool
	{
		if (!$this->parseRoutePath($route))
			return false;
		
		if (!$this->parseStandardParams($route))
			return false;
		
		if (!$this->parseExtraMatchers($route))
			return false;
		
		if (!$this->parseExtraConfig($route))
			return false;
		
		if (!$this->parseIncludes($route))
			return false;
		
		if (!$this->parseIncludes($route))
			return false;
			
		$this->parseSetup($route);
		
		return true;
	}
	
	
	public function setup(IRouterConfig $config): void
	{
		$this->request = $config->getRequest();
		$this->config = $config;
	}
	
	
	public function parseFiles($config): IRouteCursor
	{
		$result = null;
		
		$this->config->getConfigLoader()->load($config, 
			function($config)
				use (&$result)
			{
				$result = $this->parseConfig($config);
				
				return !($result);
			});
		
		return $result;
	}
	
	public function parseConfig(array $config): ?IRouteCursor
	{
		$this->cursor = new RouteCursor($this->request->getURI());
		$this->cursor->push();
		
		$result = $this->parseSingleRoute($config);
		
		return $result ? $this->cursor : null;
	}
}