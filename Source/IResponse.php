<?php
namespace WebServer;


interface IResponse
{
	public function execute(): void;
}