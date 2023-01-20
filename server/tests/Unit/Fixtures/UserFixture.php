<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Domain\Common\Contact;
use Infrastructure\User\User;
use Infrastructure\User\UserAttr;
use Infrastructure\User\UserContact;

/**
 * User fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait UserFixture
{
    /**
     * 利用者 登録.
     *
     * @return void
     */
    protected function createUsers(): void
    {
        foreach ($this->examples->users as $entity) {
            $user = User::fromDomain($entity)->saveIfNotExists();
            $attr = UserAttr::fromDomain($entity);
            $user->attr()->save($attr);
            $userContacts = array_map(
                fn (Contact $domainContact, int $index): UserContact => UserContact::fromDomain(
                    $domainContact,
                    [
                        'user_attr_id' => $attr->id,
                        'sort_order' => $index,
                    ]
                ),
                $entity->contacts,
                array_keys($entity->contacts)
            );
            $attr->contacts()->saveMany($userContacts);
        }
    }
}
