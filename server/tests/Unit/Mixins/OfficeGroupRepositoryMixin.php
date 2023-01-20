<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Office\OfficeGroupRepository;
use Mockery;

/**
 * OfficeGroupRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait OfficeGroupRepositoryMixin
{
    /**
     * @var \Domain\Office\OfficeGroupRepository|\Mockery\MockInterface
     */
    protected $officeGroupRepository;

    /**
     * OfficeGroupRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinOfficeGroupRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(OfficeGroupRepository::class, fn () => $self->officeGroupRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->officeGroupRepository = Mockery::mock(OfficeGroupRepository::class);
        });
    }
}
