<?php
namespace WebServer\Engine\Routing\Matchers;


use Traitor\TStaticClass;
use WebCore\IWebRequest;
use WebCore\Inputs\FromArray;
use WebCore\Inputs\ArrayInput;
use WebServer\Base\Engine\ITargetCursor;
use WebServer\Exceptions\WebServerFatalException;


class StandardMatcher
{
	use TStaticClass;
	
	
	public const KEYS = [
		'get'		=> true,
		'query'		=> true,
		'post'		=> true,
		'params'	=> true,
		'path'		=> true,
		
		'headers'	=> true,
		'header'	=> true,
		
		'cookies'	=> true,
		'cookie'	=> true,
		
		'env'			=> true,
		'server'		=> true,
		'environment'	=> true,
		
		'ajax'	=> true,
		
		'uri'	=> true,
		'url'	=> true,
		
		'method'	=> true
	];
	
	
	private static function matchParam(IWebRequest $request, ITargetCursor $cursor, string $key, $config): bool
	{
		$key = strtolower($key);
		$with = null;
		
		switch ($key)
		{
			case 'params':
				if ($cursor->hasRouteParams())
				{
					$with = new ArrayInput(array_merge($request->getParamsArray(), $cursor->getRouteParams()));
				}
				else
				{
					$with = $request->getParams();
				}
				
				break;
			
			case 'get':
			case 'query':
				$with = $request->getQuery();
				break;
				
			case 'post':
				$with = $request->getPost();
				break;
				
			case 'path':
				$with = new FromArray($cursor->getRouteParams());
				break;
				
			case 'header':
			case 'headers':
				$with = $request->getHeaders();
				break;
				
			case 'cookie':
			case 'cookies':
				$with = $request->getCookies();
				break;
				
			case 'env':
			case 'server':
			case 'environment':
				$with = new FromArray($_SERVER);
				break;
			
			case 'url':
				return ValueMatcher::matchSingleValue($request->getURL(), $config);
				
			case 'uri':
				return ValueMatcher::matchSingleValue($request->getURI(), $config);
			
			case 'method':
				return ValueMatcher::matchSingleKey($cursor, $request->getMethod(), $config);
				
			case 'ajax':
				return strtolower($request->getHeader('X-Requested-With', '') == 'xmlhttprequest');
				
			default:
				throw new WebServerFatalException("Unexpected key <$key> in route config");
		}
		
		foreach ($config as $paramName => $paramConfig)
		{
			if (!ValueMatcher::match($cursor, $with, $paramName, $paramConfig))
			{
				return false;
			}
		}
		
		return true;
	}
	
	
	public static function match(IWebRequest $request, ITargetCursor $cursor, array $route): bool
	{
		$params = array_intersect_key($route, self::KEYS);
		
		foreach ($params as $key => $value)
		{
			if (!self::matchParam($request, $cursor, $key, $value))
			{
				return false;
			}
		}
		
		return true;
	}
}