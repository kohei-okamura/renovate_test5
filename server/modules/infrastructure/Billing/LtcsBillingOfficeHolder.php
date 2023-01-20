<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingOffice;
use Domain\Common\Addr;
use Domain\Common\Prefecture;

/**
 * {@link \Domain\Billing\LtcsBillingOffice} Holder.
 *
 * @property \Domain\Billing\LtcsBillingOffice $status 事業
 * @mixin \Eloquent
 */
trait LtcsBillingOfficeHolder
{
    /**
     * Get mutator for status attribute.
     *
     * @return \Domain\Billing\LtcsBillingOffice
     * @noinspection PhpUnused
     */
    protected function getOfficeAttribute(): LtcsBillingOffice
    {
        return new LtcsBillingOffice(
            officeId: $this->attributes['office_id'],
            code: $this->attributes['office_code'],
            name: $this->attributes['office_name'],
            abbr: $this->attributes['office_abbr'],
            addr: new Addr(
                postcode: $this->attributes['office_addr_postcode'],
                prefecture: Prefecture::from($this->attributes['office_addr_prefecture']),
                city: $this->attributes['office_addr_city'],
                street: $this->attributes['office_addr_street'],
                apartment: $this->attributes['office_addr_apartment'],
            ),
            tel: $this->attributes['office_tel'],
        );
    }

    /**
     * Set mutator for status attribute.
     *
     * @param \Domain\Billing\LtcsBillingOffice $value
     * @return void
     * @noinspection PhpUnused
     */
    protected function setOfficeAttribute(LtcsBillingOffice $value): void
    {
        $this->attributes['office_id'] = $value->officeId;
        $this->attributes['office_code'] = $value->code;
        $this->attributes['office_name'] = $value->name;
        $this->attributes['office_abbr'] = $value->abbr;
        $this->attributes['office_addr_postcode'] = $value->addr->postcode;
        $this->attributes['office_addr_prefecture'] = $value->addr->prefecture->value();
        $this->attributes['office_addr_city'] = $value->addr->city;
        $this->attributes['office_addr_street'] = $value->addr->street;
        $this->attributes['office_addr_apartment'] = $value->addr->apartment;
        $this->attributes['office_tel'] = $value->tel;
    }
}
