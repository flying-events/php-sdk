<?php

namespace FlyingEvents;

use FlyingEvents\Exceptions\FlyingEventsException;
use FlyingEvents\Http\CurlClient;

class FlyingEventsClient
{

    protected $applicationKey;
    protected $applicationSecret;
    protected $authToken;
    protected $client;
    protected $environment;

    public function __construct($params)
    {
        $this->applicationKey = $params['applicationKey'];
        $this->applicationSecret = $params['applicationSecret'];
        $this->environment = $params['environment'];
        if ($this->environment !== Environment::LIVE() && $this->environment !== Environment::TEST()) {
            throw new FlyingEventsException("Error with environment - please use available environment types (LIVE, TEST)");
        }

        $this->client = new CurlClient();
        $this->authorizeAccount();
    }

    /**
     * Request new authorization token if existing token expired.
     *
     * @throws FlyingEventsException
     */
    protected function authorizeAccount()
    {
        if ($this->isJwtExpired()) {
            $body = [
                'applicationKey' => $this->applicationKey,
                'applicationSecret' => $this->applicationSecret
            ];
            $response = $this->client->postRequest('application/request-token', $body, null);
            if ($response['statusCode'] == 401) {
                throw new FlyingEventsException("Invalid application credentials");
            }
            $this->authToken = $this->client->extractHeader($response['header'], 'Authorization');
        }
    }

    /**
     * Request token for subscriber.
     *
     * @param $params
     * @return string
     * @throws FlyingEventsException
     */
    public function requestSubscriberToken($params)
    {
        if (!is_string($params['subscriberId']) || !isset($params['subscriberId']) || $params['subscriberId'] == '') {
            throw new FlyingEventsException("subscriberId must be a non- empty string");
        }
        $this->authorizeAccount();
        $response = $this->client->postRequest('subscriber/' . $params['subscriberId']
            . '/request-token', ['environment' => $this->environment], $this->authToken);

        return $this->client->extractHeader($response['header'], 'Authorization');
    }


    /**
     * Send new event.
     *
     * @param array $event
     *
     * @return bool
     * @throws FlyingEventsException
     */
    public function sendEvent(array $event)
    {
        $event['environment'] = $this->environment;
        $eventObj = new Event($event);

        $this->authorizeAccount();
        $response = $this->client->postRequest('worker/send-event', $eventObj->arraySerialize(), $this->authToken);
        if ($response == null) {
            $failSafeResponse = $this->client->postRequest('failsafe/send-event', $eventObj->arraySerialize(), $this->authToken);
            if ($failSafeResponse == null || floor($failSafeResponse['statusCode'] / 100) != 2) {
                return false;
            }
        } else if (floor($response['statusCode'] / 100) != 2) {
            throw new FlyingEventsException($response['body']);
        }
        return true;
    }

    private function isJwtExpired()
    {
        if ($this->authToken == null) {
            return true;
        }
        $requestBufferInSeconds = 30;
        list($header, $payload, $signature) = explode(".", $this->authToken);
        $payload = json_decode(base64_decode($payload), true);
        return time() > $payload['exp'] + $requestBufferInSeconds;
    }
}
