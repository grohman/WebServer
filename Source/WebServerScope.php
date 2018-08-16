<?php
namespace WebServer;


use Narrator\Narrator;
use Skeleton\Skeleton;

class WebServerScope
{
	public function narrator(): Narrator
	{
		return new Narrator();
	}
	
	public function skeleton(): Skeleton
	{
		return new Skeleton();
	}
}