<?php

require 'HolyTransaction/HolyTransaction.php';
require 'HolyTransaction/HolyTransactionClient.php';
require 'HolyTransaction/Crypto.php';

class Demo {

    private $apiUrl = 'https://staging.holytransaction.com/api/v1/';
    private $apiId = '=================== YOUR API ID =======================';
    private $apiKey = '=================== YOUR API KEY =======================';
    private $debug = false;

    private $wallet;
    private $rawClient;

    public function run()
    {
        $this->wallet = new HolyTransaction($this->apiId, $this->apiKey, $this->apiUrl);
        $this->rawClient = $this->wallet->getClient();

        if ($this->debug)
            $this->rawClient->setDebug(true);

        echo "<pre>";

        try {
            var_dump($this->rawClient->query('data/exchange_rates', HolyTransactionClient::HTC_REQUEST_GET));
            var_dump($this->rawClient->query('accounts/' . $this->apiId, HolyTransactionClient::HTC_REQUEST_GET));

            // $isUsed = $this->rawClient->query('accounts/is_email_used', HolyTransactionClient::HTC_REQUEST_POST, array('email' => 'test@example.com'));
            // var_dump($isUsed);
        }
        catch (HolyTransactionAPIConnectionException $e) {
            echo "API connection error: " . $e->getMessage();
        }
        catch (HolyTransactionAPIException $e) {
            echo "API error: " . $e->getMessage();
        }

        if ($this->debug)
            var_dump($this->rawClient->getDebugLog());

        echo "</pre>";
    }


    public function generateKeys() {
        // alpha
        $crypto = new Crypto('HT username', 'HT password');
        var_dump($crypto->getKeys());
    }

}


$HTDemo = new Demo();
$HTDemo->run();