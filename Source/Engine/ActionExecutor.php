<?php
namespace WebServer\Engine;


use WebServer\TargetHandler;
use WebServer\WebServerScope;


class ActionExecutor
{
	/** @var WebServerScope */
	private $scope;
	
	
	private function filterDecorators(TargetHandler $handler): array
	{
		$callbacks = [];
		
		
	}
	
	private function invoke($class, string $function): void
	{
		
	}
	
	
	public function __construct(WebServerScope $scope)
	{
		$this->scope = $scope;
	}
	
	
	public function execute(TargetHandler $handler)
	{
		$action = $handler->getActionCallback($this->scope->skeleton());
		
		
	}
}