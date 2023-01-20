<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\ServiceCode;

use Domain\ServiceCode\ServiceCode;

/**
 * {@link \Domain\ServiceCode\ServiceCode} Holder.
 *
 * @property \Domain\ServiceCode\ServiceCode $service_code サービスコード
 * @property string $service_division_code サービス種類コード
 * @property string $service_category_code サービス項目コード
 * @method static \Illuminate\Database\Eloquent\Builder|static whereServiceCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereServiceDivisionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereServiceCategoryCode($value)
 * @mixin \Eloquent
 */
trait ServiceCodeHolder
{
    /**
     * Get mutator for service_code attribute.
     *
     * @return null|\Domain\ServiceCode\ServiceCode
     * @noinspection PhpUnused
     */
    protected function getServiceCodeAttribute(): ?ServiceCode
    {
        return $this->attributes['service_code'] === ''
            ? null
            : ServiceCode::fromString($this->attributes['service_code']);
    }

    /**
     * Set mutator for service_code attribute.
     *
     * @param null|\Domain\ServiceCode\ServiceCode $serviceCode
     * @return void
     * @noinspection PhpUnused
     */
    protected function setServiceCodeAttribute(?ServiceCode $serviceCode): void
    {
        if ($serviceCode === null) {
            $this->attributes['service_code'] = '';
            $this->attributes['service_division_code'] = '';
            $this->attributes['service_category_code'] = '';
        } else {
            $this->attributes['service_code'] = $serviceCode->toString();
            $this->attributes['service_division_code'] = $serviceCode->serviceDivisionCode;
            $this->attributes['service_category_code'] = $serviceCode->serviceCategoryCode;
        }
    }
}
