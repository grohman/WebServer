<?php
namespace WebServer\Base\Config;


interface ILoaderFactory
{
	public function get(string $type): ILoader;
}