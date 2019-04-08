<?php

namespace Encoder;

/**
 * Class Encoder
 * @package Encoder
 */
class Encoder {

    const ENC_ASCII = 'ASCII';
    const ENC_UTF_8 = 'UTF-8';

    const ENC_ISO_8859_1  = 'ISO-8859-1';
    const ENC_ISO_8859_15 = 'ISO-8859-15';
    const ENC_ISO_8859_5  = 'ISO-8859-5';

    const ENC_WINDOWS_1251 = 'Windows-1251';
    const ENC_WINDOWS_1252 = 'Windows-1252';
    const ENC_KOI8_R       = 'KOI8-R';
    const ENC_KOI8_U       = 'KOI8-U';

    const ENC_GROUP_DEFAULT  = '_default_';
    const ENC_GROUP_GLOBAL   = 'GLOBAL';
    const ENC_GROUP_LATIN    = 'LATIN';
    const ENC_GROUP_CYRILLIC = 'CYRILLIC';
    const ENC_GROUP_KOI8     = 'KOI8';

    private $encodingGroupDefault = self::ENC_GROUP_LATIN;

    /**
     * defines the detect order for language groups
     * @var array
     */
    private $encodingGroups = [
        self::ENC_GROUP_LATIN    => [
            'defaultEncoding'     => self::ENC_WINDOWS_1252,
            'detectOrder'         => [self::ENC_ASCII, self::ENC_UTF_8, self::ENC_ISO_8859_1],
            'detectOrderModified' => false,
            'encodings'           => [self::ENC_ISO_8859_1, self::ENC_ISO_8859_15, self::ENC_WINDOWS_1252],
        ],
        self::ENC_GROUP_CYRILLIC => [
            'defaultEncoding'     => self::ENC_ISO_8859_5,
            'detectOrder'         => [self::ENC_ASCII, self::ENC_ISO_8859_5, self::ENC_KOI8_R, self::ENC_KOI8_U],
            'detectOrderModified' => false,
            'encodings'           => [self::ENC_ISO_8859_5],
        ],
        self::ENC_GROUP_KOI8     => [
            'defaultEncoding'     => self::ENC_KOI8_R,
            'detectOrder'         => [self::ENC_ASCII, self::ENC_UTF_8, self::ENC_KOI8_R, self::ENC_KOI8_U],
            'detectOrderModified' => false,
            'encodings'           => [self::ENC_KOI8_R, self::ENC_KOI8_U],
        ],
    ];

    /**
     * list of supported encodings and its groups
     * @var array
     */
    private $encodingList = [
        self::ENC_UTF_8        => self::ENC_GROUP_GLOBAL,
        self::ENC_ASCII        => self::ENC_GROUP_GLOBAL,
        self::ENC_ISO_8859_1   => self::ENC_GROUP_LATIN,
        self::ENC_ISO_8859_15  => self::ENC_GROUP_LATIN,
        self::ENC_WINDOWS_1252 => self::ENC_GROUP_LATIN,
        self::ENC_ISO_8859_5   => self::ENC_GROUP_CYRILLIC,
        self::ENC_WINDOWS_1251 => self::ENC_GROUP_CYRILLIC,
        self::ENC_KOI8_R       => self::ENC_GROUP_KOI8,
        self::ENC_KOI8_U       => self::ENC_GROUP_KOI8,
    ];

    /**
     * Encoder constructor.
     *
     * @param string $encGroupDefault
     *
     * @throws \Exception
     */
    public function __construct($encGroupDefault = self::ENC_GROUP_LATIN) {
        if (!isset($this->encodingGroups[$encGroupDefault])) {
            throw new Exception("encoding group {$encGroupDefault} is not known", 1001);
        }
        $this->encodingGroupDefault = $encGroupDefault;
    }

    public function convertArray($array, $encodingTo, &$decodeWith = null, $encGroup = self::ENC_GROUP_DEFAULT, $convertKeys = true): array {
        foreach ($array as $k => $v) {
            if ($convertKeys) {
                $k = $this->convertString($k, $encodingTo, $decodeWith, $encGroup);
            }
            if (is_array($v)) {
                $array[$k] = $this->convertArray($v, $encodingTo, $decodeWith, $encGroup);
            }
            elseif (is_string($v)) {
                $array[$k] = $this->convertString($v, $encodingTo, $decodeWith, $encGroup);
            }
        }
        return $array;
    }

