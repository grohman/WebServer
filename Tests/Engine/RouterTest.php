<?php
namespace WebServer\Engine;


use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use WebCore\HTTP\Requests\DummyWebRequest;
use WebServer\Base\Engine\IRouteCursor;
use WebServer\Config\Routes\ConfigLoader;
use WebServer\Engine\Routing\CostumeMatchersCollection;
use WebServer\Engine\Routing\RouterConfig;


class RouterTest extends TestCase
{
	/** @var DummyWebRequest */
	private $request;
	
	/** @var RouterConfig */
	private $config;
	
	/** @var Router */
	private $router;
	
	/** @var CostumeMatchersCollection */
	private $matcher;
	
	/** @var ConfigLoader|MockObject */
	private $configLoader;
	
	
	protected function setUp()
	{
		$this->router = new Router();
		$this->config = new RouterConfig();
		$this->request = new DummyWebRequest();
		$this->matcher = new CostumeMatchersCollection();
		$this->configLoader = $this->getMockBuilder(ConfigLoader::class)
			->setConstructorArgs([__DIR__])
			->getMock();
		
		$this->config->setMatcherCollection(new CostumeMatchersCollection());
		$this->config->setRequest($this->request);
		$this->config->setConfigLoader($this->configLoader);
		
		$this->router->setup($this->config);
	}
	
	
	/**
	 * @expectedException \WebServer\Exceptions\RoutingException
	 */
	public function test_poarseConfig_InvalidAction_ExceptionThrown(): void
	{
		$this->router->parseConfig(['action' => 123]);
	}
	
	/**
	 * @expectedException \WebServer\Exceptions\RoutingException
	 */
	public function test_poarseConfig_InvalidController_ExceptionThrown(): void
	{
		$this->router->parseConfig(['controller' => [213]]);
	}
	
	
	public function test_parseConfig_EmptyConfig_ReturnEmptyResult(): void
	{
		$result = $this->router->parseConfig([]);
		self::assertInstanceOf(IRouteCursor::class, $result);
	}
	
	
	public function test_parseConfig_ActionParamPresent_ActionSet(): void
	{
		$result = $this->router->parseConfig(['action' => 'abc']);
		self::assertEquals('abc', $result->getAction());
		
		$f = function () {};
		
		$result = $this->router->parseConfig(['action' => $f]);
		self::assertSame($f, $result->getAction());
	}
	
	public function test_parseConfig_ControllerParamPresent_ControllerSet(): void
	{
		$result = $this->router->parseConfig(['controller' => 'abc']);
		self::assertEquals('abc', $result->getController());
	}
	
	public function test_parseConfig_DecoratorParamPresent_DecoratorSet(): void
	{
		$f1 = function () {};
		$f2 = function () {};
		
		
		$result1 = $this->router->parseConfig([
			'decorator' 	=> [
				'abc',
				$f1
			],
			'decorators'	=> [
				'def',
				$f2
			]
		]);
		
		$result2 = $this->router->parseConfig([
			'decorator' 	=> $f1,
			'decorators'	=> $f2
		]);
		
		
		self::assertSame(
			[
				'abc',
				$f1,
				'def',
				$f2
			], 
			$result1->getDecorators());
		
		
		self::assertSame(
			[
				$f1,
				$f2
			], 
			$result2->getDecorators());
	}
	
	public function test_parseConfig_ParserParamPresent_ParserSet(): void
	{
		$result1 = $this->router->parseConfig([
			'parser' 	=> ['abc'],
			'parsers'	=> ['def']
		]);
		
		$result2 = $this->router->parseConfig([
			'parser' 	=> 'abc',
			'parsers'	=> 'def'
		]);
		
		
		self::assertSame(['abc', 'def'], $result1->getParsers());
		self::assertSame(['abc', 'def'], $result2->getParsers());
	}
	
	
	public function test_parseConfig_DifferentMethodType_ReturnNull(): void
	{
		$this->request->setMethod('GET');
		self::assertNull($this->router->parseConfig(['method' => 'POST']));
	}
	
	public function test_parseConfig_MatchingMethodType_ReturnResult(): void
	{
		$this->request->setMethod('POST');
		self::assertNotNull($this->router->parseConfig(['method' => 'POST']));
	}
	
