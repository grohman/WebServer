<?php
namespace WebServer\Engine\Routing;


use Structura\Map;
use Structura\Arrays;

use WebServer\Base\Routing\ICostumeMatcher;
use WebServer\Exceptions\MissingMatcherException;
use WebServer\Exceptions\WebServerException;


class CostumeMatchersCollection
{
	/** @var Map|ICostumeMatcher[] */
	private $matchers = [];
	
	
	public function add(ICostumeMatcher $matcher): void
	{
		$this->addForKeys($matcher->key(), $matcher);
	}
	
	public function addForKeys($keys, ICostumeMatcher $matcher): void
	{
		$keys = Arrays::toArray($keys);
		
		foreach ($keys as $key)
		{
			if ($this->matchers->has($key))
				throw new WebServerException("A costume matcher for the key <$key> already exists");
			
			$this->matchers->add($key, $matcher);
		}
	}
	
	
	public function get(string $key): ICostumeMatcher
	{
		if (!$this->matchers->tryGet($key, $matcher))
			throw new MissingMatcherException();
		
		return $matcher;
	}
}