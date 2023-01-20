<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Staff\InvitationRepository;
use Mockery;

/**
 * Invitation Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait InvitationRepositoryMixin
{
    /**
     * @var \Domain\Staff\InvitationRepository|\Mockery\MockInterface
     */
    protected $invitationRepository;

    /**
     * InvitationRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinInvitationRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(InvitationRepository::class, fn () => $self->invitationRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->invitationRepository = Mockery::mock(InvitationRepository::class);
        });
    }
}
