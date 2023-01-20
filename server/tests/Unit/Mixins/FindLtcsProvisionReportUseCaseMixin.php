<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Mockery;
use UseCase\ProvisionReport\FindLtcsProvisionReportUseCase;

/**
 * FindLtcsProvisionReportUseCase Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait FindLtcsProvisionReportUseCaseMixin
{
    /**
     * @var \Mockery\MockInterface|\UseCase\ProvisionReport\FindLtcsProvisionReportUseCase
     */
    protected $findLtcsProvisionReportUseCase;

    /**
     * FindLtcsProvisionReportUseCase に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinFindLtcsProvisionReportUseCase(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(FindLtcsProvisionReportUseCase::class, fn () => $self->findLtcsProvisionReportUseCase);
        });
        static::beforeEachSpec(function ($self): void {
            $self->findLtcsProvisionReportUseCase = Mockery::mock(FindLtcsProvisionReportUseCase::class);
        });
    }
}
