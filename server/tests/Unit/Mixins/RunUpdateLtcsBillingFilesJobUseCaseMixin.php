<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\RunUpdateLtcsBillingFilesJobUseCase;

/**
 * {@link \UseCase\Billing\RunUpdateLtcsBillingFilesJobUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunUpdateLtcsBillingFilesJobUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\RunUpdateLtcsBillingFilesJobUseCase
     */
    protected $runUpdateLtcsBillingFilesJobUseCase;

    /**
     * {@link \UseCase\Billing\RunUpdateLtcsBillingFilesJobUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunUpdateLtcsBillingFilesJobUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunUpdateLtcsBillingFilesJobUseCase::class,
                fn () => $self->runUpdateLtcsBillingFilesJobUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runUpdateLtcsBillingFilesJobUseCase = Mockery::mock(
                RunUpdateLtcsBillingFilesJobUseCase::class
            );
        });
    }
}
