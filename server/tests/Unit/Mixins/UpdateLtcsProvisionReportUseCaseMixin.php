<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\UpdateLtcsProvisionReportUseCase;

/**
 * UpdateLtcsProvisionReportUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait UpdateLtcsProvisionReportUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\UpdateLtcsProvisionReportUseCase
     */
    protected $updateLtcsProvisionReportUseCase;

    /**
     * UpdateLtcsProvisionReportUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinUpdateLtcsProvisionReportUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(UpdateLtcsProvisionReportUseCase::class, fn () => $self->updateLtcsProvisionReportUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->updateLtcsProvisionReportUseCase = Mockery::mock(UpdateLtcsProvisionReportUseCase::class);
        });
    }
}
