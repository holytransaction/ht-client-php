<?php

require_once 'HolyTransaction/HolyTransaction.php';

$apiUserId = 0;
$apiUserKey = '';

//$username = '';
//$password = '';
//$apiUserKey = \HolyTransaction\HolyTransaction::getUserApiKey($username, $password);

$ht = new \HolyTransaction\HolyTransaction($apiUserId, $apiUserKey);


/**
 * Exchange rates
 */
echo '<h1>Exchange rates</h1>';
$exchangeRates = $ht->get('data/exchange_rates', array('show_fiat' => 1), false);
var_dump($exchangeRates);


/**
 * Balances
 */
//echo '<h1>Balances</h1>';
//$balances = $ht->get('balances');
//var_dump($balances);


/**
 * Exchange orders with pagination
 */
//echo '<h1>Exchange orders</h1>';
//$orders = $ht->get('exchange_orders', array(
//    'per_page'  => 3,
//    'page'      => 2,
//));
//var_dump($orders);
//
//echo '<h3>Pagination</h3>';
//var_dump($ht->getPagination());


/**
 * Check email
 */
//echo '<h1>Check email</h1>';
//$email_used = $ht->post('accounts/is_email_used', array('email' => 'example@mail.com'));
//var_dump($email_used);


/**
 * Create HolyTransaction user
 */
/*
echo '<h1>Registration</h1>';

// Turn debugging on
$ht->getClient()->setDebug();

$username   = '-----USERNAME-----';
$password   = '-----PASSWORD-----';
$email      = 'example@email.com';

try {
    $result = $ht->createUser($username, $password, $email);
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


/**
 * Create Wallet (and HolyTransaction) User
 */
//$username   = '-----USERNAME-----';
//$password   = '-----PASSWORD-----';
//$email      = '------E-MAIL------';
//$wallet_token = '-----WALLET_TOKEN-----';
//
//try{
//    $result = $ht->createWalletUser($username, $password, $email, $wallet_token);
//    var_dump($result);
//}
//catch (\HolyTransaction\APIException $e) {
//    echo '<h3>Error</h3>';
//    var_dump($e);
//}