	public function test_parseConfig_MethodTypeOnOf(): void
	{
		$this->request->setMethod('POST');
		self::assertNotNull($this->router->parseConfig(['method' => '(POST,GET)']));
		
		$this->request->setMethod('POST');
		self::assertNotNull($this->router->parseConfig(['method' => ['POST', 'GET']]));
		
		$this->request->setMethod('DELETE');
		self::assertNull($this->router->parseConfig(['method' => ['POST', 'GET']]));
	}
	
	public function test_parseConfig_MethodRegex_RegexExecutedCorrectly(): void
	{
		$this->request->setMethod('POST');
		self::assertNull($this->router->parseConfig(['method' => '/^G.*$/']));
		self::assertNotNull($this->router->parseConfig(['method' => '/^.*$/']));
	}
	
	public function test_parseConfig_SetMethodAsAction_ActionSet(): void
	{
		$this->request->setMethod('POST');
		$result = $this->router->parseConfig(['method' => '(GET,POST):action']);
		self::assertEquals('POST', $result->getAction());
	}
	
	public function test_parseConfig_SetMethodAsController_ControllerSet(): void
	{
		$this->request->setMethod('POST');
		$result = $this->router->parseConfig(['method' => '(GET,POST):controller']);
		self::assertEquals('POST', $result->getController());
	}
	
	
	public function test_parseConfig_DifferentURL_ReturnNull(): void
	{
		$this->request->setURI('/def');
		$this->request->setHeaders(['HOST' => 'unstablecacao.com']);
		
		self::assertNull($this->router->parseConfig(['uri' => 'defn']));
		self::assertNull($this->router->parseConfig(['url' => 'http://unstablecacao.net']));
	}
	
	public function test_parseConfig_URLIsCorrect_ReturnResult(): void
	{
		$this->request->setURI('/def');
		$this->request->setHeaders(['HOST' => 'unstablecacao.com']);
		
		self::assertNotNull($this->router->parseConfig(['uri' => '/def', 'route' => '*']));
		self::assertNotNull($this->router->parseConfig(['uri' => '(/def,/den)', 'route' => '*']));
		self::assertNotNull($this->router->parseConfig(['uri' => '/^.*/', 'route' => '*']));
		
		self::assertNotNull($this->router->parseConfig(['url' => 'http://unstablecacao.com/def', 'route' => '*']));
		self::assertNotNull($this->router->parseConfig(['url' => '(http://unstablecacao.com/def,http://unstablecacao.com/den)', 'route' => '*']));
		self::assertNotNull($this->router->parseConfig(['url' => '/^.*/', 'route' => '*']));
	}
	
	
	public function test_parseConfig_MissingParam_ReturnNull(): void
	{
		self::assertNull($this->router->parseConfig(['get'			=> ['a' => true]]));
		self::assertNull($this->router->parseConfig(['query'		=> ['a' => true]]));
		self::assertNull($this->router->parseConfig(['post'			=> ['a' => true]]));
		self::assertNull($this->router->parseConfig(['params'		=> ['a' => true]]));
		self::assertNull($this->router->parseConfig(['headers'		=> ['a' => true]]));
		self::assertNull($this->router->parseConfig(['header'		=> ['a' => true]]));
		self::assertNull($this->router->parseConfig(['cookies'		=> ['a' => true]]));
		self::assertNull($this->router->parseConfig(['cookie'		=> ['a' => true]]));
		self::assertNull($this->router->parseConfig(['env'			=> ['a' => true]]));
		self::assertNull($this->router->parseConfig(['server'		=> ['a' => true]]));
		self::assertNull($this->router->parseConfig(['environment'	=> ['a' => true]]));
	}
	
