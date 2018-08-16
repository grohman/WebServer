<?php
namespace WebServer\Engine;


use WebCore\IWebRequest;
use WebServer\WebServerScope;


class ParametersResolver
{
	/** @var WebServerScope */
	private $scope;
	
	/** @var IWebRequest */
	private $request;
	
	
	private function resolveString(\ReflectionParameter $parameter): ?string
	{
		
		
		return null;
	}
	
	
	public function __construct(WebServerScope $scope)
	{
		$this->scope = $scope;
		$this->request = $scope->webRequest();
	}
	
	
	public function resolve(\ReflectionParameter $parameter, bool &$isFound)
	{
		$isFound = true;
		
		
		if ($parameter->hasType())
		{
			
		}
		
		
		$isFound = false;
		return null;
	}
	
	public static function initialize(WebServerScope $scope): void
	{
		$narrator = $scope->narrator();
		$params = $narrator->params();
		
		$object = new static($scope);
		
		$params->addCallback([$object, 'resolve']);
	}
}