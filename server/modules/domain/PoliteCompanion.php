<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

use Lib\Exceptions\UndefinedPropertyException;

/**
 * {@link \Domain\Polite} に関する処理.
 */
final class PoliteCompanion
{
    /**
     * 単一の属性値を取得する.
     *
     * @param object $object
     * @param string $key
     * @return mixed
     */
    public static function get(object $object, string $key): mixed
    {
        if (property_exists($object, $key)) {
            return $object->{$key};
        } else {
            throw new UndefinedPropertyException("Undefined property: {$key}");
        }
    }

    /**
     * 属性値を連想配列として取得する.
     *
     * @param object $object
     * @return array
     */
    public static function toAssoc(object $object): array
    {
        return get_object_vars($object);
    }
}
