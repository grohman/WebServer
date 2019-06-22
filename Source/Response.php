<?php
namespace WebServer;


use Traitor\TStaticClass;

use WebCore\Cookie;
use WebCore\IWebResponse;
use WebCore\HTTP\Responses\StandardWebResponse;
use WebServer\View\SimpleFile;


class Response
{
	use TStaticClass;
	
	
	/**
	 * @param int $code
	 * @param array $headers
	 * @param null|string $body
	 * @param Cookie[] $cookies
	 * @return IWebResponse
	 */
	public static function with(int $code = 200, array $headers = [], ?string $body = null, array $cookies = []): IWebResponse
	{
		$response = new StandardWebResponse();
		
		$response->setCode($code);
		$response->setCookies($cookies);
		$response->setHeaders($headers);
		
		if (!is_null($body))
		    $response->setBody($body);
		
		return $response;
	}
	
	
	public static function include(string $path, array $data = [], int $code = 200): IWebResponse 
	{
		$response = self::with($code);
		$response->setBodyCallback(SimpleFile::createCallback($path, $data));
		
		return $response;
	}
	
	
	public static function OK(): IWebResponse
	{
		return self::with();
	}
	
	/**
	 * @param string $to
	 * @param bool|int $isTemporary If int, will be used as the code.
	 * @return IWebResponse
	 */
	public static function redirect(string $to, $isTemporary): IWebResponse
	{
		if (is_bool($isTemporary))
		{
			$code = $isTemporary ? 307 : 301;
		}
		else
		{
			$code = $isTemporary;
		}
		
		return self::with(
			$code,
			['Location' => $to]
		);
	}
	
	public static function temporaryRedirect(string $to): IWebResponse
	{
		return self::redirect($to, true);
	}
	
	public static function permanentlyRedirect(string $to): IWebResponse
	{
		return self::redirect($to, false);
	}
	
	public static function string(string $body, int $code = 200): IWebResponse
	{
		return self::with($code, [], $body, []);
	}
	
	/**
	 * @param Cookie[] $cookies
	 * @param int $code
	 * @return IWebResponse
	 */
	public static function cookies(array $cookies, int $code = 200): IWebResponse
	{
		return self::with($code, [], null, $cookies);
	}
	
	/**
	 * @param string|Cookie $name
	 * @param null|string $value
	 * @param int $expire
	 * @param null|string $path
	 * @param null|string $domain
	 * @param bool $secure
	 * @param bool $serverOnly
	 * @return IWebResponse
	 */
	public static function cookie(
		$name, 
		?string $value = null, 
		$expire = 0, 
		?string $path = null, 
		?string $domain = null, 
		bool $secure = false, 
		bool $serverOnly = false): IWebResponse
	{
		$cookie = (is_string($name) ?
			Cookie::create($name, $value, $expire, $path, $domain, $secure, $serverOnly) :
			$name);
		
		return self::cookies([$cookie]);
	}
	
	
	public static function notFoundCallback(): callable 
	{
		return function(): IWebResponse { return self::withNotFound(); };
	}
	
	
	public static function withUnauthorized(): IWebResponse 
	{
		return self::with(401);
	}
	
	public static function withPaymentRequired(): IWebResponse 
	{
		return self::with(402);
	}
	
	public static function withForbidden(): IWebResponse 
	{
		return self::with(403);
	}
	
	public static function withNotFound(): IWebResponse 
	{
		return self::with(404);
	}
}