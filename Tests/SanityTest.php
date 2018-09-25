<?php
namespace WebServer;


use PHPUnit\Framework\TestCase;


class SanityTest extends TestCase
{
	public function test_sanity(): void
	{
		$server = new Server();
		$value = new NarratorLoadedValue();
		
		$server->config()->setConfigDirectory(__DIR__ . '/Sanity');
		$narrator = $server->config()->getNarrator();
		$narrator->params()->byName('narratorValue', $value);
		
		$server->execute(['config.*']);
	}
}


class NarratorLoadedValue
{
	public $value = [];
}


class TestController
{
	public function firstAction()
	{
		
	}
	
	public function helloAction()
	{
		
	}
}


class DecoratorA
{
	
}

class DecoratorB
{
	
}


class MainParser
{
	
}