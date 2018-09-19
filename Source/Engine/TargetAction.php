<?php
namespace WebServer\Engine;


use WebServer\Base\IResponseParser;
use WebServer\Base\ITargetAction;


class TargetAction implements ITargetAction
{
	/** @var ?object */
	private $controller;
	
	/** @var callable|string */
	private $action;
	
	/** @var object[] */
	private $decorators;
	
	/** @var callable[] */
	private $callbackDecorators;
	
	/** @var IResponseParser[] */
	private $responseParsers;
	
	
	/**
	 * TargetAction constructor.
	 * @param ?object $controller
	 * @param string|callable $action
	 */
	public function __construct($controller, $action)
	{
		$this->controller = $controller;
		$this->action = $action;
	}
	
	
	/**
	 * @param array $decorators
	 * @param callable[] $callbackDecorators
	 */
	public function setDecorators(array $decorators, array $callbackDecorators): void
	{
		$this->decorators = $decorators;
		$this->callbackDecorators = $callbackDecorators;
	}
	
	/**
	 * @param IResponseParser[] $parsers
	 */
	public function setResponseParsers(array $parsers): void
	{
		$this->responseParsers = $parsers;
	}
	
	
	public function hasController(): bool
	{
		return !is_null($this->controller);
	}
	
	/**
	 * @return object|null
	 */
	public function getController()
	{
		return $this->controller;
	}
	
	public function getAction(): callable
	{
		if (is_null($this->controller))
			return $this->action;
		
		return [$this->controller, $this->action];
	}
	
	/**
	 * @return callable[]
	 */
	public function getCallbackDecorators(): array
	{
		return $this->callbackDecorators;
	}
	
	/**
	 * @return object[]
	 */
	public function getDecorators(): array
	{
		return $this->decorators;
	}
	
	/**
	 * @return IResponseParser[]
	 */
	public function getResponseParsers(): array
	{
		return $this->responseParsers;
	}
}