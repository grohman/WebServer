<?php
namespace WebServer;


use PHPUnit\Framework\TestCase;
use Traitor\TStaticClass;
use WebCore\HTTP\Requests\DummyWebRequest;
use WebCore\HTTP\Responses\StandardWebResponse;
use WebCore\IWebRequest;
use WebCore\IWebResponse;
use WebServer\Base\IActionResponse;
use WebServer\Base\IResponseParser;


class SanityTest extends TestCase
{
	public function test_sanity(): void
	{
		$value = new NarratorLoadedValue();
		
		$request = new DummyWebRequest();
		$request->setURI('/v2/targ/hello');
		$request->setMethod('POST');
		
		$server = new Server($request);
		$server->config()->setConfigDirectory(__DIR__ . '/Sanity');
		$narrator = $server->config()->getNarrator();
		$narrator->params()->byName('narratorValue', $value);
		
		
		$server->execute(['config.*']);
		
		
		self::assertEquals(
			[
				[DecoratorA::class, 'init', []],
				[TestController::class, 'init', [$value]],
				[DecoratorA::class, 'preExecute', [$request]],
				[TestController::class, 'preExecute', []],
				
				[TestController::class, 'helloWorld', []],
				
				[DecoratorA::class, 'postExecute', []],
				[TestController::class, 'postExecute', []],
				
				[DecoratorA::class, 'destroy', []],
				[TestController::class, 'destroy', []],
			],
			StaticRequestState::$order
		);
		
		self::assertTrue(StaticRequestState::$responseApplied, 'Apply was not called on the generated web response');
	}
}


class StaticRequestState
{
	use TStaticClass;
	
	
	public static $order = [];
	public static $responseApplied = false;
}


class NarratorLoadedValue
{
	public $value = [];
}


class TestController
{
	public function init(NarratorLoadedValue $narratorValue)
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
	
	public function preExecute()
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
	
	
	public function firstAction()
	{
		throw new \Exception('Should not be called');
	}
	
	public function helloWorld()
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
		return 123;
	}
	
	
	public function postExecute()
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
	
	public function onException()
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
	
	public function destroy()
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
}


class DecoratorA
{
	public function init()
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
	
	public function preExecute(IWebRequest $request)
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
	
	public function postExecute()
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
	
	public function onException()
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
	
	public function destroy()
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
}

class DecoratorB
{
	public function init()
	{
		throw new \Exception('Should not be called');
	}
	
	public function preExecute()
	{
		throw new \Exception('Should not be called');
	}
	
	public function postExecute()
	{
		throw new \Exception('Should not be called');
	}
	
	public function onException()
	{
		throw new \Exception('Should not be called');
	}
	
	public function destroy()
	{
		throw new \Exception('Should not be called');
	}
}


class MainParser implements IResponseParser
{
	/**
	 * @param IActionResponse $response
	 * @return IActionResponse|IWebResponse|object|null|mixed
	 */
	public function parse(IActionResponse $response)
	{
		if ($response->get() !== 123)
			throw new \Exception('Expecting 123 from controller\'s action');
		
		return 'abc';
	}
}


class ExtraParser implements IResponseParser
{
	/**
	 * @param IActionResponse $response
	 * @return IActionResponse|IWebResponse|object|null
	 */
	public function parse(IActionResponse $response)
	{
		if ($response->get() !== 'abc')
			throw new \Exception('Expecting abc from parser');
		
		return new TempWebResponse();
	}
}

class TempWebResponse extends StandardWebResponse
{
	public function apply(): void
	{
		StaticRequestState::$responseApplied = true;
	}
}