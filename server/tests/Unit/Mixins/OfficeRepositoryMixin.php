<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Office\OfficeRepository;
use Mockery;

/**
 * OfficeRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait OfficeRepositoryMixin
{
    /**
     * @var \Domain\Office\OfficeRepository|\Mockery\MockInterface
     */
    protected $officeRepository;

    /**
     * OfficeRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinOfficeRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(OfficeRepository::class, fn () => $self->officeRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->officeRepository = Mockery::mock(OfficeRepository::class);
        });
    }
}