	public function test_parseConfig_ParamRequired_ParamPresent_ReturnResult(): void
	{
		$this->request->setParams(['a' => 'b']);
		self::assertNotNull($this->router->parseConfig(['get'			=> ['a' => true]]));
		self::assertNotNull($this->router->parseConfig(['query'			=> ['a' => true]]));
		self::assertNotNull($this->router->parseConfig(['post'			=> ['a' => true]]));
		self::assertNotNull($this->router->parseConfig(['params'		=> ['a' => true]]));
		
		$this->request->setHeaders(['d' => 'e']);
		self::assertNotNull($this->router->parseConfig(['headers'		=> ['d' => true]]));
		self::assertNotNull($this->router->parseConfig(['header'		=> ['d' => true]]));
		
		$this->request->setCookies(['e' => 'f']);
		self::assertNotNull($this->router->parseConfig(['cookies'		=> ['e' => true]]));
		self::assertNotNull($this->router->parseConfig(['cookie'		=> ['e' => true]]));
		
		$_SERVER['n'] = 'h';
		self::assertNotNull($this->router->parseConfig(['env'			=> ['n' => true]]));
		self::assertNotNull($this->router->parseConfig(['server'		=> ['n' => true]]));
		self::assertNotNull($this->router->parseConfig(['environment'	=> ['n' => true]]));
	}
	
	public function test_parseConfig_OneOfCheck(): void
	{
		$this->request->setParams(['a' => 'b']);
		self::assertNotNull($this->router->parseConfig(['get'			=> ['a' => '(b,c)']]));
		self::assertNotNull($this->router->parseConfig(['query'			=> ['a' => '(b,c)']]));
		self::assertNotNull($this->router->parseConfig(['post'			=> ['a' => '(b,c)']]));
		self::assertNotNull($this->router->parseConfig(['params'		=> ['a' => '(b,c)']]));
		self::assertNull($this->router->parseConfig(['get'			=> ['a' => '(n,c)']]));
		self::assertNull($this->router->parseConfig(['query'		=> ['a' => '(n,c)']]));
		self::assertNull($this->router->parseConfig(['post'			=> ['a' => '(n,c)']]));
		self::assertNull($this->router->parseConfig(['params'		=> ['a' => '(n,c)']]));
		
		$this->request->setHeaders(['d' => 'e']);
		self::assertNotNull($this->router->parseConfig(['headers'		=> ['d' => '(e,c)']]));
		self::assertNotNull($this->router->parseConfig(['header'		=> ['d' => '(e,c)']]));
		self::assertNull($this->router->parseConfig(['headers'		=> ['d' => '(n,c)']]));
		self::assertNull($this->router->parseConfig(['header'		=> ['d' => '(n,c)']]));
		
		$this->request->setCookies(['e' => 'f']);
		self::assertNotNull($this->router->parseConfig(['cookies'		=> ['e' => '(f,c)']]));
		self::assertNotNull($this->router->parseConfig(['cookie'		=> ['e' => '(f,c)']]));
		self::assertNull($this->router->parseConfig(['cookies'		=> ['e' => '(n,c)']]));
		self::assertNull($this->router->parseConfig(['cookie'		=> ['e' => '(n,c)']]));
		
		$_SERVER['n'] = 'h';
		self::assertNotNull($this->router->parseConfig(['env'			=> ['n' => '(b,h)']]));
		self::assertNotNull($this->router->parseConfig(['server'		=> ['n' => '(b,h)']]));
		self::assertNotNull($this->router->parseConfig(['environment'	=> ['n' => '(b,h)']]));
		self::assertNull($this->router->parseConfig(['env'			=> ['n' => '(n,c)']]));
		self::assertNull($this->router->parseConfig(['server'		=> ['n' => '(n,c)']]));
		self::assertNull($this->router->parseConfig(['environment'	=> ['n' => '(n,c)']]));
	}
	
