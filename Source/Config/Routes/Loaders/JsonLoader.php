<?php
namespace WebServer\Config\Routes\Loaders;


use WebServer\Base\Config\ILoader;


class JsonLoader implements ILoader
{
	public function load(string $path): array
	{
		$content = file_get_contents($path);
		return jsondecode($content, true);
	}
}