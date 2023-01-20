<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Common;

use Domain\Common\Addr;
use Domain\Common\Prefecture;

/**
 * {@link \Domain\Common\Addr} Holder.
 *
 * @property string $addr_postcode 郵便番号
 * @property int $addr_prefecture 都道府県
 * @property string $addr_city 市区町村
 * @property string $addr_street 町名・番地
 * @property string $addr_apartment 建物名など
 * @property-read \Domain\Common\Addr $addr 住所
 * @method static \Illuminate\Database\Eloquent\Builder|static whereAddrApartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereAddrCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereAddrPostcode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereAddrPrefecture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereAddrStreet($value)
 * @mixin \Eloquent
 */
trait AddrHolder
{
    /**
     * Get mutator for addr attribute.
     *
     * @return \Domain\Common\Addr
     * @noinspection PhpUnused
     */
    protected function getAddrAttribute(): Addr
    {
        return new Addr(
            postcode: $this->addr_postcode,
            prefecture: Prefecture::from($this->addr_prefecture),
            city: $this->addr_city,
            street: $this->addr_street,
            apartment: $this->addr_apartment,
        );
    }

    /**
     * Set mutator for addr attribute.
     *
     * @param \Domain\Common\Addr $x
     * @return void
     * @noinspection PhpUnused
     */
    protected function setAddrAttribute(Addr $x): void
    {
        $this->attributes['addr_postcode'] = $x->postcode;
        $this->attributes['addr_prefecture'] = $x->prefecture->value();
        $this->attributes['addr_city'] = $x->city;
        $this->attributes['addr_street'] = $x->street;
        $this->attributes['addr_apartment'] = $x->apartment;
    }
}
