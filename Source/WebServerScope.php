<?php
namespace WebServer;


use Narrator\Narrator;
use Skeleton\Skeleton;

use WebCore\IWebRequest;
use WebCore\IWebResponse;
use WebCore\HTTP\Requests\StandardWebRequest;
use WebCore\HTTP\Responses\StandardWebResponse;


class WebServerScope
{
	/** @var IWebRequest */
	private $webRequest = null;
	
	/** @var IWebResponse */
	private $webResponse = null;
	
	/** @var ActionResponse */
	private $actionResponse;
	
	/** @var Narrator */
	private $narrator;
	
	/** @var Skeleton */
	private $skeleton;
	
	
	private function createNarrator()
	{
		$narrator	= new Narrator();
		$skeleton	= $this->skeleton();
		$params		= $narrator->params();
		
		$params->byType(Skeleton::class, $skeleton);
		$params->byType(Narrator::class, $narrator);
		$params->byType(WebServerScope::class, $this);
		
		$params->byType(IWebRequest::class, [$this, 'webRequest']);
		$params->byType(IWebResponse::class, [$this, 'webResponse']);
		$params->byType(ActionResponse::class, [$this, 'actionResponse']);
		
		$params->fromSkeleton($skeleton);
		
		$this->narrator = $narrator;
	}
	
	
	public function __construct()
	{
		$this->skeleton = new Skeleton();
		$this->skeleton->useGlobal();
		
		$this->createNarrator();
	}
	
	
	public function narrator(): Narrator
	{
		return $this->narrator;
	}
	
	public function skeleton(): Skeleton
	{
		return $this->skeleton;
	}
	
	public function actionResponse(): ActionResponse
	{
		return $this->actionResponse;
	}
	
	public function webRequest(): IWebRequest
	{
		if (!$this->webRequest)
			$this->webRequest = StandardWebRequest::current();
		
		return $this->webRequest;
	}
	
	public function webResponse(): IWebResponse
	{
		if (!$this->webResponse)
			$this->webResponse = new StandardWebResponse();
		
		return $this->webResponse;
	}
}