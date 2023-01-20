<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Infrastructure\Billing;

use Domain\Billing\DwsBillingUser;
use Domain\Common\StructuredName;

/**
 * {@link \Domain\Billing\DwsBillingUser} Holder.
 *
 * @property-read \Domain\Billing\DwsBillingUser $user 利用者
 * @mixin \Infrastructure\Model
 */
trait DwsBillingUserHolder
{
    /**
     * Get mutator for user.
     *
     * @return \Domain\Billing\DwsBillingUser
     * @noinspection PhpUnused
     */
    protected function getUserAttribute(): DwsBillingUser
    {
        return DwsBillingUser::create([
            'userId' => $this->attributes['user_id'],
            'dwsCertificationId' => $this->attributes['user_dws_certification_id'],
            'dwsNumber' => $this->attributes['user_dws_number'],
            'name' => new StructuredName(
                familyName: $this->attributes['user_family_name'],
                givenName: $this->attributes['user_given_name'],
                phoneticFamilyName: $this->attributes['user_phonetic_family_name'],
                phoneticGivenName: $this->attributes['user_phonetic_given_name'],
            ),
            'childName' => new StructuredName(
                familyName: $this->attributes['user_child_family_name'],
                givenName: $this->attributes['user_child_given_name'],
                phoneticFamilyName: $this->attributes['user_child_phonetic_family_name'],
                phoneticGivenName: $this->attributes['user_child_phonetic_given_name'],
            ),
            'copayLimit' => $this->attributes['user_copay_limit'],
        ]);
    }

    /**
     * Set mutator for user.
     *
     * @param \Domain\Billing\DwsBillingUser $user
     * @noinspection PhpUnused
     */
    protected function setUserAttribute(DwsBillingUser $user): void
    {
        $this->attributes['user_id'] = $user->userId;
        $this->attributes['user_dws_certification_id'] = $user->dwsCertificationId;
        $this->attributes['user_dws_number'] = $user->dwsNumber;
        $this->attributes['user_family_name'] = $user->name->familyName;
        $this->attributes['user_given_name'] = $user->name->givenName;
        $this->attributes['user_phonetic_family_name'] = $user->name->phoneticFamilyName;
        $this->attributes['user_phonetic_given_name'] = $user->name->phoneticGivenName;
        $this->attributes['user_child_family_name'] = $user->childName->familyName;
        $this->attributes['user_child_given_name'] = $user->childName->givenName;
        $this->attributes['user_child_phonetic_family_name'] = $user->childName->phoneticFamilyName;
        $this->attributes['user_child_phonetic_given_name'] = $user->childName->phoneticGivenName;
        $this->attributes['user_copay_limit'] = $user->copayLimit;
    }
}
