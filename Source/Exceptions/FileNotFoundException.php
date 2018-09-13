<?php
namespace WebServer\Exceptions;


class FileNotFoundException extends WebServerException
{
	public function __construct(string $path)
	{
		parent::__construct("File with the path $path was not found");
	}
}