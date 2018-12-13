<?php
namespace WebServer\Engine;


use WebCore\Cookie;
use WebCore\IWebResponse;


class ResponseContainer implements IResponseContainer
{
	private $headers 	= [];
	private $body 		= null;
	private $code		= null;
		
	/** @var Cookie[] */
	private $cookies 	= [];
	
	
	public function wrap(IWebResponse $response)
	{
		if ($this->headers)
			$response->addHeaders($this->headers);
		
		if ($this->cookies)
			$response->addCookies($this->cookies);
		
		if ($this->code)
			$response->setCode($this->code);
		
		if ($this->body)
		{
			if (is_callable($this->body))
			{
				$response->setBodyCallback($this->body);
			}
			else
			{
				$response->setBody($this->body);
			}
		}
	}
	
	public function setCode(int $code): void
	{
		$this->code = $code;
	}
	
	public function addHeaders(array $headers): void
	{
		$this->headers = array_merge($this->headers, $headers);
	}
	
	public function setHeader(string $header, ?string $value = null): void
	{
		$this->addHeaders([$header => $value]);
	}
	
	public function hasHeader(string $header): bool
	{
		return isset($this->headers[$header]);
	}
	
	public function addCookies(array $cookies): void
	{
		$this->cookies = array_merge($this->cookies, $cookies);
	}
	
	public function setCookieByName(string $cookie, string $value): void
	{
		$this->cookies[$cookie] = Cookie::create($cookie, (string)$value);
	}
	
	public function setCookie(Cookie $cookie): void
	{
		$this->cookies[$cookie->Name] = $cookie;
	}
	
	/**
	 * @param string $name
	 * @param null|string $value
	 * @param int|string $expire
	 * @param null|string $path
	 * @param null|string $domain
	 * @param bool $secure
	 * @param bool $serverOnly
	 */
	public function createCookie(
		string $name,
		?string $value = null,
		$expire = 0,
		?string $path = null,
		?string $domain = null,
		bool $secure = false,
		bool $serverOnly = false): void
	{
		$this->setCookie(Cookie::create($name, $value, $expire, $path, $domain, $secure, $serverOnly));
	}
	
	public function setBody($body): void
	{
		$this->body = $body;
	}
	
	/**
	 * @param array|int|double|bool|string $body
	 */
	public function setJSON($body): void
	{
		$this->body = jsonencode($body);
	}
}