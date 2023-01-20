<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Infrastructure\Organization\OrganizationRepositoryFallback;
use Mockery;

/**
 * OrganizationRepositoryFallback Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait OrganizationRepositoryFallbackMixin
{
    /**
     * @var \Infrastructure\Organization\OrganizationRepositoryFallback|\Mockery\MockInterface
     */
    protected $organizationRepositoryFallback;

    /**
     * OrganizationRepositoryFallback に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinOrganizationRepositoryFallback(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(OrganizationRepositoryFallback::class, fn () => $self->organizationRepositoryFallback);
        });
        static::beforeEachSpec(function ($self): void {
            $self->organizationRepositoryFallback = Mockery::mock(OrganizationRepositoryFallback::class);
        });
    }
}
