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
	
	
	private const KEYS = [
		'get'			=> true,
		'query'			=> true,
		'post'			=> true,
		'params'		=> true,
		'path'			=> true,
		'headers'		=> true,
		'header'		=> true,
		'cookies'		=> true,
		'cookie'		=> true,
		'env'			=> true,
		'server'		=> true,
		'environment'	=> true,
		'ajax'			=> true,
		'uri'			=> true,
		'url'			=> true,
		'method'		=> true,
		'route'			=> true,
		'action'		=> true,
		'controller'	=> true,
		'include'		=> true,
		'inc'			=> true,
		'_'				=> true,
		'require'		=> true,
		'req'			=> true,
		'*'				=> true,
		'config'		=> true,
		'decorator'		=> true,
		'decorators'	=> true,
		'parser'		=> true,
		'parsers'		=> true
	];
	
	
	private function parseRoutePath(array $route): bool
	{
		if (!isset($route['route']))
			return true;
		
		return RouteMatcher::match($this->cursor, $route['route']);
	}
	
	private function parseStandardParams(array $route): bool
	{
		return StandardMatcher::match($this->request, $this->cursor, $route);
	}
	
	private function parseExtraMatchers(array $route): bool
	{
		$extraValues = array_diff_key($route, self::KEYS);
		
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
		
		$result = false;
		
		$this->config->getConfigLoader()->load($config, 
			function($config)
				use (&$result)
			{
				$result = $this->parseSingleRoute($config, true);
				return $result;
			});
		
		return $result;
	}
	
	private function parseIncludes(array $route): bool
	{
		$includes = array_merge($route['include'] ?? [], $route['inc'] ?? [], $route['_'] ?? []);
		$requires = array_merge($route['require'] ?? [], $route['req'] ?? [], $route['*'] ?? []);
		
		if (!$includes && !$requires)
			return true;
		
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
				$isMatched = true;
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
	
	
	private function parseSingleRoute(array $route, bool $skipRoute = false): bool
	{
		if (!$this->parseRoutePath($route))
		{
			return false;
		}
		else if (!$this->parseStandardParams($route))
		{
			return false;
		}
		else if (!$this->parseExtraMatchers($route))
		{
			return false;
		}
		else if (!$this->parseExtraConfig($route))
		{
			return false;
		}
		else if (!$this->parseIncludes($route))
		{
			return false;
		}
		else if (!$skipRoute && $this->cursor->getRoutePath())
		{
			return false;
		}
			
		$this->parseSetup($route);
		
		return true;
	}
	
	
	public function setup(IRouterConfig $config): void
	{
		$this->request = $config->getRequest();
		$this->config = $config;
	}
	
	
	public function parseFiles($config): ?IRouteCursor
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