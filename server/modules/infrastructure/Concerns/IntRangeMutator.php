<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Concerns;

use Domain\Common\IntRange;

/**
 * IntRange Mutator.
 *
 * @mixin \Eloquent
 */
trait IntRangeMutator
{
    /**
     * Get mutator for IntRange Value.
     *
     * @param string $key
     * @return \Domain\Common\IntRange
     */
    protected function getIntRange(string $key): IntRange
    {
        return IntRange::create([
            'start' => $this->attributes["{$key}_start"],
            'end' => $this->attributes["{$key}_end"],
        ]);
    }

    /**
     * Set mutator for IntRange Value.
     *
     * @param \Domain\Common\IntRange $intRange
     * @param string $key
     * @return void
     */
    protected function setIntRange(IntRange $intRange, string $key): void
    {
        $this->attributes["{$key}_start"] = $intRange->start;
        $this->attributes["{$key}_end"] = $intRange->end;
    }
}
