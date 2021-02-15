<?php
namespace FlyingEvents;


use JsonSerializable;
use FlyingEvents\Exceptions\IllegalArgumentException;

class Event implements JsonSerializable
{
	
	protected $eventName;
	protected $payload;
	protected $environment;
	protected $subscribersIds;
	
	function __construct($event){
		$this->setEventName($event['eventName']);
		$this->setPayload($event['payload']);
		$this->setSubscribersIds($event['subscribersIds']);
		$this->setEnvironment($event['environment']);
	}

	public function setEventName($eventName){
		if($this->isValidString($eventName)){
			$this->eventName = $eventName;
		}else{
			throw new IllegalArgumentException("eventName must be a non empty string"); 
		}
	}

	public function setPayload($payload){
		if($this->isValidString($payload)){
			$this->payload = $payload;
		}else{
			throw new IllegalArgumentException("payload must be a non empty string"); 
		}
	}

	public function setSubscribersIds($subscribersIds){
		if($this->isValidArray($subscribersIds)){
			$this->subscribersIds = $subscribersIds;
		}else{
			throw new IllegalArgumentException("subscribersIds must be a non empty array of string IDs"); 
		}	
	}

	protected function isValidString($stringVar){
		return (is_string($stringVar) && isset($stringVar) && $stringVar !== '');
	}

	protected function isValidArray($arrayVar){
		if($arrayVar && is_array($arrayVar)){
			foreach ($arrayVar as $value){ 
  				if(!$this->isValidString($value))
  					return false;
  			}
  			return true;
		}
		return false;
	}

	

	public function setEnvironment($environment){
		$this->environment = $environment;
	}

	public function getEventName(){
		return $this->eventName;
	}


	public function getPayload(){
		return $this->payload;
	}


	public function getSubscribersIds(){
		return $this->subscribersIds;
	}


	public function getEnvironment(){
		return $this->environment;
	}

	public function jsonSerialize()
    {
        return 
        [
            'eventName'   => $this->getEventName(),
            'environment' => $this->getEnvironment(),
            'subscribersIds' => $this->getSubscribersIds(),
            'payload' => $this->getPayload()
        ];
    }

}

