<?php
namespace WebServer\Base\Routing;


use WebServer\Base\Engine\ITargetCursor;


abstract class AbstractCostumeMatcher implements ICostumeMatcher
{
	/** @var ITargetCursor */
	private $targetCursor;
	
	
	protected function setAction($action): void
	{
		$this->targetCursor->setAction($action);
	}
	
	protected function setController(string $controller): void
	{
		$this->targetCursor->setController($controller);
	}
	
	
	public function setTargetCursor(ITargetCursor $cursor): void
	{
		$this->targetCursor = $cursor;
	}
}