	public function test_parseConfig_RegexCheck(): void
	{
		$this->request->setParams(['a' => 'b']);
		self::assertNotNull($this->router->parseConfig(['get'			=> ['a' => '/^[bc]$/']]));
		self::assertNotNull($this->router->parseConfig(['query'			=> ['a' => '/^[bc]$/']]));
		self::assertNotNull($this->router->parseConfig(['post'			=> ['a' => '/^[bc]$/']]));
		self::assertNotNull($this->router->parseConfig(['params'		=> ['a' => '/^[bc]$/']]));
		self::assertNull($this->router->parseConfig(['get'			=> ['a' => '/^[bc]{2}$/']]));
		self::assertNull($this->router->parseConfig(['query'		=> ['a' => '/^[bc]{2}$/']]));
		self::assertNull($this->router->parseConfig(['post'			=> ['a' => '/^[bc]{2}$/']]));
		self::assertNull($this->router->parseConfig(['params'		=> ['a' => '/^[bc]{2}$/']]));
		
		$this->request->setHeaders(['d' => 'e']);
		self::assertNotNull($this->router->parseConfig(['headers'		=> ['d' => '/^[ec]$/']]));
		self::assertNotNull($this->router->parseConfig(['header'		=> ['d' => '/^[ec]$/']]));
		self::assertNull($this->router->parseConfig(['headers'		=> ['d' => '/^[bc]{2}$/']]));
		self::assertNull($this->router->parseConfig(['header'		=> ['d' => '/^[bc]{2}$/']]));
		
		$this->request->setCookies(['e' => 'f']);
		self::assertNotNull($this->router->parseConfig(['cookies'		=> ['e' => '/^[fc]$/']]));
		self::assertNotNull($this->router->parseConfig(['cookie'		=> ['e' => '/^[fc]$/']]));
		self::assertNull($this->router->parseConfig(['cookies'		=> ['e' => '/^[bc]{2}$/']]));
		self::assertNull($this->router->parseConfig(['cookie'		=> ['e' => '/^[bc]{2}$/']]));
		
		$_SERVER['n'] = 'h';
		self::assertNotNull($this->router->parseConfig(['env'			=> ['n' => '/^[hc]$/']]));
		self::assertNotNull($this->router->parseConfig(['server'		=> ['n' => '/^[hc]$/']]));
		self::assertNotNull($this->router->parseConfig(['environment'	=> ['n' => '/^[hc]$/']]));
		self::assertNull($this->router->parseConfig(['env'			=> ['n' => '/^[bc]{2}$/']]));
		self::assertNull($this->router->parseConfig(['server'		=> ['n' => '/^[bc]{2}$/']]));
		self::assertNull($this->router->parseConfig(['environment'	=> ['n' => '/^[bc]{2}$/']]));
	}
	
	public function test_parseConfig_WildCardMatch(): void
	{
		$this->request->setParams(['a' => 'b']);
		self::assertNotNull($this->router->parseConfig(['get'			=> ['a' => 'b*']]));
		self::assertNotNull($this->router->parseConfig(['query'			=> ['a' => 'b*']]));
		self::assertNotNull($this->router->parseConfig(['post'			=> ['a' => 'b*']]));
		self::assertNotNull($this->router->parseConfig(['params'		=> ['a' => 'b*']]));
		
		$this->request->setHeaders(['d' => 'e']);
		self::assertNotNull($this->router->parseConfig(['headers'		=> ['d' => 'e*']]));
		self::assertNotNull($this->router->parseConfig(['header'		=> ['d' => 'e*']]));
		
		$this->request->setCookies(['e' => 'f']);
		self::assertNotNull($this->router->parseConfig(['cookies'		=> ['e' => 'f*']]));
		self::assertNotNull($this->router->parseConfig(['cookie'		=> ['e' => 'f*']]));
		
		$_SERVER['n'] = 'h';
		self::assertNotNull($this->router->parseConfig(['env'			=> ['n' => 'h*']]));
		self::assertNotNull($this->router->parseConfig(['server'		=> ['n' => 'h*']]));
		self::assertNotNull($this->router->parseConfig(['environment'	=> ['n' => 'h*']]));
	}
	
