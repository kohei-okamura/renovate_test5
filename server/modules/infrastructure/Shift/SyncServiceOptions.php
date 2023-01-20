<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

use Domain\Shift\ServiceOption;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ScalikePHP\Seq;

/**
 * Eloquent モデル向けサービスオプション同期処理.
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\Infrastructure\Shift\ServiceOptionProvider[] $options
 * @mixin \Eloquent
 */
trait SyncServiceOptions
{
    /**
     * HasMany: ShiftServiceOption.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    abstract public function options(): HasMany;

    /**
     * サービスオプションの一覧を同期する.
     *
     * @param \Domain\Shift\ServiceOption[]|iterable $options
     * @return void
     */
    public function syncServiceOptions(iterable $options): void
    {
        $given = Seq::from(...$options);
        $current = Seq::from(...$this->options);

        $old = $current->filterNot(fn (ServiceOption $x): bool => $given->contains($x));
        $this->options()
            ->whereIn('service_option', [...$old->map(fn (ServiceOption $x): int => $x->value())])
            ->delete();

        $new = $given->filterNot(fn (ServiceOption $x): bool => $current->contains($x));
        foreach ($new as $value) {
            $this->options()->create(['service_option' => $value]);
        }

        $this->refresh();
    }
}
