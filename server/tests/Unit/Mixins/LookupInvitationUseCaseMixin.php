<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Staff\LookupInvitationUseCase;

/**
 * LookupInvitationUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait LookupInvitationUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Staff\LookupInvitationUseCase
     */
    protected $lookupInvitationUseCase;

    /**
     * LookupInvitationUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinLookupInvitationUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(LookupInvitationUseCase::class, fn () => $self->lookupInvitationUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->lookupInvitationUseCase = Mockery::mock(LookupInvitationUseCase::class);
        });
    }
}
