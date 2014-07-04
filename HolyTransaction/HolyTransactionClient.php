<?php

/*
 * HolyTransaction client library
 *
 * (C) 2013-2014 NoveltyLab
 * Licensed under MIT license
 */

class HolyTransactionClient
{
    const HTC_REQUEST_GET    = 'GET';
    const HTC_REQUEST_POST   = 'POST';
    const HTC_REQUEST_DELETE = 'GET';
    const HTC_REQUEST_PATCH  = 'PATCH';

    private $apiId, $apiKey, $apiUrl;
    private $curl, $responseHeaders = '';
    private $debug = false, $debugLog = array();

    public function __construct($apiId, $apiKey, $apiUrl)
    {
        $this->apiId = $apiId;
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
    }

    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    public function setDebug($enabled = false) {
        $this->debug = ($enabled == true);
    }

    public function getDebugLog() {
        return $this->debugLog;
    }

    public function addToLog($function, $data) {
        $this->debugLog[] = [$function, $data];
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

        switch ($requestMethod) {
            case self::HTC_REQUEST_GET:
                return $this->sendGetRequest($apiFunction);
            case self::HTC_REQUEST_POST:
                return $this->sendPostRequest($apiFunction, $params);
            case self::HTC_REQUEST_PATCH:
                return $this->sendPatchRequest($apiFunction, $params);
            case self::HTC_REQUEST_DELETE:
                return $this->sendDeleteRequest($apiFunction);
            default:
                throw new \HolyTransactionAPIConnectionException('Wrong requested method: ' . $requestMethod);
        }
    }


    protected function sendGetRequest($apiFunction)
    {
        return $this->sendRequest($apiFunction, 'GET');
    }


    protected function sendPostRequest($apiFunction, array $params)
    {
        $postContent = json_encode($params);

        return $this->sendRequest($apiFunction, 'POST', $postContent);
    }


    protected function sendPatchRequest($apiFunction, array $params)
    {
        $postContent = json_encode($params);

        return $this->sendRequest($apiFunction, 'PATCH', $postContent);
    }


    protected function sendDeleteRequest($apiFunction)
    {
        return $this->sendRequest($apiFunction, 'DELETE');
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

        curl_setopt($this->curl, CURLOPT_URL, $this->apiUrl . $apiFunction);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

        if ($this->debug) {
            curl_setopt($this->curl, CURLOPT_VERBOSE, 1);

            $log_data = array(
                'apiUrl' => $this->apiUrl,
                'apiFunction' => $apiFunction,
                'requestMethod' => $requestMethod,
                'postContent' => $postContent,
                'headers' => $headers,
            );

            $this->addToLog('sendRequest', $log_data);
        }

        if ($requestMethod == 'POST') {
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($this->curl, CURLOPT_POST, TRUE);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postContent);
        }
        else if ($requestMethod == 'DELETE') {
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($this->curl, CURLOPT_POST, FALSE);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, null);
        }
        else if ($requestMethod == 'PATCH') {
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($this->curl, CURLOPT_POST, TRUE);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postContent);
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
            $this->addToLog('sendRequest.response.headers', $this->responseHeaders);
            $this->addToLog('sendRequest.response.content', $response);
        }

        if ($response === false) {
            $error = curl_error($this->curl);

            if ($this->debug) {
                $this->addToLog('sendRequest.response.error', $error);
            }

            curl_close($this->curl);
            throw new \HolyTransactionAPIConnectionException('Cannot connect to the API: ' . $error);
        }

        if ($this->debug) {
            $this->addToLog('sendRequest.response.details', curl_getinfo($this->curl));
        }

        $decodedResponse = json_decode($response, true);
        if ($decodedResponse === null) {
            throw new \HolyTransactionAPIException('Invalid data received, JSON was expected: ' . $response);
        }

        if (!empty($decodedResponse['error'])) {
            $status = isset($decodedResponse['status']) ? $decodedResponse['status'] : 500;
            throw new \HolyTransactionAPIException('Error ' . $status . ': ' . $decodedResponse['error'], $status);
        }

        return $decodedResponse;
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

}

class HolyTransactionAPIException extends Exception
{
}

class HolyTransactionAPIAuthorizationException extends HolyTransactionAPIException
{
}

class HolyTransactionAPIConnectionException extends HolyTransactionAPIException
{
}