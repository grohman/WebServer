<?php
namespace WebServer;


use Structura\Arrays;

use WebCore\IWebRequest;
use WebCore\IWebResponse;
use WebCore\HTTP\Requests\StandardWebRequest;

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
	
	
	private function getType($of): string
	{
		if (is_null($of))
			return 'NULL';
		else if (is_object($of))
			return get_class($of);
		else
			return gettype($of);
	}
	
	
	private function setup(IWebRequest $request): void
	{
		$narrator = $this->config->getNarrator();
		$narrator->params()->byType(IWebRequest::class, $request);
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
	}
	
	
	/**
	 * @param string|string[] $config
	 * @param IWebRequest|null $request
	 */
	public function execute($config, IWebRequest $request = null): void
	{
		$responseWrapper = new ResponseContainer();
		$this->config->getNarrator()->params()->byType(IResponseContainer::class, $responseWrapper);
		
		$actionExecutor = new ActionExecutor($this->config->getNarrator());
		$request = $request ?: StandardWebRequest::current();
		
		$this->setup($request);
		
		$target			= $this->getTarget($config, $request);
		$actionResponse	= $this->executeAction($actionExecutor, $target);
		$webResponse	= $this->parseResponse($target, $actionResponse);
		
		$responseWrapper->wrap($webResponse);
		$this->config->getNarrator()->params()->byType(IWebResponse::class, $webResponse);
		
		$actionExecutor->executeComplete();
		
		$webResponse->apply();
	}
}