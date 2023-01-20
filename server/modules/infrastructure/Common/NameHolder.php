<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Common;

use Domain\Common\StructuredName;

/**
 * {@link \Domain\Common\StructuredName} Holder.
 *
 * @property string $family_name 姓
 * @property string $given_name 名
 * @property string $phonetic_family_name フリガナ：姓
 * @property string $phonetic_given_name フリガナ：名
 * @property-read \Domain\Common\StructuredName $name 氏名
 * @method static \Illuminate\Database\Eloquent\Builder|static whereFamilyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static whereGivenName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static wherePhoneticFamilyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|static wherePhoneticGivenName($value)
 * @mixin \Eloquent
 */
trait NameHolder
{
    /**
     * Get mutator for name attribute.
     *
     * @return \Domain\Common\StructuredName
     * @noinspection PhpUnused
     */
    protected function getNameAttribute(): StructuredName
    {
        return new StructuredName(
            familyName: $this->family_name,
            givenName: $this->given_name,
            phoneticFamilyName: $this->phonetic_family_name,
            phoneticGivenName: $this->phonetic_given_name,
        );
    }

    /**
     * Set mutator for name attribute.
     *
     * @param \Domain\Common\StructuredName $name
     * @return void
     * @noinspection PhpUnused
     */
    protected function setNameAttribute(StructuredName $name): void
    {
        $this->attributes['family_name'] = $name->familyName;
        $this->attributes['given_name'] = $name->givenName;
        $this->attributes['phonetic_family_name'] = $name->phoneticFamilyName;
        $this->attributes['phonetic_given_name'] = $name->phoneticGivenName;
    }
}
