<?php
declare(strict_types=1);

namespace EncoderTest;

use PHPUnit\Framework\TestCase;
use Encoder;

/**
 *
 */
final class EncoderTest extends TestCase {

    public function testInstance() {
        try {
            $e = new \Encoder\Encoder();
            $this->assertIsObject($e);
            return $e;
        }
        catch (\Exception $e) {
            $this->assertIsObject(null, $e->getMessage());
        }
        return null;
    }

    /**
     * @depends testInstance
     *
     * @throws \Exception
     */
    public function testConvert(Encoder\Encoder $e) {
        $string = 'äöüß';
        $decodeWith = $e::ENC_UTF_8;
        $encoded = $e->convertString($string, $e::ENC_WINDOWS_1252, $decodeWith);
        $this->assertSame(utf8_decode($string), $encoded);
    }

    /**
     * @depends testInstance
     *
     *
     * @param Encoder\Encoder $e
     *
     * @throws \Exception
     */
    public function testConvertArray(Encoder\Encoder $e) {
        $string = 'äöüß';
        $testArray = [
            'äö' => 'üß',
            'ab' => 'ac',
        ];
        $compareArray = [];
        foreach ($testArray as $k => $v) {
            $k = utf8_decode($k);
            $v = utf8_decode($v);
            $compareArray[$k] = $v;
        }

        $decodeWith = $e::ENC_UTF_8;
        $encodedArray = $e->convertArray($testArray, $e::ENC_WINDOWS_1252, $decodeWith);
        foreach ($compareArray as $k => $v) {
            $this->assertTrue(array_key_exists($k,$encodedArray));
            $this->assertSame($v, $encodedArray[$k]);
        }
    }

    /**
     * @depends  testInstance
     *
     * @param Encoder\Encoder $e
     */
    public function testDetect(Encoder\Encoder $e) {
        $string = 'öääüß';
        $this->assertSame($e::ENC_UTF_8, $e->detectEncoding($string));
        $stringWindowsEncoding = utf8_decode($string);
        $this->assertSame($e::ENC_ISO_8859_1, $e->detectEncoding($stringWindowsEncoding, $e::ENC_GROUP_LATIN));

        $euro = '€';
        $isoEuro = $e->convertString($euro, $e::ENC_WINDOWS_1252);
        $this->assertSame($e::ENC_WINDOWS_1252, $e->detectEncoding($isoEuro, $e::ENC_GROUP_LATIN));

        $euro = '€';
        $isoEuro = $e->convertString($euro, $e::ENC_ISO_8859_15);
        $this->assertSame($e::ENC_ISO_8859_15, $e->detectEncoding($isoEuro, $e::ENC_GROUP_LATIN));
    }
}