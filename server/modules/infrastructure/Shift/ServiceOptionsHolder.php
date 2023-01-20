<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

/**
 * Array of {@link \Domain\Shift\ServiceOption} Holder.
 *
 * @property \Domain\Shift\ServiceOption[] $options サービスオプション
 * @mixin \Eloquent
 * @mixin \Infrastructure\Concerns\RelationSupport
 */
trait ServiceOptionsHolder
{
    /**
     * Get mutator for options.
     *
     * @return \Domain\Shift\ServiceOption[]
     * @noinspection PhpUnused
     */
    protected function getOptionsAttribute(): array
    {
        return $this->mapRelation('options', fn (ServiceOptionProvider $x) => $x->service_option);
    }
}
