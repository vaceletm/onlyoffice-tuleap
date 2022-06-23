<?php

namespace Tuleap\Onlyoffice;

class FileUtility
{
    public static function GetKey ($file)
    {
        $id = $file->getId();
        $lastUpdate = $file->getUpdateDate();

        $expectedKey = $id . $lastUpdate;

        return self::GenerateRevisionId($expectedKey);
    }

    private static function GenerateRevisionId(string $expectedKey): string
    {
        if (strlen($expectedKey) > 20) $expectedKey = crc32( $expectedKey);
        $key = preg_replace("[^0-9-.a-zA-Z_=]", "_", $expectedKey);
        $key = substr($key, 0, min(array(strlen($key), 20)));
        return $key;
    }
}