	public function test_parseConfig_ExactMatch(): void
	{
		$this->request->setParams(['a' => 'b']);
		self::assertNotNull($this->router->parseConfig(['get'			=> ['a' => 'b']]));
		self::assertNotNull($this->router->parseConfig(['query'			=> ['a' => 'b']]));
		self::assertNotNull($this->router->parseConfig(['post'			=> ['a' => 'b']]));
		self::assertNotNull($this->router->parseConfig(['params'		=> ['a' => 'b']]));
		self::assertNull($this->router->parseConfig(['get'			=> ['a' => 'bb']]));
		self::assertNull($this->router->parseConfig(['query'		=> ['a' => 'bb']]));
		self::assertNull($this->router->parseConfig(['post'			=> ['a' => 'bb']]));
		self::assertNull($this->router->parseConfig(['params'		=> ['a' => 'bb']]));
		
		$this->request->setHeaders(['d' => 'e']);
		self::assertNotNull($this->router->parseConfig(['headers'		=> ['d' => 'e']]));
		self::assertNotNull($this->router->parseConfig(['header'		=> ['d' => 'e']]));
		self::assertNull($this->router->parseConfig(['headers'		=> ['d' => 'ee']]));
		self::assertNull($this->router->parseConfig(['header'		=> ['d' => 'ee']]));
		
		$this->request->setCookies(['e' => 'f']);
		self::assertNotNull($this->router->parseConfig(['cookies'		=> ['e' => 'f']]));
		self::assertNotNull($this->router->parseConfig(['cookie'		=> ['e' => 'f']]));
		self::assertNull($this->router->parseConfig(['cookies'		=> ['e' => 'ff']]));
		self::assertNull($this->router->parseConfig(['cookie'		=> ['e' => 'ff']]));
		
		$_SERVER['n'] = 'h';
		self::assertNotNull($this->router->parseConfig(['env'			=> ['n' => 'h']]));
		self::assertNotNull($this->router->parseConfig(['server'		=> ['n' => 'h']]));
		self::assertNotNull($this->router->parseConfig(['environment'	=> ['n' => 'h']]));
		self::assertNull($this->router->parseConfig(['env'			=> ['n' => 'h1']]));
		self::assertNull($this->router->parseConfig(['server'		=> ['n' => 'h1']]));
		self::assertNull($this->router->parseConfig(['environment'	=> ['n' => 'h1']]));
	}
	
	public function test_parseConfig_MatchArray(): void
	{
		$this->request->setParams(['a' => 'b']);
		self::assertNotNull($this->router->parseConfig(['get'			=> ['a' => ['c', '(b,d)']]]));
		self::assertNotNull($this->router->parseConfig(['query'			=> ['a' => ['c', '(b,d)']]]));
		self::assertNotNull($this->router->parseConfig(['post'			=> ['a' => ['c', '(b,d)']]]));
		self::assertNotNull($this->router->parseConfig(['params'		=> ['a' => ['c', '(b,d)']]]));
		self::assertNull($this->router->parseConfig(['get'			=> ['a' => ['c', '(d)']]]));
		self::assertNull($this->router->parseConfig(['query'		=> ['a' => ['c', '(d)']]]));
		self::assertNull($this->router->parseConfig(['post'			=> ['a' => ['c', '(d)']]]));
		self::assertNull($this->router->parseConfig(['params'		=> ['a' => ['c', '(d)']]]));
		
		$this->request->setHeaders(['d' => 'e']);
		self::assertNotNull($this->router->parseConfig(['headers'		=> ['d' => ['c', '(e,d)']]]));
		self::assertNotNull($this->router->parseConfig(['header'		=> ['d' => ['c', '(e,d)']]]));
		self::assertNull($this->router->parseConfig(['headers'		=> ['d' => ['c', '(b,d)']]]));
		self::assertNull($this->router->parseConfig(['header'		=> ['d' => ['c', '(b,d)']]]));
		
		$this->request->setCookies(['e' => 'f']);
		self::assertNotNull($this->router->parseConfig(['cookies'		=> ['e' => ['c', '(b,f)']]]));
		self::assertNotNull($this->router->parseConfig(['cookie'		=> ['e' => ['c', '(b,f)']]]));
		self::assertNull($this->router->parseConfig(['cookies'		=> ['e' => ['c', '(b,d)']]]));
		self::assertNull($this->router->parseConfig(['cookie'		=> ['e' => ['c', '(b,d)']]]));
		
		$_SERVER['n'] = 'h';
		self::assertNotNull($this->router->parseConfig(['env'			=> ['n' => ['c', '(h,d)']]]));
		self::assertNotNull($this->router->parseConfig(['server'		=> ['n' => ['c', '(h,d)']]]));
		self::assertNotNull($this->router->parseConfig(['environment'	=> ['n' => ['c', '(h,d)']]]));
		self::assertNull($this->router->parseConfig(['env'			=> ['n' => ['c', '(b,d)']]]));
		self::assertNull($this->router->parseConfig(['server'		=> ['n' => ['c', '(b,d)']]]));
		self::assertNull($this->router->parseConfig(['environment'	=> ['n' => ['c', '(b,d)']]]));
	}
	
