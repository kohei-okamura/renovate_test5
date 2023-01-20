<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\DeleteLtcsProvisionReportUseCase;

/**
 * DeleteLtcsProvisionReportUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DeleteLtcsProvisionReportUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\DeleteLtcsProvisionReportUseCase
     */
    protected $deleteLtcsProvisionReportUseCase;

    /**
     * DeleteLtcsProvisionReportUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinDeleteLtcsProvisionReportUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DeleteLtcsProvisionReportUseCase::class, fn () => $self->deleteLtcsProvisionReportUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->deleteLtcsProvisionReportUseCase = Mockery::mock(DeleteLtcsProvisionReportUseCase::class);
        });
    }
}
