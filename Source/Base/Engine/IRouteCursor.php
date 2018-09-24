<?php
namespace WebServer\Base\Engine;


interface IRouteCursor extends ITargetCursor
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
	
	public function push(): void;
	public function pop(bool $cleanChildren = false);
	public function removeChildren(): void;
}