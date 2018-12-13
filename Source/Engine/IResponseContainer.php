<?php
namespace WebServer\Engine;


use WebCore\Cookie;


interface IResponseContainer
{
	public function setCode(int $code): void;
	public function addHeaders(array $headers): void;
	public function setHeader(string $header, ?string $value = null): void;
	public function hasHeader(string $header): bool;
	public function addCookies(array $cookies): void;
	public function setCookieByName(string $cookie, string $value): void;
	public function setCookie(Cookie $cookie): void;
	
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
		bool $serverOnly = false): void;
	
	public function setBody($body): void;
	
	/**
	 * @param array|int|double|bool|string $body
	 */
	public function setJSON($body): void;
}