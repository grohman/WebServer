<?php
namespace WebServer\Config\Routes\Loaders;


use WebServer\Base\Config\ILoader;
use WebServer\Exceptions\WebServerFatalException;


class YmlLoader implements ILoader
{
	public function load(string $path): array
	{
		if (!extension_loaded('YAML'))
			throw new WebServerFatalException("Extension YAML is not loaded");
			
		return yaml_parse(file_get_contents($path));
	}
}