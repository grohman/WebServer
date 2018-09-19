<?php
namespace WebServer\Exceptions;


class RoutingException extends WebServerException
{
	public function __construct(string $message = '')
	{
		parent::__construct($message);
	}
}