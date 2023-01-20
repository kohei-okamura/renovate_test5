<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Lib;

use JsonException;
use ScalikePHP\Option;

/**
 * JSON Encoder/Decoder.
 */
final class Json
{
    private const DEFAULT_DEPTH = 512;

    /**
     * Returns the JSON representation of a value.
     *
     * @param mixed $value
     * @param int $options
     * @return false|string
     */
    public static function encode($value, $options = \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR)
    {
        return json_encode($value, $options);
    }

    /**
     * Decodes a JSON string.
     *
     * @param string $json
     * @param bool $assoc
     * @throws \JsonException
     * @return mixed
     */
    public static function decode($json, $assoc = false)
    {
        return json_decode($json, $assoc, self::DEFAULT_DEPTH, \JSON_THROW_ON_ERROR);
    }

    /**
     * Decodes a JSON string safety.
     *
     * @param string $json
     * @param bool $assoc
     * @return \ScalikePHP\Option
     */
    public static function decodeSafety($json, $assoc = false): Option
    {
        try {
            $value = self::decode($json, $assoc);
            return Option::some($value);
        } catch (JsonException $exception) {
            return Option::none();
        }
    }
}
