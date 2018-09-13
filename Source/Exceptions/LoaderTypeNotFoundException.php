<?php
namespace WebServer\Exceptions;


class LoaderTypeNotFoundException extends WebServerException
{
	public function __construct(string $type)
	{
		parent::__construct("Loader of type $type was not found");
	}
}