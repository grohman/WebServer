<?php
namespace WebServer\Config;


interface IConfigLoader
{
	/**
	 * @param string $source
	 * @return array
	 */
	public function load(string $source): array;
}