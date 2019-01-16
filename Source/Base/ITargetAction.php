<?php
namespace WebServer\Base;


interface ITargetAction
{
	public function hasController(): bool;
	public function isMethod(): bool;
	public function isCallback(): bool;
	
	/**
	 * @return object|null
	 */
	public function getController();
	
	public function getActionName(): ?string;
	
	public function getAction(): callable;
	
	/**
	 * @return callable[]
	 */
	public function getCallbackDecorators(): array;
	
	/**
	 * @return object[]
	 */
	public function getDecorators(): array;
	
	/**
	 * @return IResponseParser[]
	 */
	public function getResponseParsers(): array;
}