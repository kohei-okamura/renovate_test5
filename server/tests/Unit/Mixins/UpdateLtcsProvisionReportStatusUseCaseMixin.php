<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\UpdateLtcsProvisionReportStatusUseCase;

/**
 * UpdateLtcsProvisionReportStatusUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UpdateLtcsProvisionReportStatusUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\UpdateLtcsProvisionReportStatusUseCase
     */
    protected $updateLtcsProvisionReportStatusUseCase;

    /**
     * UpdateLtcsProvisionReportStatusUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUpdateLtcsProvisionReportStatusUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(UpdateLtcsProvisionReportStatusUseCase::class, fn () => $self->updateLtcsProvisionReportStatusUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->updateLtcsProvisionReportStatusUseCase = Mockery::mock(UpdateLtcsProvisionReportStatusUseCase::class);
        });
    }
}
