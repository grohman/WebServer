<?php
namespace WebServer\Engine;


use Structura\Arrays;
use Objection\Mapper;
use Objection\LiteObject;

use Structura\Map;
use WebCore\Cookie;
use WebCore\IWebResponse;
use WebServer\Response;
use WebServer\Base\IActionResponse;
use WebServer\Base\IResponseParser;


class DefaultParser implements IResponseParser
{
	/**
	 * @param IActionResponse $response
	 * @return IActionResponse|IWebResponse|mixed|null
	 */
	public function parse(IActionResponse $response)
	{
		if (!$response->isSet())
			return Response::OK();
		
		$result = $response->get();
		
		if (is_int($result))
		{
			return Response::with($result);
		}
		else if (is_string($result))
		{
			return Response::string($result);
		}
		else if ($result && is_array($result))
		{
			$first = Arrays::first($result);
			
			if ($first instanceof Cookie)
			{
				return Response::cookies($result);
			}
			else if ($first instanceof LiteObject)
			{
				return Response::with(200, [], Mapper::getJsonFor($result));
			}
			else
			{
				return Response::with(200, [], jsonencode($result));
			}
		}
		else if ($result instanceof Cookie)
		{
			return Response::cookies([$result]);
		}
		else if ($result instanceof \stdClass)
		{
			return Response::with(200, [], jsonencode($result, JSON_FORCE_OBJECT));
		}
		else if ($result instanceof Map)
		{
			return Response::with(200, [], jsonencode($result->toArray(), JSON_FORCE_OBJECT));
		}
		
		return null;
	}
}