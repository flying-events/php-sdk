<?php

namespace FlyingEvents\Http;

class CurlClient
{

    private const API_BASE_URL = "https://app.flying.events/api/";

    public function postRequest($relativeUrl, $body, $authorizationToken)
    {
        $curl = curl_init();
        $headers = ['Content-Type: application/json'];
        if ($authorizationToken != null) {
            array_push($headers, 'Authorization: Bearer ' . $authorizationToken);
        }
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::API_BASE_URL . $relativeUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_HEADER => 1,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $headers,
        ));
        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $httpStatusCode = curl_getinfo($curl)['http_code'];
        if (curl_error($curl)) {
            return null;
        }
        curl_close($curl);
        return ['statusCode' => $httpStatusCode, 'header' => $header, 'body' => $body];
    }

    public function extractHeader($headers, $headerName)
    {
        $headers = explode("\n", $headers);
        foreach ($headers as $header) {
            if($header && strpos($header,  ':') > -1){
                list($key, $value) = explode(':', $header, 2);
                if(strcasecmp(trim($key), $headerName) == 0){
                    return trim($value);
                }
            }
        }
        return null;
    }

}
