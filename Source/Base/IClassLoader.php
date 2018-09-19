<?php
namespace WebServer\Base;


interface IClassLoader
{
	/**
	 * @param string $action
	 * @return callable|null
	 */
	public function getAction(string $action);
	
	/**
	 * @param string $name
	 * @return object|null
	 */
	public function getDecorator(string $name);
	
	/**
	 * @param string $name
	 * @return object|null
	 */
	public function getResponseParser(string $name);
	
	/**
	 * @param string $controller
	 * @param string $action
	 * @return object|null
	 */
	public function getController(string $controller, string $action);
}