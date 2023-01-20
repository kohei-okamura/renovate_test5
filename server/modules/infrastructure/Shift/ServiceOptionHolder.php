<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Shift;

use Domain\Shift\ServiceOption;

/**
 * {@link \Domain\Shift\ServiceOption} Holder.
 *
 * ※このトレイトをクラスに追加する場合は {@link \Infrastructure\Shift\ServiceOptionProvider} を実装すること.
 *
 * @property \Domain\Shift\ServiceOption $service_option サービスオプション（勤務シフト・勤務実績）ID
 * @method static \Illuminate\Database\Eloquent\Builder|static whereServiceOption($value)
 * @mixin \Eloquent
 */
trait ServiceOptionHolder
{
    /**
     * Get mutator for service_option.
     *
     * @return \Domain\Shift\ServiceOption
     * @noinspection PhpUnused
     */
    protected function getServiceOptionAttribute(): ServiceOption
    {
        return ServiceOption::from($this->attributes['service_option']);
    }

    /**
     * Set mutator for service_option.
     *
     * @param \Domain\Shift\ServiceOption $service_option
     * @return void
     * @noinspection PhpUnused
     */
    protected function setServiceOptionAttribute(ServiceOption $service_option): void
    {
        $this->attributes['service_option'] = $service_option->value();
    }
}
