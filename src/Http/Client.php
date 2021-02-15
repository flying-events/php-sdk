<?php
namespace FlyingEvents\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use FlyingEvents\Exceptions\UnauthorizedException;
use FlyingEvents\Exceptions\EventNotFoundException;
use FlyingEvents\Event;


class Client extends GuzzleClient
{
    private const API_BASE_URL = "https://app.flying.events/api/";
    private const SUBSCRIBER_REQUEST_TOKEN = "subscriber/%s/request-token";
    private const APPLICATION_REQUEST_TOKEN = "application/request-token";
    private const PUBLISH_EVENT = "worker/publish-event";


  public function requestApplicationToke($body){
        $url = self::API_BASE_URL . self::APPLICATION_REQUEST_TOKEN;
        $headers = ['Content-Type' => 'application/json'];
        try{
            
            $response = parent::request('POST', $url , [
                'body' => json_encode($body),
                'headers' => $headers
            ]);
            return $response->getHeader('Authorization')[0];

        }catch(ClientException $e){
            throw new UnauthorizedException("Error authorizing account response=". $e->getResponse()->getBody());
        }
    }


    public function requestSubscriberToken($params, $environment, $authToken){
        $url = self::API_BASE_URL . sprintf(self::SUBSCRIBER_REQUEST_TOKEN,$params['subscriberId']);
        $headers = [
            'Authorization' => 'Bearer ' . $authToken,
            'Content-Type' => 'application/json'
        ];
        $body = ['environment' => $environment];

        $response = parent::request('POST', $url , [
            'headers' => $headers,
            'body' => json_encode($body)
        ]);

        return $response->getHeader('Authorization')[0];

    }

    public function sendEvent($event, $authToken){
        
        $url = self::API_BASE_URL . self::PUBLISH_EVENT;
        $headers = [
            'Authorization' => 'Bearer ' . $authToken,
            'Content-Type' => 'application/json',
        ];

        try{
            $response = parent::request('POST', $url , [
              'headers' => $headers,
              'body' => json_encode($event)
             ]);

        }catch(ClientException $e){
            throw new EventNotFoundException($e->getResponse()->getBody());
        }
    }


}
