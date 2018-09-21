<?php
namespace WebServer\Engine\Routing\Matchers;


use Traitor\TStaticClass;
use WebServer\Base\Engine\ITargetCursor;
use WebServer\Exceptions\RoutingException;


class RouteSetup
{
	use TStaticClass;
	
	
	public static function setup(ITargetCursor $cursor, array $route): void
	{
		if (isset($route['action']))
		{
			$action = $route['action'];
			
			if (!is_string($action) || !is_callable($action))
				throw new RoutingException("<action> must be a string or callable");
			
			$cursor->setAction($action);
		}
		
		if (isset($route['controller']))
		{
			$controller = $route['controller'];
			
			if (!is_string($controller))
				throw new RoutingException("<controller> must be a string");
			
			$cursor->setController($controller);
		}
	}
}