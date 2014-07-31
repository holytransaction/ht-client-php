<?php

namespace HolyTransaction;

/*
 * HolyTransaction API client library
 *
 * (C) 2013-2014 NoveltyLab
 * Licensed under MIT license
 */
class Client
{
    private
        $apiUrl = 'https://api.holytransaction.com/api/v1/',
        $apiId,
        $apiKey,
        $curl,
        $responseHeaders,
        $debug = false,
        $debugLog = array();


    public function __construct($apiUserId, $apiUserKey, $apiUrl=null)
    {
        $this->apiId    = $apiUserId;
        $this->apiKey   = $apiUserKey;

        if ($apiUrl) {
            $this->apiUrl = $apiUrl;
        }
    }


    public function query($apiFunction, $requestMethod, array $params = array())
    {
        $this->responseHeaders = '';

        if ($this->debug) {
            $log_data = array(
                'apiFunction'   => $apiFunction,
                'requestMethod' => $requestMethod,
                'params'        => $params,
            );

            $this->addToLog('query', $log_data);
        }

        $requestMethod = strtoupper($requestMethod);

        if (!in_array($requestMethod, array('GET', 'POST', 'PATCH', 'DELETE'))) {
            throw new APIConnectionException('Wrong requested method: ' . $requestMethod);
        }

        $requestParams = '';

        if (in_array($requestMethod, array('GET', 'DELETE'))) {
            if (!empty($params)) {
                $apiFunction .= '?' . http_build_query($params);
            }
        }
        else {
            $requestParams = json_encode($params);
        }

        return $this->sendRequest($apiFunction, $requestMethod, $requestParams);
    }


    private function headersToArray($headers_text)
    {
        $headers = array();

        foreach (explode("\r\n", $headers_text) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            }
            elseif($line) {
                list ($key, $value) = explode(': ', $line);
                $headers[$key] = $value;
            }
        }

        return $headers;
    }


    private function initCurl()
    {
        if ($this->curl)
            return;

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_USERAGENT, 'PHP client; ' . php_uname('s') . '; PHP/' . phpversion());
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 1); // man-in-the-middle defense by verifying ssl cert.
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 2); // man-in-the-middle defense by verifying ssl cert.
    }


    private function prepareRequestHeaders($apiFunction, $requestMethod, $content)
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'X-Hmac-Id'     => $this->apiId,
            'X-Hmac-Nonce'  => round(microtime(true), 3) * 1000, // 1399954063392
            'X-Hmac-Signature' => ''
        ];

        $canonicalString = implode(',', array($requestMethod, '/api/v1/' . $apiFunction, base64_encode(md5($content, true)), $headers['X-Hmac-Nonce']));

        $headers['X-Hmac-Signature'] = trim(base64_encode(hash_hmac("sha1", $canonicalString, $this->apiKey, true)));

        $headers['X-Debug-Canonical'] = $canonicalString;
        $headers['X-Debug-Content'] = $content;


        $curlHeaders = array();
        foreach ($headers as $key => $val) {
            $curlHeaders[] = $key . ': ' . $val;
        }

        return $curlHeaders;
    }


    protected function sendRequest($apiFunction, $requestMethod = 'GET', $postContent = null)
    {
        $this->initCurl();

        $headers = $this->prepareRequestHeaders($apiFunction, $requestMethod, $postContent);

        if ($this->debug) {
            curl_setopt($this->curl, CURLOPT_VERBOSE, 1);

            $log_data = array(
                'apiUrl' => $this->apiUrl,
                'apiFunction' => $apiFunction,
                'requestMethod' => $requestMethod,
                'postContent' => $postContent,
                'headers' => $headers,
            );

            $this->addToLog('request.params', $log_data);
        }

        curl_setopt($this->curl, CURLOPT_URL, $this->apiUrl . $apiFunction);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

        if ($requestMethod == 'POST') {
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($this->curl, CURLOPT_POST, TRUE);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postContent);
        }
        else if ($requestMethod == 'PATCH') {
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($this->curl, CURLOPT_POST, TRUE);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postContent);
        }
        else if ($requestMethod == 'DELETE') {
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($this->curl, CURLOPT_POST, FALSE);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, null);
        }
        else {
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($this->curl, CURLOPT_POST, FALSE);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, null);
        }

        curl_setopt($this->curl, CURLOPT_HEADER, 1);
        curl_setopt($this->curl, CURLINFO_HEADER_OUT, 1);

        $response = curl_exec($this->curl);

        $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $this->responseHeaders = $this->headersToArray(substr($response, 0, $headerSize));
        $response = substr($response, $headerSize);

        if ($this->debug) {
            $this->addToLog('response.headers', $this->responseHeaders);
            $this->addToLog('response.content', $response);
        }

        if ($response === false) {
            $error = curl_error($this->curl);

            if ($this->debug) {
                $this->addToLog('response.error', $error);
            }

            curl_close($this->curl);
            throw new APIConnectionException('Cannot connect to the API: ' . $error);
        }

        if ($this->debug) {
            $this->addToLog('response.details', curl_getinfo($this->curl));
        }

        $decodedResponse = json_decode($response, true);
        if ($decodedResponse === null) {
            throw new APIException('Invalid data received, JSON was expected: ' . $response);
        }

        if (!empty($decodedResponse['error'])) {
            $status = isset($decodedResponse['status']) ? $decodedResponse['status'] : 500;
            throw new APIException('Error ' . $status . ': ' . $decodedResponse['error'], $status);
        }

        return $decodedResponse;
    }


    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }


    private function addToLog($function, $data)
    {
        $this->debugLog[] = array($function => $data);
    }


    public function setDebug($enabled = false)
    {
        $this->debug = (bool)$enabled;
    }


    public function getDebugLog($clear=true)
    {
        $result = $this->debugLog;

        if ($clear)
            $this->debugLog = array();

        return $result;
    }


}



class APIException extends \Exception
{
}

class APIAuthorizationException extends APIException
{
}

class APIConnectionException extends APIException
{
}