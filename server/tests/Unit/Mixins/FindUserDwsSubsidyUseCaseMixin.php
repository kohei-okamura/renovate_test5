<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\User\FindUserDwsSubsidyUseCase;

/**
 * FindUserDwsSubsidyUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindUserDwsSubsidyUseCaseMixin
{
    /**
     * @var \Domain\DwsCertification\DwsCertification|\Mockery\MockInterface
     */
    protected $findUserDwsSubsidyUseCase;

    public static function mixinUserDwsSubsidyUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindUserDwsSubsidyUseCase::class, fn () => $self->findUserDwsSubsidyUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findUserDwsSubsidyUseCase = Mockery::mock(FindUserDwsSubsidyUseCase::class);
        });
    }
}
