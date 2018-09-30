<?php
namespace WebServer\Base;


use WebCore\IWebResponse;


interface IResponseParser
{
	/**
	 * @param IActionResponse $response
	 * @return IActionResponse|IWebResponse|mixed|null
	 */
	public function parse(IActionResponse $response);
}