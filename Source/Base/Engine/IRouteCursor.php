<?php
namespace WebServer\Base\Engine;


interface IRouteCursor
{
	public function getController(): ?string;
	
	/**
	 * @return string|callable
	 */
	public function getAction();
	
	/**
	 * @return string[]
	 */
	public function getParsers(): array;
	
	/**
	 * @return string[]|callable[]
	 */
	public function getDecorators(): array;
}