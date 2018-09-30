<?php
namespace WebServer\Engine;


use Narrator\INarrator;

use WebServer\Base\ITargetAction;
use WebServer\Base\IActionResponse;
use WebServer\Exceptions\WebServerException;


class ActionExecutor
{
	private const HANDLERS_INIT			= 'init';
	private const HANDLERS_PRE_ACTION	= 'preExecute';
	private const HANDLERS_POST_ACTION	= 'postExecute';
	private const HANDLERS_ON_EXCEPTION	= 'onException';
	private const HANDLERS_DESTROY		= 'destroy';
	
	
	/** @var IActionResponse|null */
	private $response = null;
	
	/** @var INarrator */
	private $narrator;
	
	/** @var ITargetAction */
	private $target;
	
	
	private function invokeMethod(string $method): void
	{
		foreach ($this->target->getDecorators() as $object)
		{
			$this->narrator->invokeMethodIfExists($object, $method);
		}
		
		if ($this->target->hasController())
		{
			$this->narrator->invokeMethodIfExists($this->target->getController(), $method);
		}
	}
	
	private function invokeCallbackDecorators(): void
	{
		foreach ($this->target->getCallbackDecorators() as $callbackDecorator)
		{
			$this->narrator->invoke($callbackDecorator);
		}
	}
	
	private function invokeAction(): void
	{
		$result = $this->narrator->invoke($this->target->getAction());
		$this->response = new ActionResponse($result);
		
		$this->narrator->params()->byType(IActionResponse::class, function () { return $this->response; });
	}
	
	private function invokeMethodWithResponse(string $method, ?INarrator $narrator = null): void
	{
		$narrator = $narrator ?: $this->narrator;
		$controller = $this->target->getController();
		$decorators = $this->target->getDecorators();
		
		foreach ($decorators as $decorator)
		{
			$result = $narrator->invokeMethodIfExists($decorator, $method);
			
			if (!is_null($result))
			{
				$this->response = new ActionResponse($result);
			}
		}
		
		if ($controller)
		{
			$result = $narrator->invokeMethodIfExists($controller, $method);
			
			if (!is_null($result))
			{
				$this->response = new ActionResponse($result);
			}
		}
	}
	
	private function handleException(\Throwable $t): void
	{
		if (!$this->response)
			$this->response = new ActionResponse();
		
		$narrator = clone $this->narrator;
		$narrator->params()->first($t);
		
		$this->invokeMethodWithResponse(self::HANDLERS_ON_EXCEPTION, $narrator);
		
	}
		
	
	public function __construct(INarrator $narrator)
	{
		$this->narrator = $narrator;
		
		$narrator->params()->byType(IActionResponse::class, [$this, 'getServerResponse']);
	}
	
	
	public function getServerResponse(): IActionResponse
	{
		if (!$this->response)
			throw new WebServerException(IActionResponse::class . ' is not available at this point');
		
		return $this->response;
	}
	
	public function initialize(ITargetAction $target): void
	{
		$this->target = $target;
	}
	
	
	public function executeAction(): IActionResponse
	{
		$this->invokeMethod(self::HANDLERS_INIT);
		$this->invokeMethod(self::HANDLERS_PRE_ACTION);
		$this->invokeCallbackDecorators();
		
		try
		{
			$this->invokeAction();
			$this->invokeMethodWithResponse(self::HANDLERS_POST_ACTION);
		}
		catch (\Throwable $t)
		{
			$this->handleException($t);
		}
		
		$this->invokeMethod(self::HANDLERS_DESTROY);
		
		return $this->response;
	}
}