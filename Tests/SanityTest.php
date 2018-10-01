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
	public function setUp()
	{
		StaticRequestState::$responseApplied = false;
		StaticRequestState::$order = [];
	}
	
	
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
		
		
		TestController::$result = 123;
		MainParser::$expected = 123;
		
		$server->execute(['config.*']);
		
		
		self::assertEquals(
			[
				[DecoratorA::class, 'init', []],
				[TestController::class, 'init', [$value]],
				[DecoratorA::class, 'before', [$request]],
				[TestController::class, 'before', []],
				
				[TestController::class, 'helloWorld', []],
				
				[DecoratorA::class, 'after', []],
				[TestController::class, 'after', []],
				
				[DecoratorA::class, 'complete', []],
				[TestController::class, 'complete', []],
			],
			StaticRequestState::$order
		);
		
		self::assertTrue(StaticRequestState::$responseApplied, 'Apply was not called on the generated web response');
	}
	
	public function test_sanity_for_exception(): void
	{
		$value = new NarratorLoadedValue();
		
		$request = new DummyWebRequest();
		$request->setURI('/v2/targ/hello');
		$request->setMethod('POST');
		
		$server = new Server($request);
		$server->config()->setConfigDirectory(__DIR__ . '/Sanity');
		$narrator = $server->config()->getNarrator();
		$narrator->params()->byName('narratorValue', $value);
		
		
		TestController::$result = new \Exception('Some error in controller');
		TestController::$errorResult = 456;
		MainParser::$expected = 456;
		
		$server->execute(['config.*']);
		
		
		self::assertEquals(
			[
				[DecoratorA::class, 'init', []],
				[TestController::class, 'init', [$value]],
				[DecoratorA::class, 'before', [$request]],
				[TestController::class, 'before', []],
				
				[TestController::class, 'helloWorld', []],
				
				[DecoratorA::class, 'onException', [TestController::$result]],
				[TestController::class, 'onException', [TestController::$result]],
				
				[DecoratorA::class, 'complete', []],
				[TestController::class, 'complete', []],
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
	public $value = 789;
}


class TestController
{
	public static $result = 123;
	public static $errorResult = 456;
	
	
	public function init(NarratorLoadedValue $narratorValue)
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
	
	public function before()
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
		
		$r = self::$result;
		
		if ($r instanceof \Throwable)
			throw $r;
		
		return $r;
	}
	
	
	public function after()
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
	
	public function onException(\Exception $e)
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
		return self::$errorResult;
	}
	
	public function complete()
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
	
	public function before(IWebRequest $request)
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
	
	public function after()
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
	
	public function onException(\Exception $e)
	{
		StaticRequestState::$order[] = [__CLASS__, __FUNCTION__, func_get_args()];
	}
	
	public function complete()
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
	
	public function before()
	{
		throw new \Exception('Should not be called');
	}
	
	public function after()
	{
		throw new \Exception('Should not be called');
	}
	
	public function onException()
	{
		throw new \Exception('Should not be called');
	}
	
	public function complete()
	{
		throw new \Exception('Should not be called');
	}
}


class MainParser implements IResponseParser
{
	public static $expected = 123;
	
	/**
	 * @param IActionResponse $response
	 * @return IActionResponse|IWebResponse|object|null|mixed
	 */
	public function parse(IActionResponse $response)
	{
		$expected = self::$expected;
			
		if ($response->get() !== $expected)
			throw new \Exception("Expecting $expected from controller's action");
		
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