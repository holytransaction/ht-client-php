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

    public function __construct($password, $username) {
        $this->cryptoKeys = [
            'wallet' => $this->createKeys($password, $username, 'wallet'),
            'api' => $this->createKeys($password, $username, 'api')
        ];
    }

    public function createKeys($password, $username, $service) {
        return $this->deriveKeys($password, $username . $service);
    }

    // TODO: use https://github.com/Gasol/pecl-nacl and https://github.com/DomBlack/php-scrypt


}
