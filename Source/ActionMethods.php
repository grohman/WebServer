<?php
namespace WebServer;


use Traitor\TConstsClass;


class ActionMethods
{
	use TConstsClass;
	
	
	public const INIT			= 'init';
	public const PRE_EXECUTE	= 'preExecute';
	public const POST_EXECUTE	= 'postExecute';
	public const FINALIZE		= 'finalize';
}