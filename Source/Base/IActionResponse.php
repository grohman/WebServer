<?php
namespace WebServer\Base;


use WebCore\IWebResponse;


interface IActionResponse
{
	/**
	 * @return mixed
	 */
	public function get();
	
	public function getWebResponse(): IWebResponse;
	public function isWebResponse(): bool;
	
	public function isSet(): bool;
	public function isArray(): bool;
	public function isScalar(): bool;
	public function isStdClass(): bool;
	public function isInstanceOf(string $name): bool;
}