    /**
     * convert a string
     *
     * @param      $string
     * @param      $decodeWith
     * @param null $encodingTo
     *
     * @return string
     * @throws \Exception
     */
    public function convertString($string, $encodingTo, &$decodeWith = null, $encGroup = self::ENC_GROUP_DEFAULT): string {
        if (is_null($string) || $string === '') {
            //nothing to decode
            return $string;
        }
        if (!isset($this->encodingList[$encodingTo])) {
            throw new Exception("encoding '{$decodeWith}' is not supported, tried string '{$string}'", 5001);
        }

        if ($encGroup === self::ENC_GROUP_DEFAULT) {
            $encGroup = $this->encodingGroupDefault;
        }

        if (!isset($this->encodingGroups[$encGroup])) {
            throw new Exception("encoding group '{$encGroup}' is not known, tried detect string '{$string}'", 6001);
        }

        if (!$decodeWith) {
            $decodeWith = $this->detectEncoding($string, $encGroup);
        }
        if (!isset($this->encodingList[$decodeWith])) {
            throw new Exception("unsupported encoding for decode: {$decodeWith} for string {$string}", 6002);
        }
        return mb_convert_encoding($string, $encodingTo, $decodeWith);
    }

    /**
     * detect encoding
     *
     * @param        $string
     * @param string $encGroup
     * @param bool   $malFormCheck
     *
     * @return false|mixed|string
     * @throws \Exception
     */
    public function detectEncoding($string, $encGroup = self::ENC_GROUP_DEFAULT, $malFormCheck = true): string {

        if ($encGroup === self::ENC_GROUP_DEFAULT) {
            $encGroup = $this->encodingGroupDefault;
        }

        if (!isset($this->encodingGroups[$encGroup])) {
            throw new Exception("encoding group '{$encGroup}' is not known, tried detect string '{$string}'", 6001);
        }
        $detected = mb_detect_encoding($string, $this->encodingGroups[$encGroup]['detectOrder'], true);
        if (!$detected) {
            throw new Exception("failed to detect encoding for string {$string}", 6002);
        }
        switch ($detected) {
            case self::ENC_UTF_8:
                if ($malFormCheck) {
                    /* detection shows us encoding, but lets try to ensure that we dont have a malformed string */
                    preg_match("/.*/u", $string);
                    $pcreError = preg_last_error();
                    if ($pcreError === PREG_BAD_UTF8_ERROR || $pcreError === PREG_BAD_UTF8_OFFSET_ERROR) {
                        return $this->encodingGroups[$encGroup]['defaultEncoding'];
                    }
                }
                break;
            case self::ENC_ISO_8859_1:
                if (!$this->encodingGroups[$encGroup]['detectOrderModified']) {
                    if (preg_match("/[\x7F-\x9F]/", $string, $matches)) {
                        /*
                         * ambiguous range is  not used in 8859-1 but in 8859-15 and others as well as in WINDOWS-1252
                         * in a common consensus WINDOWS-1252 is practicable here, but may not cover 100% of your belongings.
                        */
                        return self::ENC_WINDOWS_1252;
                    }
                    if (preg_match("/\xA4/", $string, $matches)) {
                        /*
                         * \xA4 is the position of EUR Symbol. So when we find this, we imagine ISO-8859-15 is used                     *
                        */
                        return self::ENC_ISO_8859_15;
                    }
                }
                break;
        }
        return $detected;
    }

    /**
     * @param $encGroup
     * @param $encoding
     *
     * @return bool
     */
    public function setDefaultEncoding($encGroup, $encoding): bool {
        if (!$this->isGroupSupported($encGroup)) {
            return false;
        }
        if (!isset($this->encodingList[$encoding])) {
            return false;
        }
        $this->encodingGroups[$encGroup]['defaultEncoding'] = $encoding;
        return true;
    }

    /**
     * is the encoding group supported
     *
     * @param $encGroup
     *
     * @return bool
     */
    public function isGroupSupported($encGroup): bool {
        return isset($this->encodingGroups[$encGroup]);
    }

    /**
     * @return array
     */
    public function getEncodingGroups(): array {
        return array_keys($this->encodingGroups);
    }

    /**
     * @param $encGroup
     *
     * @return array
     */
    public function getEncodingsByGroup($encGroup): array {
        if (!$this->isGroupSupported($encGroup)) {
            return [];
        }
        return $this->encodingGroups[$encGroup]['encodings'];
    }
}