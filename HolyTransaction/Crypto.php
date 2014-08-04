<?php

namespace HolyTransaction;

/*
 * HolyTransaction API crypto library
 *
 * (C) 2013-2014 NoveltyLab
 * Licensed under MIT license
 */

class Crypto
{
    const HT_PUBLIC_KEY = '47ff22f86c3eb1eecfaa49c439c37f829c03b2b73d18d503202ded414de0db3b';


    public static function createKeys($username, $password, $service)
    {
        return self::deriveKeys($password, $username . $service);
    }


    public static function deriveKeys($password, $salt)
    {
        $seed = hex2bin(scrypt($password, $salt, 2048, 1, 1, 32));

        $keypair = crypto_sign_seed_keypair($seed);

        $keys = array(
            'public'    => bin2hex(crypto_sign_publickey($keypair)),
            'private'   => bin2hex(crypto_sign_secretkey($keypair)),
        );

        return $keys;
    }


    public static function signData($data)
    {
        $senderKeypair = crypto_box_keypair();
        $nonce = randombytes_buf(CRYPTO_BOX_NONCEBYTES);

        $senderSecretkey = crypto_box_secretkey($senderKeypair);
        $senderPublickey = crypto_box_publickey($senderKeypair);
        $reсipientPublickey = hex2bin(self::HT_PUBLIC_KEY);

        $cipher = crypto_box(hex2bin($data), $nonce, crypto_box_keypair_from_secretkey_and_publickey($senderSecretkey, $reсipientPublickey));

        $result = array(
            'data'  => bin2hex($cipher),
            'nonce' => bin2hex($nonce),
            'senderPublicKey' => bin2hex($senderPublickey),
        );

        return $result;
    }

}
