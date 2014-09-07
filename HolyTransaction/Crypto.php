<?php

namespace HolyTransaction;
use Sodium;

/*
 * HolyTransaction API crypto library
 *
 * (C) 2013-2014 NoveltyLab
 * Licensed under MIT license
 */

class Crypto
{
    const HT_PUBLIC_KEY = 'e1038b6410244c3fc199c53cb42e23531c8b0cc2e2e2a3ccdde0924a3c483f19';


    public static function createKeys($username, $password, $service)
    {
        return self::deriveKeys($password, $username . $service);
    }


    public static function deriveKeys($password, $salt)
    {
        $seed = hex2bin(scrypt($password, $salt, 2048, 1, 1, 32));

        $keypair = Sodium::crypto_sign_seed_keypair($seed);

        $keys = array(
            'public'    => bin2hex(Sodium::crypto_sign_publickey($keypair)),
            'private'   => bin2hex(Sodium::crypto_sign_secretkey($keypair)),
        );

        return $keys;
    }


    public static function signData($data)
    {
        $senderKeypair = Sodium::crypto_box_keypair();
        $nonce = randombytes_buf(CRYPTO_BOX_NONCEBYTES);

        $senderSecretkey = Sodium::crypto_box_secretkey($senderKeypair);
        $senderPublickey = Sodium::crypto_box_publickey($senderKeypair);
        $reсipientPublickey = hex2bin(self::HT_PUBLIC_KEY);

        $cipher = Sodium::crypto_box(hex2bin($data), $nonce, Sodium::crypto_box_keypair_from_secretkey_and_publickey($senderSecretkey, $reсipientPublickey));

        $result = array(
            'data'  => bin2hex($cipher),
            'nonce' => bin2hex($nonce),
            'senderPublicKey' => bin2hex($senderPublickey),
        );

        return $result;
    }

}
