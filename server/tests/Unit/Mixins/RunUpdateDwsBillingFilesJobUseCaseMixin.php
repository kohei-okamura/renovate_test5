<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\RunUpdateDwsBillingFilesJobUseCase;

/**
 * {@link \UseCase\Billing\RunUpdateDwsBillingFilesJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunUpdateDwsBillingFilesJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\RunUpdateDwsBillingFilesJobUseCase
     */
    protected $runUpdateDwsBillingFilesJobUseCase;

    /**
     * {@link \UseCase\Billing\RunUpdateDwsBillingFilesJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunUpdateDwsBillingFilesJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunUpdateDwsBillingFilesJobUseCase::class,
                fn () => $self->runUpdateDwsBillingFilesJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runUpdateDwsBillingFilesJobUseCase = Mockery::mock(
                RunUpdateDwsBillingFilesJobUseCase::class
            );
        });
    }
}
