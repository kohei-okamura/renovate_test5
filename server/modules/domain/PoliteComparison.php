<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain;

use Carbon\CarbonInterface;
use ScalikePHP\Seq;
use Traversable;

/**
 * PHP 8.1 版ドメインモデル比較処理.
 */
trait PoliteComparison
{
    /**
     * 与えられた2つの値が一致するかどうかを判定する.
     *
     * @param mixed $a
     * @param mixed $b
     * @return bool
     */
    private static function compare(mixed $a, mixed $b): bool
    {
        return $a instanceof Equatable && $a->equals($b)
            || $a instanceof CarbonInterface && $b instanceof CarbonInterface && $a->eq($b)
            || is_array($a) && is_array($b) && self::compareArray($a, $b)
            || $a instanceof Traversable && $b instanceof Traversable && self::compareTraversable($a, $b)
            || $a === $b;
    }

    /**
     * 与えられた2つの配列が一致するかどうかを判定する.
     *
     * @param array $a
     * @param array $b
     * @return bool
     */
    private static function compareArray(array $a, array $b): bool
    {
        if (count($a) !== count($b)) {
            return false;
        } else {
            $xs = array_keys($a);
            $p = fn ($key): bool => array_key_exists($key, $b) && self::compare($a[$key], $b[$key]);
            return Seq::fromArray($xs)->forAll($p);
        }
    }

    /**
     * 与えられた2つの {@link \Traversable} が一致するかどうかを判定する.
     *
     * @param \Traversable $a
     * @param \Traversable $b
     * @return bool
     */
    private static function compareTraversable(Traversable $a, Traversable $b): bool
    {
        return self::compareArray(iterator_to_array($a), iterator_to_array($b));
    }
}
