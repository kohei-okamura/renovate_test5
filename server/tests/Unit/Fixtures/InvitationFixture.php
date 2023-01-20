<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Fixtures;

use Infrastructure\Staff\Invitation;

/**
 * Invitation fixture.
 *
 * @mixin \Tests\Unit\Examples\ExamplesConsumer
 */
trait InvitationFixture
{
    /**
     * 招待 登録.
     *
     * @return void
     */
    protected function createInvitations(): void
    {
        foreach ($this->examples->invitations as $entity) {
            $invitation = Invitation::fromDomain($entity)->saveIfNotExists();
            $invitation->roles()->sync($entity->roleIds);
            $invitation->offices()->sync($entity->officeIds);
            $invitation->officeGroups()->sync($entity->officeGroupIds);
        }
    }
}
