<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Office\OfficeGroupFinder;
use Mockery;

/**
 * OfficeGroupFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait OfficeGroupFinderMixin
{
    /**
     * @var \Domain\Office\OfficeGroupFinder|\Mockery\MockInterface
     */
    protected $officeGroupFinder;

    /**
     * OfficeGroupFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinOfficeGroupFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(OfficeGroupFinder::class, fn () => $self->officeGroupFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->officeGroupFinder = Mockery::mock(OfficeGroupFinder::class);
        });
    }
}
