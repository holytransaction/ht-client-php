<?php

require_once 'HolyTransaction/HolyTransaction.php';

$apiUserId = 0;
$apiUserKey = '-----API_KEY-----';

//$apiUserId = 0;
//$username = '-----USERNAME-----';
//$password = '-----PASSWORD-----';
//$apiUserKey = \HolyTransaction\HolyTransaction::getUserApiKey($username, $password);


$ht = new \HolyTransaction\HolyTransaction($apiUserId, $apiUserKey, $sandboxApiUrl);


/**
 * Exchange rates
 */
echo '<h1>Exchange rates</h1>';
$exchangeRates = $ht->get('data/exchange_rates', array('show_fiat' => 1));
var_dump($exchangeRates);


/**
 * Balances
 */
echo '<h1>Balances</h1>';
$balances = $ht->get('balances');
var_dump($balances);


/**
 * Exchange orders with pagination
 */
echo '<h1>Exchange orders</h1>';
$orders = $ht->get('exchange_orders', array(
    'per_page'  => 3,
    'page'      => 2,
));
var_dump($orders);

echo '<h3>Pagination</h3>';
var_dump($ht->getPagination());


/**
 * Check email
 */
echo '<h1>Check email</h1>';
$email_used = $ht->post('accounts/is_email_used', array('email' => 'example@mail.com'));
var_dump($email_used);


/**
 * Create user
 */
/*
echo '<h1>Registration</h1>';

$username   = '-----USERNAME-----';
$password   = '-----PASSWORD-----';
$email      = 'example@email.com';

$keys = $ht->getUserKeys($username, $password);

echo '<h3>UserKeys from username and password</h3>';
var_dump($keys);

$encryptedKeys = array(
    'api'   => \HolyTransaction\Crypto::signData($keys['api']['private']),
    'key'   => \HolyTransaction\Crypto::signData($keys['key']['private']),
);

echo '<h3>Encrypted Keys</h3>';
var_dump($encryptedKeys);

$account = array(
    'account' => array(
        'email'                 => $email,
        'encrypted_hmac_key'    => $encryptedKeys['api']['data'],
        'hmac_box_public_key'   => $encryptedKeys['api']['publicKey'],
        'hmac_box_nonce'        => $encryptedKeys['api']['nonce'],
        'encrypted_wallet_key'  => $encryptedKeys['key']['data'],
        'wallet_box_public_key' => $encryptedKeys['key']['publicKey'],
        'wallet_box_nonce'      => $encryptedKeys['key']['nonce'],
    )
);

echo '<h3>Account</h3>';
var_dump($account);

// Turn debugging on
$ht->getClient()->setDebug(true);

try {
    $result = $ht->post('accounts', $account);
}
catch (\HolyTransaction\APIException $e) {
    echo '<h3>Exception</h3>';
    var_dump($e);
}

echo '<h3>Result</h3>';
var_dump($result);

echo '<h3>Debug</h3>';
var_dump($ht->getClient()->getDebugLog());
*/
