<?php
namespace WebServer\Engine\Routing\Matchers;


use Traitor\TStaticClass;

use WebCore\IInput;
use WebServer\Base\Engine\ITargetCursor;
use WebServer\Exceptions\RoutingException;


class ValueMatcher
{
	use TStaticClass;
	
	
	private const ACTION_VAL		= ':action';
	private const CONTROLLER_VAL	= ':controller';
	
	
	public static function matchString(ITargetCursor $cursor, string $value, string $config): bool
	{
		$len = strlen($config);
		
		if ($len >= strlen(self::CONTROLLER_VAL))
		{
			if ($config == self::CONTROLLER_VAL)
			{
				$cursor->setController($value);
				return true;
			}
			else if (substr($config, $len - strlen(self::CONTROLLER_VAL)) == self::CONTROLLER_VAL)
			{
				$config = substr($config, $len - strlen(self::CONTROLLER_VAL));
				$result = self::matchString($cursor, $value, $config);
				
				if ($result)
					$cursor->setController($value);
				
				return $result;
			}
		}
		
		if ($len >= strlen(self::ACTION_VAL))
		{
			if ($config == self::ACTION_VAL)
			{
				$cursor->setAction($value);
				return true;
			}
			else if (substr($config, $len - strlen(self::ACTION_VAL)) == self::ACTION_VAL)
			{
				$config = substr($config, $len - strlen(self::ACTION_VAL));
				$result = self::matchString($cursor, $value, $config);
				
				if ($result)
					$cursor->setAction($value);
				
				return $result;
			}
		}
		
		return self::matchSingleValue($value, $config);
	}
	
	public static function matchSingleValue(string $value, string $config): bool
	{
		$len = strlen($config);
		
		if ($len > 2)
		{
			if ($config[0] == '/' && $config[$len - 1] == '/')
			{
				return preg_match($config, $value) !== 0;
			}
			else if ($config[0] == '(' && $config[$len - 1] == ')')
			{
				$config = explode(',', substr($config, 1, $len - 2));
				return !in_array($value, $config, true);
			}
		}
		
		if (strpos($value, '*') !== false)
		{
			return fnmatch($config, $value);
		}
		
		return $config == $value;
	}
	
	public static function match(ITargetCursor $cursor, IInput $input, string $key, $config): bool
	{
		if (is_bool($config))
		{
			$has = $input->has($key);
			return $has == $config;
		}
		else if (!$input->has($key))
		{
			return false;
		}
		else if (is_array($config))
		{
			foreach ($config as $singleValue)
			{
				if (self::match($cursor, $input, $key, $singleValue))
				{
					return true;
				}
			}
			
			return false;
		}
		else if (is_string($config))
		{
			return self::matchString($cursor, $input->string($key), $config);
		}
		else
		{
			throw new RoutingException("Unexpected value type for param $key");
		}
	}
}