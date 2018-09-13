<?php
namespace WebServer\Config\Routes\Loaders;


use WebServer\Base\Config\ILoader;


class IniLoader implements ILoader
{
	private function getArrayFromKeyAndValue(string $key, $value): array 
	{
		$keys = explode('.', $key);
		$count = count($keys);
		
		$result[$keys[$count - 1]] = $value;
		
		for ($i = $count - 2; $i >= 0; $i--)
		{
			$result[$keys[$i]] = $result;
		}
		
		return $result;
	}
	
	
	public function load(string $path): array
	{
		$content = parse_ini_file($path);
		
		foreach ($content as $key => $value)
		{
			if (strpos($key, '.') !== false)
			{
				unset($content[$key]);
				$content = array_merge($content, $this->getArrayFromKeyAndValue($key, $value));
			}
		}
		
		return $content;
	}
}