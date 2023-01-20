<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Calling\CreateCallingsUseCase;

/**
 * {@link \UseCase\Calling\CreateCallingsUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait CreateCallingUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Calling\CreateCallingsUseCase
     */
    protected $createCallingUseCase;

    public static function mixinCreateCallingUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(CreateCallingsUseCase::class, fn () => $self->createCallingUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->createCallingUseCase = Mockery::mock(CreateCallingsUseCase::class);
        });
    }
}
