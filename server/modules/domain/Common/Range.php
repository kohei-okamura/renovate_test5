<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Domain\Common;

use Domain\Model;

/**
 * Range 基底クラス.
 *
 * @property-read mixed $start
 * @property-read mixed $end
 */
abstract class Range extends Model
{
    /**
     * 対象の値が範囲に含まれるかどうかを判定する.
     *
     * @param mixed $value
     * @return bool
     */
    public function contains($value): bool
    {
        return $this->start <= $value && $this->end >= $value;
    }

    /**
     * 対象の範囲と重複があるかどうかを判定する.
     *
     * @param static $that
     * @param bool $includeBoundary
     * @return bool
     */
    public function isOverlapping(Range $that, bool $includeBoundary = true): bool
    {
        return $includeBoundary
            ? $this->start <= $that->end && $this->end >= $that->start
            : $this->start < $that->end && $this->end > $that->start;
    }

    /** {@inheritdoc} */
    protected function attrs(): array
    {
        return [
            'start',
            'end',
        ];
    }

    /** {@inheritdoc} */
    protected function jsonables(): array
    {
        return [
            'start' => true,
            'end' => true,
        ];
    }
}
