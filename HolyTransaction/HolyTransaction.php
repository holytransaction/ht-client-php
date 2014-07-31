<?php

namespace HolyTransaction;

require_once(dirname(__FILE__) . '/Crypto.php');
require_once(dirname(__FILE__) . '/Client.php');

/*
 * HolyTransaction API client library
 *
 * (C) 2013-2014 NoveltyLab
 * Licensed under MIT license
 */
class HolyTransaction
{

    private
        $client;


    public function __construct($apiUserId, $apiUserKey, $apiUrl=null)
    {
        $this->client = new Client($apiUserId, $apiUserKey, $apiUrl);
    }


    public function get($apiFunction, array $params = array())
    {
        return $this->client->query($apiFunction, 'GET', $params);
    }


    public function post($apiFunction, array $params = array())
    {
        return $this->client->query($apiFunction, 'POST', $params);
    }


    public function patch($apiFunction, array $params = array())
    {
        return $this->client->query($apiFunction, 'PATCH', $params);
    }


    public function delete($apiFunction, array $params = array())
    {
        return $this->client->query($apiFunction, 'DELETE', $params);
    }


    public static function getUserApiKey($username, $password)
    {
        $keys = Crypto::createKeys($username, $password, 'api');
        return $keys['private'];
    }


    public static function getUserKeys($username, $password, $keys = array('api', 'key'))
    {
        $result = array();

        foreach ($keys as $k) {
            $result[$k] = Crypto::createKeys($username, $password, $k);
        }

        return $result;
    }


    public function getPagination()
    {
        $headers = $this->client->getResponseHeaders();

        if (!$headers || !isset($headers['X-Pagination']))
            return false;

        return (array)json_decode($headers['X-Pagination']);
    }


    public function getClient()
    {
        return $this->client;
    }

}
