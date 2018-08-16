<?php
namespace WebServer;


class ActionResponse
{
	private $actionResponse		= null;
	private $decoratorsResponse = [];
	
	
	public function setActionResponse($response): void
	{
		$this->actionResponse = $response;
	}
	
	public function addDecoratorResponse($response): void
	{
		$this->decoratorsResponse[] = $response;
	}
	
	
	public function getActionResponse()
	{
		return $this->actionResponse;
	}
	
	public function getDecoratorsResponse(): array 
	{
		return $this->decoratorsResponse; 
	}
	
	public function hasActionResponse(): bool
	{
		return !is_null($this->actionResponse);
	}
	
	public function hasDecoratorsResponse(): bool 
	{
		return (bool)$this->decoratorsResponse;
	}
}