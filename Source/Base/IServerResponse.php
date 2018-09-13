<?php
namespace WebServer\Base;


interface IServerResponse
{
	/**
	 * @return mixed
	 */
	public function get();
	
	public function isSet(): bool;
	public function isScalar(): bool;
	public function isArray(): bool;
	public function isStdClass(): bool;
}