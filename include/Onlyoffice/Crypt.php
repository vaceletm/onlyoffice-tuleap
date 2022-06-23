<?php

namespace Tuleap\Onlyoffice;

use Tuleap\Cryptography\KeyFactory;

class Crypt
{
    public static function GetHash($object): string
    {
        return \Firebase\JWT\JWT::encode($object, self::GetCryptKey(), "HS256");
    }

    public static function ReadHash($token): array
    {
        $key = new \Firebase\JWT\Key(self::GetCryptKey(), "HS256");

        $result = null;
        $error = null;
        if ($token === null) {
            return [$result, "token is empty"];
        }
        try {
            $result = \Firebase\JWT\JWT::decode($token, $key);
        } catch (\UnexpectedValueException $e) {
            $error = $e->getMessage();
        }

        return [$result, $error];
    }

    private static function GetCryptKey(): string
    {
        $keyFactory = new KeyFactory();
        $key = $keyFactory->getEncryptionKey();

        return $key->getRawKeyMaterial();
    }
}