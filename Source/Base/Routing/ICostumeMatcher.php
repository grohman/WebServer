<?php
namespace WebServer\Base\Routing;


use WebCore\IWebRequest;
use WebServer\Base\Engine\ITargetCursor;


interface ICostumeMatcher
{
	/**
	 * @return string|string[]
	 */
	public function key();
	
	public function setTargetCursor(ITargetCursor $cursor): void; 
	
	/**
	 * @param IWebRequest $request
	 * @param mixed $value
	 * @return bool
	 */
	public function match(IWebRequest $request, $value): bool; 
}