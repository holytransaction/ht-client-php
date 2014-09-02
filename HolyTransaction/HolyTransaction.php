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
        $client,
        $htApiUrl = 'https://api.holytransaction.com/api/v1/',
        $walletApiUrl = 'https://holytransaction.com/api/';


    public function __construct($apiUserId = null, $apiUserKey = null, $apiUrl=null)
    {
        $this->client = new Client($apiUserId, $apiUserKey, $apiUrl ? $apiUrl : $this->htApiUrl);
    }


    public function get($apiFunction, array $params = array(), $auth = true)
    {
        return $this->client->query($apiFunction, 'GET', $params, $auth);
    }


    public function post($apiFunction, array $params = array(), $auth = true)
    {
        return $this->client->query($apiFunction, 'POST', $params, $auth);
    }


    public function patch($apiFunction, array $params = array(), $auth = true)
    {
        return $this->client->query($apiFunction, 'PATCH', $params, $auth);
    }


    public function delete($apiFunction, array $params = array(), $auth = true)
    {
        return $this->client->query($apiFunction, 'DELETE', $params, $auth);
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

        return json_decode($headers['X-Pagination'], true);
    }


    public function getClient()
    {
        return $this->client;
    }


    public function createUser($username, $password, $email)
    {
        $keys = $this->getUserKeys($username, $password);

        $encryptedKeys = array(
            'api'   => Crypto::signData($keys['api']['private']),
            'key'   => Crypto::signData($keys['key']['private']),
        );

        $account = array(
            'account' => array(
                'email'                 => $email,
                'encrypted_hmac_key'    => $encryptedKeys['api']['data'],
                'hmac_box_public_key'   => $encryptedKeys['api']['senderPublicKey'],
                'hmac_box_nonce'        => $encryptedKeys['api']['nonce'],
                'encrypted_wallet_key'  => $encryptedKeys['key']['data'],
                'wallet_box_public_key' => $encryptedKeys['key']['senderPublicKey'],
                'wallet_box_nonce'      => $encryptedKeys['key']['nonce'],
            )
        );

        $result = $this->post('accounts', $account);

        return $result;
    }


    public function createWalletUser($username, $password, $email, $token)
    {
        $walletClient = new Client(null, null, $this->walletApiUrl);

        $keys = $this->getUserKeys($username, $password, array('wallet', 'api', 'key'));

        $account = array(
            'token'     => $token,
            'username'  => $username,
            'email'     => $email,
            'country'   => '',
            'timezone'  => '',
            'keys'      => array(
                'wallet'    => $keys['wallet']['private'],
                'api'       => Crypto::signData($keys['api']['private']),
                'key'       => Crypto::signData($keys['key']['private']),
            ),
        );

        $result = $walletClient->query('user/create', 'POST', $account, false);

        return $result;
    }

}
