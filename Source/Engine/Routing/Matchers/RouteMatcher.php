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
		if (strlen($config) <= 2)
			return $part == $config;
		
		if ($config[0] != '{' || $config[1] != '}')
			return $config == $part;
		
		$config = substr(1, strlen($config) - 2);
		$firstSep = strpos($config, ':');
		
		if ($firstSep === false)
		{
			$target->addRouteParam($config, $part);
			return true;
		}
		
		$name = substr($config, 0, $firstSep);
		$target->addRouteParam($name, $part);
		
		return ValueMatcher::matchString($target, $part, substr($config, $firstSep + 1));
	}
	
	
	public static function match(ITargetCursor $target, $config): bool
	{
		if (!is_string($config))
			throw new RoutingException("The value of a route config must be a string");
		
		if ($config == '' || $config == '/')
			return $target->getRoutePath() == '';
		
		$isLastCharacterIsSlash = $config[strlen($config) - 1];
		
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
			if (!isset($path[$i]))
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