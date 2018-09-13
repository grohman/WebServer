<?php
namespace WebServer\Config\Routes;


use WebServer\Base\Config\ILoader;
use WebServer\Base\Config\ILoaderFactory;
use WebServer\Config\Routes\Loaders;
use WebServer\Enum\LoaderType;
use WebServer\Exceptions\LoaderTypeNotFoundException;


class LoaderFactory implements ILoaderFactory
{
	public function get(string $type): ILoader
	{
		switch ($type)
		{
			case LoaderType::INI:
				return new Loaders\IniLoader();
			
			case LoaderType::JSON:
				return new Loaders\JsonLoader();
			
			case LoaderType::PHP:
				return new Loaders\PhpLoader();
			
			case LoaderType::YML:
				return new Loaders\YmlLoader();
			
			default:
				throw new LoaderTypeNotFoundException($type);
		}
	}
}