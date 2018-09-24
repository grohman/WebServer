<?php
namespace WebServer\Engine\Routing\Matchers;


use Traitor\TStaticClass;
use WebServer\Base\Engine\ITargetCursor;
use WebServer\Exceptions\RoutingException;


class RouteMatcher
{
	use TStaticClass;
	
	
	private static function compareRoutePart(ITargetCursor $target, string $config, string $part): bool
	{
		$len = strlen($config);
		
		if ($len <= 2)
			return $part == $config;
		
		if ($config[0] != '{' || $config[$len - 1] != '}')
			return $config == $part;
		
		$config = substr($config, 1, $len - 2);
		$firstSep = strpos($config, ':');
		
		if ($firstSep === false)
		{
			$target->addRouteParam($config, $part);
			return true;
		}
		
		if ($firstSep != 0)
		{
			$name = substr($config, 0, $firstSep);
			$target->addRouteParam($name, $part);
		}
		
		return ValueMatcher::matchSingleKey($target, $part, substr($config, $firstSep + 1));
	}
	
	
	public static function match(ITargetCursor $target, $config): bool
	{
		if (!is_string($config))
			throw new RoutingException("The value of a route config must be a string");
		
		if ($target->getRoutePath() == '')
		{
			return in_array($config, ['', '*']);
		}
		else if ($config == '' || $config == '/')
		{
			return $target->getRoutePath() == '';
		}
		else if ($config == '*')
		{
			$target->setRoutePath('');
			return true;
		}
		
		$isLastCharacterIsSlash = ($config[strlen($config) - 1] == '/');
		
		if ($config[0] == '/')
		{
			if ($isLastCharacterIsSlash)
			{
				$config = substr($config, 1, strlen($config) - 2);
			}
			else
			{
				$config = substr($config, 1);
			}
		}
		else if ($isLastCharacterIsSlash)
		{
			$config = substr($config, 0, strlen($config) - 1);
		}
		
		$config = explode('/', $config);
		$path = explode('/', $target->getRoutePath());
		
		for ($i = 0; $i < count($config); $i++)
		{
			if ($config[$i] == '*')
			{
				$i = count($path);
				break;
			}
			else if (!isset($path[$i]))
			{
				return false;
			}
			else if (!self::compareRoutePart($target, $config[$i], $path[$i]))
			{
				return false;
			}
		}
		
		$path = implode('/', array_slice($path, $i));
		$target->setRoutePath($path);
		
		return true;
	}
}