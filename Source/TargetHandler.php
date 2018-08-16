<?php
namespace WebServer;


use Skeleton\Skeleton;


class TargetHandler
{
	private $controller;
	private $action;
	private $decorators = [];


	public function __construct(?string $controller, string $action)
	{
		$this->controller = $controller;
		$this->action = $action;
	}


	public function addDecorators(array $decorators): void
	{
		$this->decorators = array_merge($this->decorators, $decorators);
	}


	public function getController(): ?string
	{
		return $this->controller;
	}

	public function getAction(): string
	{
		return $this->action;
	}

	public function getActionCallback(Skeleton $skeleton): callable
	{
		if ($this->controller)
		{
			$controller = $skeleton->load($this->controller);
			return [$controller, $this->action];
		}
		else
		{
			return [];
		}
	}
	
	public function getDecorators(): array
	{
		return $this->decorators;
	}
}