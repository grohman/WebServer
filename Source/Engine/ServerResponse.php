<?php
namespace WebServer\Engine;


use WebServer\Base\IServerResponse;


class ServerResponse implements IServerResponse
{
	private $value = null;
	
	
	public function __construct($value = null)
	{
		if ($value && $value instanceof IServerResponse)
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
}