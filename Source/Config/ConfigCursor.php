<?php
namespace WebServer\Config;


class ConfigCursor
{
	private $url;
	private $uri = '';
	private $urlParts;
	
	private $controller = null;
	private $action     = null;
	
	private $decorators         = [];
	private $routeParams        = [];
	private $errorHandlers      = [];
	private $responseParsers    = [];
	
	
	private function addURLPart(): bool
	{
		
	}
	
	
	public function __construct(string $url)
	{
		if ($url[0] != '/')
			$url = '/' . $url;
		
		$this->url = $url;
		$this->urlParts = explode('/', $url);
	}
	
	
	public function getURI(): string
	{
		return $this->uri;
	}
	
	public function getController(): ?string
	{
		return $this->controller;
	}
	
	public function getAction(): ?string
	{
		return $this->action;
	}
	
	public function hasAction(): bool
	{
		return (bool)$this->action;
	}
	
	public function getDecorators(): array
	{
		return $this->decorators;
	}
	
	public function getErrorHandlers(): array
	{
		return $this->errorHandlers;
	}
	
	public function getResponseParsers(): array
	{
		return $this->responseParsers;
	}
	
	public function getRouteParams(): array 
	{
		return $this->routeParams;
	}
	
	
	public function addURI(string $part): bool
	{
	}
}