	public function test_parseConfig_ActionSet(): void
	{
		$this->request->setParams(['a' => 'b']);
		self::assertEquals('b', $this->router->parseConfig(['get'			=> ['a' => 'b:action']])->getAction());
		self::assertEquals('b', $this->router->parseConfig(['query'			=> ['a' => 'b:action']])->getAction());
		self::assertEquals('b', $this->router->parseConfig(['post'			=> ['a' => 'b:action']])->getAction());
		self::assertEquals('b', $this->router->parseConfig(['params'		=> ['a' => 'b:action']])->getAction());
		
		$this->request->setHeaders(['d' => 'e']);
		self::assertEquals('e', $this->router->parseConfig(['headers'		=> ['d' => 'e:action']])->getAction());
		self::assertEquals('e', $this->router->parseConfig(['header'		=> ['d' => 'e:action']])->getAction());
		
		$this->request->setCookies(['e' => 'f']);
		self::assertEquals('f', $this->router->parseConfig(['cookies'		=> ['e' => 'f:action']])->getAction());
		self::assertEquals('f', $this->router->parseConfig(['cookie'		=> ['e' => 'f:action']])->getAction());
		
		$_SERVER['n'] = 'h';
		self::assertEquals('h', $this->router->parseConfig(['env'			=> ['n' => 'h:action']])->getAction());
		self::assertEquals('h', $this->router->parseConfig(['server'		=> ['n' => 'h:action']])->getAction());
		self::assertEquals('h', $this->router->parseConfig(['environment'	=> ['n' => 'h:action']])->getAction());
	}
	
	public function test_parseConfig_ControllerSet(): void
	{
		$this->request->setParams(['a' => 'b']);
		self::assertEquals('b', $this->router->parseConfig(['get'			=> ['a' => 'b:controller']])->getController());
		self::assertEquals('b', $this->router->parseConfig(['query'			=> ['a' => 'b:controller']])->getController());
		self::assertEquals('b', $this->router->parseConfig(['post'			=> ['a' => 'b:controller']])->getController());
		self::assertEquals('b', $this->router->parseConfig(['params'		=> ['a' => 'b:controller']])->getController());
		
		$this->request->setHeaders(['d' => 'e']);
		self::assertEquals('e', $this->router->parseConfig(['headers'		=> ['d' => 'e:controller']])->getController());
		self::assertEquals('e', $this->router->parseConfig(['header'		=> ['d' => 'e:controller']])->getController());
		
		$this->request->setCookies(['e' => 'f']);
		self::assertEquals('f', $this->router->parseConfig(['cookies'		=> ['e' => 'f:controller']])->getController());
		self::assertEquals('f', $this->router->parseConfig(['cookie'		=> ['e' => 'f:controller']])->getController());
		
		$_SERVER['n'] = 'h';
		self::assertEquals('h', $this->router->parseConfig(['env'			=> ['n' => 'h:controller']])->getController());
		self::assertEquals('h', $this->router->parseConfig(['server'		=> ['n' => 'h:controller']])->getController());
		self::assertEquals('h', $this->router->parseConfig(['environment'	=> ['n' => 'h:controller']])->getController());
	}
	
	
	public function test_parseConfig_Route_MatchSlashes(): void
	{
		$this->request->setURI('/a');
		
		self::assertNotNull($this->router->parseConfig(['route' => '/a']));
		self::assertNotNull($this->router->parseConfig(['route' => 'a/']));
		self::assertNotNull($this->router->parseConfig(['route' => '/a/']));
	}
	
	public function test_parseConfig_Route_ExactValue(): void
	{
		$this->request->setURI('/a');
		
		self::assertNotNull($this->router->parseConfig(['route' => 'a']));
		self::assertNull($this->router->parseConfig(['route' => 'b']));
	}
	
	public function test_parseConfig_Route_SetAsParam(): void
	{
		$this->request->setURI('/a');
		
		$result = $this->router->parseConfig(['route' => '{abc}']);
		self::assertEquals(['abc' => 'a'], $result->getRouteParams());
	}
	
