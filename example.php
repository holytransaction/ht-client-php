<?php

require 'HolyTransaction/HolyTransaction.php';
require 'HolyTransaction/HolyTransactionClient.php';

class Demo {

    private $apiUrl = "https://staging.holytransaction.com/api/v1/";
    private $apiId = "";
    private $apiKey = "";

    private $wallet;
    private $rawClient;


    public function run()
    {
        $this->wallet = new HolyTransaction($this->apiId, $this->apiKey, $this->apiUrl);
        $this->rawClient = $this->wallet->getClient();
        $this->rawClient->setDebug(true);

        try {
            $isUsed = $this->rawClient->query('accounts/is_email_used', HolyTransactionClient::HTC_REQUEST_POST, array('email' => 'test@example.com'))['used'];
            var_dump($isUsed);
        }
        catch (HolyTransactionAPIConnectionException $e) {
            echo "API connection error: " . $e->getMessage();
        }
        catch (HolyTransactionAPIException $e) {
            echo "API error: " . $e->getMessage();
        }

        var_dump($this->rawClient->getDebugLog());
    }

}


$HTDemo = new Demo();
$HTDemo->run();