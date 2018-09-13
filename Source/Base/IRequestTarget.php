<?php
namespace WebServer\Base;


interface IRequestTarget
{
	public function hasController(): bool;
	
	/**
	 * @return object|null
	 */
	public function getController();
	
	public function getAction(): callable;
	
	/**
	 * @return callable[]
	 */
	public function getCallbackDecorators(): array;
	
	/**
	 * @return object[]
	 */
	public function getDecorators(): array;
}