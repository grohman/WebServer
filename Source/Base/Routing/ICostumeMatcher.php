<?php
namespace WebServer\Base\Routing;


use WebCore\IWebRequest;


interface ICostumeMatcher
{
	/**
	 * @return string|string[]
	 */
	public function key();
	
	/**
	 * @param IWebRequest $request
	 * @param mixed $value
	 * @return bool
	 */
	public function match(IWebRequest $request, $value): bool; 
}