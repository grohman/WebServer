<?php
namespace WebServer\Engine;


use Narrator\Narrator;

use WebServer\Base\IRequestTarget;
use WebServer\Base\IServerResponse;
use WebServer\Exceptions\WebServerException;


class ActionExecutor
{
	private const HANDLERS_INIT			= 'init';
	private const HANDLERS_PRE_ACTION	= 'preAction';
	private const HANDLERS_POST_ACTION	= 'postAction';
	private const HANDLERS_ON_EXCEPTION	= 'onException';
	private const HANDLERS_DESTROY		= 'destroy';
	
	
	/** @var IServerResponse|null */
	private $response = null;
	
	/** @var Narrator */
	private $narrator;
	
	/** @var IRequestTarget */
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
		$this->response = new ServerResponse($result);
	}
	
	private function invokeMethodWithResponse(string $method, ?Narrator $narrator = null): void
	{
		$narrator = $narrator ?: $this->narrator;
		$controller = $this->target->getController();
		$decorators = $this->target->getDecorators();
		
		if ($controller)
		{
			$result = $narrator->invokeMethodIfExists($controller, $method);
			
			if (!is_null($result))
			{
				$this->response = new ServerResponse($result);
			}
		}
		
		foreach ($decorators as $decorator)
		{
			$result = $narrator->invokeMethodIfExists($decorator, $method);
			
			if (!is_null($result))
			{
				$this->response = new ServerResponse($result);
			}
		}
	}
	
	private function handleException(\Throwable $t): void
	{
		if (!$this->response)
			$this->response = new ServerResponse();
		
		$narrator = clone $this->narrator;
		$narrator->params()->first($t);
		
		$this->invokeMethodWithResponse(self::HANDLERS_ON_EXCEPTION, $narrator);
	}
		
	
	public function __construct(Narrator $narrator)
	{
		$this->narrator = $narrator;
		
		$narrator->params()->byType(IServerResponse::class, [$this, 'getServerResponse']);
	}
	
	
	public function getServerResponse(): IServerResponse
	{
		if (!$this->response)
			throw new WebServerException(IServerResponse::class . ' is not available at this point');
		
		return $this->response;
	}
	
	public function initialize(IRequestTarget $target): void
	{
		$this->target = $target;
	}
	
	
	public function executeAction(): IServerResponse
	{
		$this->invokeMethod(self::HANDLERS_INIT);
		$this->invokeMethod(self::HANDLERS_PRE_ACTION);
		$this->invokeCallbackDecorators();
		
		try
		{
			$this->invokeAction();
		}
		catch (\Throwable $t)
		{
			$this->handleException($t);
			return $this->response;
		}
		
		$this->invokeMethodWithResponse(self::HANDLERS_POST_ACTION);
		$this->invokeMethod(self::HANDLERS_DESTROY);
		
		return $this->response;
	}
}