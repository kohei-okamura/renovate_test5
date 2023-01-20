<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Office\OfficeFinder;
use Mockery;

/**
 * OfficeFinder Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait OfficeFinderMixin
{
    /**
     * @var \Domain\Office\OfficeFinder|\Mockery\MockInterface
     */
    protected $officeFinder;

    /**
     * OfficeFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinOfficeFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(OfficeFinder::class, fn () => $self->officeFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->officeFinder = Mockery::mock(OfficeFinder::class);
        });
    }
}
