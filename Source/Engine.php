<?php
namespace WebServer;


use Narrator\INarrator;
use Structura\Arrays;

use WebCore\IWebRequest;
use WebCore\IWebResponse;
use WebCore\HTTP\Requests\StandardWebRequest;

use WebCore\Validation\Loader\InputLoader;
use WebCore\Validation\Loader\InputValidatorLoader;
use WebCore\Validation\Loader\ScalarLoader;
use WebServer\Base\IActionResponse;
use WebServer\Base\ITargetAction;
use WebServer\Engine\IResponseContainer;
use WebServer\Engine\ResponseContainer;
use WebServer\Engine\Router;
use WebServer\Config\ServerConfig;
use WebServer\Engine\ActionExecutor;
use WebServer\Engine\ActionResponse;
use WebServer\Engine\Utilities\CursorToTarget;
use WebServer\Exceptions\WebServerException;
use WebServer\Exceptions\RouteNotFoundException;


class Engine
{
	/** @var ServerConfig */
	private $config;
	
	/** @var ResponseContainer */
	private $responseContainer;
	
	
	private function getType($of): string
	{
		if (is_null($of))
			return 'NULL';
		else if (is_object($of))
			return get_class($of);
		else
			return gettype($of);
	}
	
	
	/**
	 * @param string|string[] $config
	 * @param IWebRequest $request
	 * @return ITargetAction
	 */
	private function getTarget($config, IWebRequest $request): ITargetAction
	{
		$routerConfig = $this->config->getRouterConfig($request);
		$router = new Router();
		$router->setup($routerConfig);
		
		if (is_string($config) ||Arrays::isNumeric($config))
		{
			$target = $router->parseFiles($config);
		}
		else
		{
			$target = $router->parseConfig($config);
		}
		
		if (!$target)
		{
			throw new RouteNotFoundException();
		}
		else if (!$target->isSet())
		{
			$target->setAction(Response::notFoundCallback());
		}
		
		return CursorToTarget::convert($this->config->getClassLoader(), $target);
	}
	
	private function executeAction(ActionExecutor $actionExecutor, ITargetAction $action): IActionResponse
	{
		$actionExecutor->initialize($action);
		return $actionExecutor->executeAction();
	}
	
	private function setupNarrator(IWebRequest $request)
	{
		$narrator = $this->config->getNarrator();
		$params = $narrator->params();
		
		InputValidatorLoader::register($narrator, $this->config->getSkeleton());
		InputLoader::register($narrator, $request);
		ScalarLoader::register($narrator, $request);
		
		$narrator->params()
			->byType(IWebRequest::class, $request)
			->byType(IResponseContainer::class, $this->responseContainer)
			->addCallback([new InputLoader($request), 'get'])
			->addCallback([new ScalarLoader($request), 'get']);
	}
	
	private function parseResponse(ITargetAction $action, IActionResponse $response): IWebResponse
	{
		if ($response->isWebResponse())
			return $response->get();
		
		foreach ($action->getResponseParsers() as $parser)
		{
			$result = $parser->parse($response);
			
			if ($result instanceof IWebResponse)
				return $result;
			else if (is_null($result))
				continue;
			else
				$response = new ActionResponse($result);
		}
		
		throw new WebServerException('The final response object must be an instance of ' . IWebResponse::class . 
			'. However, got ' . $this->getType($response->get()) . ' instead');
	}
	
	
	public function __construct(ServerConfig $config)
	{
		$this->config = $config;
		$this->responseContainer = new ResponseContainer();
	}
	
	
	/**
	 * @param string|string[] $config
	 * @param IWebRequest|null $request
	 */
	public function execute($config, IWebRequest $request = null): void
	{
		$this->setupNarrator($request);
		
		$narrator		= $this->config->getNarrator();
		$request		= $request ?: StandardWebRequest::current();
		$actionExecutor	= new ActionExecutor($narrator);
		$target			= $this->getTarget($config, $request);
		
		$narrator->params()->byType(ITargetAction::class, $target);
		
		$actionResponse	= $this->executeAction($actionExecutor, $target);
		$webResponse	= $this->parseResponse($target, $actionResponse);
		
		$this->responseContainer->wrap($webResponse);
		$narrator->params()->byType(IWebResponse::class, $webResponse);
		
		$actionExecutor->executeComplete();
		
		$webResponse->apply();
	}
}