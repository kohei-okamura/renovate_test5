<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\CreateStaffWithInvitationUseCase;

/**
 * {@link \UseCase\Staff\CreateStaffWithInvitationUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateStaffWithInvitationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\CreateStaffWithInvitationUseCase
     */
    protected $createStaffWithInvitationUseCase;

    /**
     * {@link \UseCase\Staff\CreateStaffWithInvitationUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateStaffWithInvitationUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                CreateStaffWithInvitationUseCase::class,
                fn () => $self->createStaffWithInvitationUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->createStaffWithInvitationUseCase = Mockery::mock(
                CreateStaffWithInvitationUseCase::class
            );
        });
    }
}