	public function test_parseConfig_Route_MatchExpressions(): void
	{
		$this->request->setURI('/a');
		
		$result = $this->router->parseConfig(['route' => '{abc:(a,e,f)}']);
		self::assertEquals(['abc' => 'a'], $result->getRouteParams());
		
		$result = $this->router->parseConfig(['route' => '{abc:a*}']);
		self::assertEquals(['abc' => 'a'], $result->getRouteParams());
		
		$result = $this->router->parseConfig(['route' => '{abc:a}']);
		self::assertEquals(['abc' => 'a'], $result->getRouteParams());
	}
	
	public function test_parseConfig_Route_SetAsAction(): void
	{
		$this->request->setURI('/a');
		
		$result = $this->router->parseConfig(['route' => '{abc::action}']);
		self::assertEquals('a', $result->getAction());
	}
	
	public function test_parseConfig_Route_SetAsController(): void
	{
		$this->request->setURI('/a');
		
		$result = $this->router->parseConfig(['route' => '{abc::controller}']);
		self::assertEquals('a', $result->getController());
	}
	
	public function test_parseConfig_Route_SetAsActionOrController(): void
	{
		$this->request->setURI('/a');
		
		$result = $this->router->parseConfig(['route' => '{abc:a:action}']);
		self::assertEquals('a', $result->getAction());
		
		$result = $this->router->parseConfig(['route' => '{abc:a:controller}']);
		self::assertEquals('a', $result->getController());
	}
	
	public function test_parseConfig_Route_SetAsActionOrControllerOnly(): void
	{
		$this->request->setURI('/a');
		
		$result = $this->router->parseConfig(['route' => '{:*:action}']);
		self::assertEquals('a', $result->getAction());
		self::assertEmpty($result->getRouteParams());
		
		$result = $this->router->parseConfig(['route' => '{:*:controller}']);
		self::assertEquals('a', $result->getController());
		self::assertEmpty($result->getRouteParams());
	}
	
	public function test_parseConfig_Route_LongerThenRequired_ReturnNull(): void
	{
		$this->request->setURI('/a/b');
		
		$result = $this->router->parseConfig(['route' => 'a']);
		self::assertNull($result);
	}
	
	public function test_parseConfig_Route_AsteriskRouteMatchingAnything(): void
	{
		$this->request->setURI('/a/b/c');
		
		self::assertNotNull($this->router->parseConfig(['route' => '*']));
		self::assertNotNull($this->router->parseConfig(['route' => '/a/*']));
		self::assertNotNull($this->router->parseConfig(['route' => '/a/b/*']));
	}
	
	public function test_parseConfig_Route_MultipleSlashesMatchEmptyValue(): void
	{
		$this->request->setURI('///');
		
		$result = $this->router->parseConfig(['route' => '{a:}/{b}']);
		self::assertNotNull($result);
		self::assertEquals(['a' => '', 'b' => ''], $result->getRouteParams());
	}
	
	public function test_parseConfig_Route_Sanity(): void
	{
		$this->request->setURI('//a/hello_world/b/');
		
		$result = $this->router->parseConfig(['route' => '{a:}/a/{b:hell*rld}/{:*:controller}']);
		self::assertNotNull($result);
		self::assertEquals(['a' => '', 'b' => 'hello_world'], $result->getRouteParams());
		self::assertEquals('b', $result->getController());
	}
	
	
	public function test_parseConfig_Include_ChildConfigIncluded(): void
	{
		$result = $this->router->parseConfig([
			'inc' => [
				'main' => [
					'action' => 'abc'
				]
			]
		]);
		
		self::assertEquals('abc', $result->getAction());
	}
	
	public function test_parseConfig_Include_ChildOverridesParent(): void
	{
		$result = $this->router->parseConfig([
			'action' => 'nm',
			'inc' => [
				'main' => [
					'action' => 'abc'
				]
			]
		]);
		
		self::assertEquals('abc', $result->getAction());
	}
	
