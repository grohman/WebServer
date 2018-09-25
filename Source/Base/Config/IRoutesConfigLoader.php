<?php
namespace WebServer\Base\Config;


interface IRoutesConfigLoader
{
	/**
	 * @param string|string[] $path
	 * @param callable|null $callback
	 * @return array|void
	 */
	public function load($path, ?callable $callback = null);
}