<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingOffice;
use Domain\Common\Addr;
use Domain\Common\Prefecture;

/**
 * Trait DwsBillingOfficeHolder
 *
 * @property-read \Domain\Billing\DwsBillingOffice $office
 * @mixin \Infrastructure\Model
 */
trait DwsBillingOfficeHolder
{
    /**
     * Get mutator for office.
     *
     * @return \Domain\Billing\DwsBillingOffice
     * @noinspection PhpUnused
     */
    protected function getOfficeAttribute(): DwsBillingOffice
    {
        return DwsBillingOffice::create([
            'officeId' => $this->attributes['office_id'],
            'code' => $this->attributes['office_code'],
            'name' => $this->attributes['office_name'],
            'abbr' => $this->attributes['office_abbr'],
            'addr' => new Addr(
                postcode: $this->attributes['office_addr_postcode'],
                prefecture: Prefecture::from($this->attributes['office_addr_prefecture']),
                city: $this->attributes['office_addr_city'],
                street: $this->attributes['office_addr_street'],
                apartment: $this->attributes['office_addr_apartment'],
            ),
            'tel' => $this->attributes['office_tel'],
        ]);
    }

    /**
     * Set mutator for office.
     *
     * @param \Domain\Billing\DwsBillingOffice $office
     * @noinspection PhpUnused
     */
    protected function setOfficeAttribute(DwsBillingOffice $office): void
    {
        $this->attributes['office_id'] = $office->officeId;
        $this->attributes['office_code'] = $office->code;
        $this->attributes['office_name'] = $office->name;
        $this->attributes['office_abbr'] = $office->abbr;
        $this->attributes['office_addr_postcode'] = $office->addr->postcode;
        $this->attributes['office_addr_prefecture'] = $office->addr->prefecture->value();
        $this->attributes['office_addr_city'] = $office->addr->city;
        $this->attributes['office_addr_street'] = $office->addr->street;
        $this->attributes['office_addr_apartment'] = $office->addr->apartment;
        $this->attributes['office_tel'] = $office->tel;
    }
}
