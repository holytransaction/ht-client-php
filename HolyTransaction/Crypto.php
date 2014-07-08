<?php

/*
 * HolyTransaction API library
 *
 * (C) 2013-2014 NoveltyLab
 * Licensed under MIT license
 */

class Crypto
{
    private $cryptoKeys;


    public function __construct($username, $password) {
        $this->cryptoKeys = [
            'wallet' => $this->createKeys($password, $username, 'wallet'),
            'api' => $this->createKeys($password, $username, 'api')
        ];
    }


    public function getKeys() {
        return $this->cryptoKeys;
    }


    public function createKeys($password, $username, $service) {
        return $this->deriveKeys($password, $username . $service);
    }


    private function deriveKeys($password, $salt) {
        $seed = scrypt($password, $salt, 2048, 1, 1, 32);
        $private = $public = '';

        $keyPair = nacl_crypto_sign_keypair($private, $public);
        return [$keyPair, $private, $public];

//      TODO: init nacl_crypto_sign_keypair by seed.
//
//        return array(
//            'public' => $this->formatKey($keyPair->signPk),
//            'private' => $this->formatKey($keyPair->signSk)
//        );
    }


    private function formatKey($key) {
        return array(
            'origin' => $key,
            'hex' => null,
            'utf8' => null,
            'latin1' => null
        );
    }

}
