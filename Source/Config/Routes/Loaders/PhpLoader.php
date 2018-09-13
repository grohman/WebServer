<?php
namespace WebServer\Config\Routes\Loaders;


use WebServer\Base\Config\ILoader;


class PhpLoader implements ILoader
{
	public function load(string $path): array
	{
		return include $path;
	}
}