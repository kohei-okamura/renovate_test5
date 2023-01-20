<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Equatable;
use JetBrains\PhpStorm\Pure;
use JsonSerializable;
use Lib\Math;

/**
 * 小数を表すモデル.
 *
 * 内部的に整数部n桁 + 小数部4桁の整数として扱う.
 */
final class Decimal implements Equatable, JsonSerializable
{
    private const FRACTION_DIGITS = 4;

    private int $value;

    /**
     * {@link \Domain\Common\Decimal} constructor.
     *
     * @param int $value
     */
    private function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * 整数表現と小数部の桁数を受け取り新たなインスタンスを生成する.
     *
     * 小数部の桁数が5桁以上の場合は切り捨てる.
     *
     * @param int $value
     * @param int $fractionDigits
     * @return static
     */
    #[Pure]
    public static function fromInt(int $value, int $fractionDigits = self::FRACTION_DIGITS): self
    {
        return new self(Math::floor($value * 10 ** (self::FRACTION_DIGITS - $fractionDigits)));
    }

    /**
     * ゼロを表すインスタンスを生成する.
     *
     * @return static
     */
    #[Pure]
    public static function zero(): self
    {
        return new self(0);
    }

    /**
     * 値がゼロかどうかを判定する.
     *
     * @return bool
     */
    #[Pure]
    public function isZero(): bool
    {
        return $this->value === 0;
    }

    /**
     * 小数部の桁数を受け取り整数表現を返す.
     *
     * @param int $fractionDigits
     * @return int
     */
    #[Pure]
    public function toInt(int $fractionDigits = self::FRACTION_DIGITS): int
    {
        return Math::floor($this->value * 10 ** ($fractionDigits - self::FRACTION_DIGITS));
    }

    /**
     * 浮動小数点数を返す.
     *
     * @return float
     */
    #[Pure]
    public function toFloat(): float
    {
        return $this->value / (10 ** self::FRACTION_DIGITS);
    }

    /** {@inheritdoc} */
    #[Pure]
    public function equals(mixed $that): bool
    {
        return $that instanceof self && $this->value === $that->value;
    }

    /** {@inheritdoc} */
    #[Pure]
    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
