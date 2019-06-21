<?php
namespace WebServer\View;


class SimpleFile
{
	public function __construct(array $data)
	{
		foreach ($data as $key => $value)
		{
			$this->$key = $value;
		}
	}
	
	
	public function include(string $path): void
	{
		/** @noinspection PhpIncludeInspection */
		require_once $path;
	}
	
	
	public static function createCallback(string $path, array $data = []): callable
	{
		$fileGenerator = new SimpleFile($data);
		
		return function() 
			use ($fileGenerator, $path)
		{
			$fileGenerator->include($path);
		};
	}
}