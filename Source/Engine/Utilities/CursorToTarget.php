<?php
namespace WebServer\Engine\Utilities;


use Traitor\TStaticClass;

use WebServer\Base\Config\IClassLoader;
use WebServer\Base\ITargetAction;
use WebServer\Base\Engine\IRouteCursor;
use WebServer\Engine\TargetAction;
use WebServer\Exceptions\RoutingException;


class CursorToTarget
{
	use TStaticClass;
	
	
	private static function createTarget(IClassLoader $loader, IRouteCursor $cursor): TargetAction
	{
		$action = $cursor->getAction();
		$controller = $cursor->getController();
		
		if (is_null($controller) && !is_callable($action))
		{
			throw new RoutingException('Controller was not set');
		}
		else if (!is_string($action))
		{
			throw new RoutingException('Action was not set');
		}
		
		$controllerObject = $loader->getController($controller, $action);
		
		if (!$controller)
		{
			throw new RoutingException("Controller <$controller> is not a valid controller name");
		}
		
		return new TargetAction($controllerObject, $action);
	}
	
	
	private static function getParsers(IClassLoader $loader, TargetAction $action, IRouteCursor $cursor): void
	{
		$parsers = [];
		
		foreach ($cursor->getParsers() as $parserName)
		{
			$parser = $loader->getResponseParser($parserName);
			
			if (!$parser)
			{
				throw new RoutingException("Response parser <$parserName> was not found");
			}
			
			$parsers[] = $parser;
		}
		
		$action->setResponseParsers($parsers);
	}
	
	private static function getDecorators(IClassLoader $loader, TargetAction $action, IRouteCursor $cursor): void
	{
		$decorators = [];
		$callbackDecorators = [];
		
		foreach ($cursor->getDecorators() as $decoratorName)
		{
			if (is_callable($decoratorName))
			{
				$callbackDecorators[] = $decoratorName;
			}
			else
			{
				$decorator = $loader->getDecorator($decoratorName);
				
				if (!$decorator)
				{
					throw new RoutingException("Decorator <$decoratorName> was not found");
				}
				
				$decorators[] = $decorator;
			}
		}
		
		$action->setDecorators($decorators, $callbackDecorators);
	}
	
	
	public static function convert(IClassLoader $loader, IRouteCursor $cursor): ITargetAction
	{
		$targetAction = self::createTarget($loader, $cursor);
		
		self::getParsers($loader, $targetAction, $cursor);
		self::getDecorators($loader, $targetAction, $cursor);
		
		return $targetAction;
	}
}