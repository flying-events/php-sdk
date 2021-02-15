<?php

namespace FlyingEvents;

use Carbon\Carbon;
use FlyingEvents\Exceptions\UnauthorizedException;
use FlyingEvents\Http\Client as HttpClient;
use FlyingEvents\Event;
use FlyingEvents\Environment;
use FlyingEvents\Exceptions\EnvironmentNotFoundException;
use FlyingEvents\Exceptions\EventNotFoundException;
use FlyingEvents\Exceptions\IllegalArgumentException;

class FlyingEventsClient
{

    protected $applicationKey;
    protected $applicationSecret;
    protected $authToken;
    protected $reAuthTime;
    protected $authTimeoutSeconds;
    protected $client;
    protected $environment;

    public function __construct($params){

        $this->applicationKey = $params['applicationKey'];
        $this->applicationSecret = $params['applicationSecret'];
        $this->environment = $params['environment'];
        if($this->environment !== Environment::LIVE() && $this->environment !== Environment::TEST()){
            throw new EnvironmentNotFoundException("Error with environment - please use available environment types (LIVE, TEST)");
        }

        $this->authTimeoutSeconds = 12 * 60 * 60; // 12 hour default
        // set reauthorize time to force an authentication to take place
        $this->reAuthTime = Carbon::now('UTC')->subSeconds($this->authTimeoutSeconds);

        $this->client = new HttpClient();
        $this->authorizeAccount();
    }

    /**
     * Request token for subscriber.
     *
     * @param $params
     * @return string
     * @throws IllegalArgumentException
     */
    public function requestSubscriberToken($params){
        echo $params['subscriberId'];
        if(!is_string($params['subscriberId']) || !isset($params['subscriberId']) || $params['subscriberId'] == ''){
            throw new IllegalArgumentException("subscriberId must be a non- empty string");
        }
        try {
            $this->authorizeAccount();
        } catch (UnauthorizedException $e) {
            echo $e;
        }
        return $this->client->requestSubscriberToken($params, $this->environment,	$this->authToken);
    }

    /**
     * Publish new event.
     *
     * @param array $event
     *
     * @throws EventNotFoundException
     */
    public function sendEvent($event){
        $event['environment'] = $this->environment;
        $eventObj = new Event($event);

        try {
            $this->authorizeAccount();
        } catch (UnauthorizedException $e) {
            echo $e;
        }
        $this->client->sendEvent($eventObj, $this->authToken);
    }

    /**
     * Request new authorization token if existing token expired.
     *
     * @throws UnauthorizedException
     */
    protected function authorizeAccount(){
        if (Carbon::now('UTC')->timestamp < $this->reAuthTime->timestamp) {
            return;
        }

        $body = [
            'applicationKey' => $this->applicationKey,
            'applicationSecret' => $this->applicationSecret
        ];

        $this->authToken = $this->client->requestApplicationToke($body);

        $this->reAuthTime = Carbon::now('UTC');
        $this->reAuthTime->addSeconds($this->authTimeoutSeconds);
    }
}
