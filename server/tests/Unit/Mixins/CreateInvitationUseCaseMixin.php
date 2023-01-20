<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\CreateInvitationUseCase;

/**
 * CreateInvitationUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateInvitationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\CreateInvitationUseCase
     */
    protected $createInvitationUseCase;

    /**
     * CreateInvitationUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinCreateInvitationUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateInvitationUseCase::class, fn () => $self->createInvitationUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createInvitationUseCase = Mockery::mock(CreateInvitationUseCase::class);
        });
    }
}
