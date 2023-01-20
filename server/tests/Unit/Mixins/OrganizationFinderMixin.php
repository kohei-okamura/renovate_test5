<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Organization\OrganizationFinder;
use Mockery;

/**
 * {@link \Domain\Organization\OrganizationFinder} Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait OrganizationFinderMixin
{
    /**
     * @var \Domain\Organization\OrganizationFinder|\Mockery\MockInterface
     */
    protected $organizationFinder;

    /**
     * OrganizationFinder に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinOrganizationFinder(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(OrganizationFinder::class, fn () => $self->organizationFinder);
        });
        static::beforeEachSpec(function ($self): void {
            $self->organizationFinder = Mockery::mock(OrganizationFinder::class);
        });
    }
}
