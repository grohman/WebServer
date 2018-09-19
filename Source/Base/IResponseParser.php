<?php
namespace WebServer\Base;


use WebCore\IWebResponse;


interface IResponseParser
{
	/**
	 * @param IActionResponse $response
	 * @return IActionResponse|IWebResponse|object|null
	 */
	public function parse(IActionResponse $response);
}