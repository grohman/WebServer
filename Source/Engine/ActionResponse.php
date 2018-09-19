<?php
namespace WebServer\Engine;


use WebCore\IWebResponse;
use WebServer\Base\IActionResponse;
use WebServer\Exceptions\WebServerException;


class ActionResponse implements IActionResponse
{
	private $value = null;
	
	
	public function __construct($value = null)
	{
		if ($value instanceof ActionResponse)
		{
			$value = $value->value;
		}
		else if ($value instanceof IActionResponse)
		{
			$value = $value->get();
		}
		
		$this->value = $value;
	}
	
	
	/**
	 * @return mixed
	 */
	public function get()
	{
		return $this->value;
	}
	
	
	public function getWebResponse(): IWebResponse
	{
		if (!($this->value instanceof IWebResponse))
			throw new WebServerException('Response object is not a ' . IWebResponse::class . ' instance');
		
		return $this->value;
	}
	
	public function isWebResponse(): bool
	{
		return $this->value instanceof IWebResponse;
	}
	
	
	public function isSet(): bool
	{
		return !is_null($this->value);
	}
	
	public function isScalar(): bool
	{
		return is_scalar($this->value);
	}
	
	public function isArray(): bool
	{
		return is_array($this->value);
	}
	
	public function isStdClass(): bool
	{
		return $this->value instanceof \stdClass;
	}
	
	public function isInstanceOf(string $name): bool
	{
		return $this->value instanceof $name;
	}
}