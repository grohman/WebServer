<?php
namespace WebServer\Base\Config;


use Narrator\INarrator;
use Skeleton\Base\ISkeletonSource;
use Skeleton\Skeleton;
use WebServer\Base\Routing\ICostumeMatcher;


interface IServerConfig
{
	public function setConfigDirectory(string $directory): IServerConfig;
	
	/**
	 * @param IClassLoader|IClassLoader[] $loaders
	 * @return IServerConfig
	 */
	public function addLoader($loaders): IServerConfig;
	
	/**
	 * @param ICostumeMatcher|ICostumeMatcher[] $matcher
	 * @return IServerConfig
	 */
	public function addCostumeMatcher($matcher): IServerConfig;
	public function addCostumeMatcherForKey(string $key, ICostumeMatcher $matcher): IServerConfig;
	
	public function getNarrator(): INarrator;
	public function getSkeleton(): Skeleton;
}