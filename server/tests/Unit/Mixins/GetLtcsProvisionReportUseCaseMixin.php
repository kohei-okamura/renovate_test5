<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\GetLtcsProvisionReportUseCase;

/**
 * {@link \UseCase\ProvisionReport\GetLtcsProvisionReportUseCase} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait GetLtcsProvisionReportUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\GetLtcsProvisionReportUseCase
     */
    protected $getLtcsProvisionReportUseCase;

    /**
     * {@link \UseCase\ProvisionReport\GetLtcsProvisionReportUseCase} に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinGetLtcsProvisionReportUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            assert($self instanceof self);
            app()->bind(
                GetLtcsProvisionReportUseCase::class,
                fn () => $self->getLtcsProvisionReportUseCase
            );
        });
        static::beforeEachSpec(function ($self): void {
            assert($self instanceof self);
            $self->getLtcsProvisionReportUseCase = Mockery::mock(
                GetLtcsProvisionReportUseCase::class
            );
        });
    }
}
