<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\DwsCertification\DwsCertificationFinder;
use Mockery;

/**
 * DwsCertificationFinder Mixin
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait DwsCertificationFinderMixin
{
    /**
     * @var \Domain\DwsCertification\DwsCertification|\Mockery\MockInterface
     */
    protected $dwsCertificationFinder;

    public static function mixinDwsCertificationFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(DwsCertificationFinder::class, fn () => $self->dwsCertificationFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->dwsCertificationFinder = Mockery::mock(DwsCertificationFinder::class);
        });
    }
}
