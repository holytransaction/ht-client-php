<?php

/*
 * HolyTransaction API library
 *
 * (C) 2013-2014 NoveltyLab
 * Licensed under MIT license
 */
class HolyTransaction
{
    public $apiUrl = 'http://staging.holytransaction.com/api/v1/';

    /**
     * @var HolyTransactionClient
     */
    protected $client;


    public function __construct($apiId, $apiKey, $apiUrl = null)
    {
        if ($apiUrl === null)
            $apiUrl = $this->apiUrl;

        $this->client = new HolyTransactionClient($apiId, $apiKey, $apiUrl);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getPagination()
    {
        $headers = $this->client->getResponseHeaders();

        if (!$headers || !isset($headers['X-Pagination']))
            return false;

        return json_decode($headers['X-Pagination']);
    }

}
