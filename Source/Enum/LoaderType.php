<?php
namespace WebServer\Enum;


use Traitor\TEnum;


class LoaderType
{
	use TEnum;
	
	
	public const INI 	= 'ini';
	public const JSON 	= 'json';
	public const PHP 	= 'php';
	public const YML 	= 'yml';
}