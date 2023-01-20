<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\LtcsBillingUser;
use Domain\Common\Carbon;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\LtcsInsCard\LtcsLevel;

/**
 * {@link \Domain\Billing\LtcsBillingUser} Holder.
 *
 * @property \Domain\Billing\LtcsBillingUser $user 利用者
 * @mixin \Eloquent
 */
trait LtcsBillingUserHolder
{
    /**
     * Get mutator for status attribute.
     *
     * @return \Domain\Billing\LtcsBillingUser
     * @noinspection PhpUnused
     */
    protected function getUserAttribute(): LtcsBillingUser
    {
        return new LtcsBillingUser(
            userId: $this->attributes['user_id'],
            ltcsInsCardId: $this->attributes['user_ltcs_ins_card_id'],
            insNumber: $this->attributes['user_ins_number'],
            name: new StructuredName(
                familyName: $this->attributes['user_family_name'],
                givenName: $this->attributes['user_given_name'],
                phoneticFamilyName: $this->attributes['user_phonetic_family_name'],
                phoneticGivenName: $this->attributes['user_phonetic_given_name'],
            ),
            sex: Sex::from($this->attributes['user_sex']),
            birthday: Carbon::parse($this->attributes['user_birthday']),
            ltcsLevel: LtcsLevel::from($this->attributes['user_ltcs_level']),
            activatedOn: Carbon::parse($this->attributes['user_activated_on']),
            deactivatedOn: Carbon::parse($this->attributes['user_deactivated_on']),
        );
    }

    /**
     * Set mutator for status attribute.
     *
     * @param \Domain\Billing\LtcsBillingUser $value
     * @return void
     * @noinspection PhpUnused
     */
    protected function setUserAttribute(LtcsBillingUser $value): void
    {
        $this->attributes['user_id'] = $value->userId;
        $this->attributes['user_ltcs_ins_card_id'] = $value->ltcsInsCardId;
        $this->attributes['user_ins_number'] = $value->insNumber;
        $this->attributes['user_family_name'] = $value->name->familyName;
        $this->attributes['user_given_name'] = $value->name->givenName;
        $this->attributes['user_phonetic_family_name'] = $value->name->phoneticFamilyName;
        $this->attributes['user_phonetic_given_name'] = $value->name->phoneticGivenName;
        $this->attributes['user_sex'] = $value->sex;
        $this->attributes['user_birthday'] = $value->birthday;
        $this->attributes['user_ltcs_level'] = $value->ltcsLevel;
        $this->attributes['user_activated_on'] = $value->activatedOn;
        $this->attributes['user_deactivated_on'] = $value->deactivatedOn;
    }
}
