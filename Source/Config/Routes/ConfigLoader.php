<?php
namespace WebServer\Config\Routes;


use WebServer\Base\Config\IRoutesConfigLoader;
use WebServer\Exceptions\FileNotFoundException;


class ConfigLoader implements IRoutesConfigLoader
{
	/** @var \WebServer\Base\Config\ILoaderFactory */
	private $factory;
	
	/** @var string */
	private $rootDir;
	
	
	private function loadForPath(string $path): array 
	{
		if (!file_exists($path))
			throw new FileNotFoundException($path);
		
		$loader = $this->factory->get(pathinfo($path, PATHINFO_EXTENSION));
		$result = $loader->load($path);
		
		return $result;
	}
	
	
	public function __construct(string $rootDir)
	{
		if (substr($rootDir, -1) != DIRECTORY_SEPARATOR)
			$rootDir .= DIRECTORY_SEPARATOR;
		
		$this->rootDir = $rootDir;
		$this->factory = new LoaderFactory();
	}


	/**
	 * @param string|string[] $path
	 * @param callable|null $callback
	 * @return array|void
	 */
	public function load($path, ?callable $callback = null)
	{
		if (!is_array($path))
			$path = [$path];
		
		$result = [];
			
		foreach ($path as $item)
		{
			$files = glob($this->rootDir . $item);
			
			foreach ($files as $file)
			{
				$loadedResult = $this->loadForPath($file);
				
				if ($callback)
				{
					if ($callback($loadedResult) === false)
						return;
				}
				else 
				{
					$result = array_merge($result, $loadedResult);
				}
			}
		}
		
		if (!$callback)
		{
			/** @noinspection PhpInconsistentReturnPointsInspection */
			return $result;
		}
	}
}