<?php
namespace WebServer\Base\Config;


interface ILoader
{
	public function load(string $path): array;
}