<?php
namespace WebServer\Engine;


use WebServer\ActionResult;
use WebServer\ActionMethods;
use WebServer\TargetHandler;
use WebServer\WebServerScope;


class ActionExecutor
{
	/** @var WebServerScope */
	private $scope;
	
	/** @var ActionResult */
	private $response;
	
	
	private function sortDecorators(TargetHandler $handler): array
	{
		$callbacks = [];
		$classes = [];
		
		foreach ($handler->getDecorators() as $decorator)
		{
			if (is_callable($decorator))
			{
				$callbacks[] = $decorator;
			}
			else if (is_object($decorator))
			{
				$classes[] = $decorator;
			}
			else if (is_string($decorator))
			{
				$classes[] = $this->scope->skeleton()->load($decorator);
			}
		}
		
		return [$callbacks, $classes];
	}
	
	private function invoke(array $classes, string $method): void
	{
		foreach ($classes as $class)
		{
			if (!method_exists($class, $method))
				continue;
			
			$result = $this->scope->narrator()->invoke([$class, $method]);
			
			if (!is_null($result))
			{
				$this->response->addDecoratorResponse($result);
			}
		}
	}
	
	private function invokeCallbacks(array $callbacks): void
	{
		foreach ($callbacks as $callback)
		{
			$result = $this->scope->narrator()->invoke($callback);
			
			if (!is_null($result))
			{
				$this->response->addDecoratorResponse($result);
			}
		}
	}
	
	private function invokeAction($action)
	{
		$this->response->setActionResponse($this->scope->narrator()->invoke($action));
	}
	
	
	public function __construct(WebServerScope $scope)
	{
		$this->scope = $scope;
	}
	
	
	public function execute(TargetHandler $handler): void
	{
		$action = $handler->getActionCallback($this->scope->skeleton());
		list($callbacks, $classes) = $this->sortDecorators($handler);
		
		
		$this->invoke($classes, ActionMethods::INIT);
		$this->invokeCallbacks($callbacks);
		$this->invoke($classes, ActionMethods::PRE_EXECUTE);
		
		$this->invokeAction($action);
		
		$this->invoke($classes, ActionMethods::POST_EXECUTE);
		$this->invoke($classes, ActionMethods::FINALIZE);
	}
}