	public function test_parseConfig_Include_ChildDoesNotMatch_ChildDoesNotOverride(): void
	{
		$result = $this->router->parseConfig([
			'action' => 'nm',
			'inc' => [
				'main' => [
					'get' => ['a' => true],
					'action' => 'abc'
				]
			]
		]);
		
		self::assertEquals('nm', $result->getAction());
	}
	
	public function test_parseConfig_Include_DeeperChild(): void
	{
		$result = $this->router->parseConfig([
			'action' => 'nm',
			'inc' => [
				'main' => [
					'action' => 'abc',
					'inc' => [
						'child' => [
							'action' => 'def'
						]
					]
				]
			]
		]);
		
		self::assertEquals('def', $result->getAction());
	}
	
	public function test_parseConfig_Include_DeeperChildNotMatching(): void
	{
		$result = $this->router->parseConfig([
			'action' => 'nm',
			'inc' => [
				'main' => [
					'action' => 'abc',
					'inc' => [
						'child' => [
							'get' => ['a' => true],
							'action' => 'def'
						]
					]
				]
			]
		]);
		
		self::assertEquals('abc', $result->getAction());
	}
	
	public function test_parseConfig_Include_OneOfChildrenMatching(): void
	{
		$result = $this->router->parseConfig([
			'action' => 'nm',
			'inc' => [
				'main' => [
					'action' => 'abc',
					'get' => ['a' => true]
				],
				'b' => [
					'action' => 'b'
				],
				'a' => [
					'action' => 'a'
				]
			]
		]);
		
		self::assertEquals('b', $result->getAction());
	}
	
	public function test_parseConfig_IncludeRequireSynonyms(): void
	{
		$result = $this->router->parseConfig(['include' => ['b' => ['action' => 'b']]]);
		self::assertEquals('b', $result->getAction());
		
		$result = $this->router->parseConfig(['inc' => ['b' => ['action' => 'b']]]);
		self::assertEquals('b', $result->getAction());
		
		$result = $this->router->parseConfig(['_' => ['b' => ['action' => 'b']]]);
		self::assertEquals('b', $result->getAction());
		
		$result = $this->router->parseConfig(['require' => ['b' => ['action' => 'b']]]);
		self::assertEquals('b', $result->getAction());
		
		$result = $this->router->parseConfig(['req' => ['b' => ['action' => 'b']]]);
		self::assertEquals('b', $result->getAction());
		
		$result = $this->router->parseConfig(['*' => ['b' => ['action' => 'b']]]);
		self::assertEquals('b', $result->getAction());
	}
	
	public function test_sanity(): void
	{
		$this->request->setURI('/hello/world');
		$this->request->setParams(['a' => 'b']);
		
		$config = 
		[
			'decorator' => 'always',
			'parsers' => [
				'parserA',
				'parserB'
			],
			
			'action'		=> 'not-found',
			'controller'	=> 'default',
			
			'req' => 
			[
				'first' => 
				[
					'action' => 'first',
					'route' => '{a}',
					'get' => [
						'a' => true,
					],
					'header' => [
						'a' => true
					]
				],
				'second' =>  
				[
					'decorator' => 'not-this',
					'parser' => 'not-this',
					'action' => 'second',
					'route' => '{b}/{c}',
					'get' => ['a' => true],
					'req' => 
					[
						'child' => 
						[
							'route' => '{d}'
						]
					]
				],
				'third' => 
				[
					'action' => 'third',
					'route' => '{d}',
					'req' =>
					[
						'child' => 
						[
							'decorator' => ['plus'],
							'parser' => 'p',
							'route' => 'world'
						]
					]
				]
			]
		];
		
		$result = $this->router->parseConfig($config);
		
		self::assertEquals('third', $result->getAction());
		self::assertEquals('default', $result->getController());
		self::assertEquals(['always', 'plus'], $result->getDecorators());
		self::assertEquals(['parserA', 'parserB', 'p'], $result->getParsers());
	}
	
	
	public function test_parseConfig_config_ConfigIncluded()
	{
		$this->configLoader
			->expects($this->once())
			->method('load')
			->with(
				'abc',
				$this->callback(
					function(callable $callback)
					{
						return $callback(['action' => 'a']);
					}
				)
			);
		
		$result = $this->router->parseConfig(['config' => 'abc']);
		
		self::assertEquals('a', $result->getAction());
	}
}