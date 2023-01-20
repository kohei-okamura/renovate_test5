<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\Billing\RunUpdateLtcsBillingFilesJobInteractor;

/**
 * {@link \UseCase\Billing\RunUpdateLtcsBillingFilesJobInteractor} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait RunUpdateLtcsBillingFilesJobInteractorMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\Billing\RunUpdateLtcsBillingFilesJobInteractor
     */
    protected $runUpdateLtcsBillingFilesJobInteractor;

    /**
     * {@link \UseCase\Billing\RunUpdateLtcsBillingFilesJobInteractor} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinRunUpdateLtcsBillingFilesJobInteractor(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                RunUpdateLtcsBillingFilesJobInteractor::class,
                fn () => $self->runUpdateLtcsBillingFilesJobInteractor
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->runUpdateLtcsBillingFilesJobInteractor = Mockery::mock(
                RunUpdateLtcsBillingFilesJobInteractor::class
            );
        });
    }
}
