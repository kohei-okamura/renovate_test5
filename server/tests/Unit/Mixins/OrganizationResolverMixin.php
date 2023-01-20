<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use App\Resolvers\OrganizationResolver;
use Mockery;
use ScalikePHP\Option;

/**
 * OrganizationResolver Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait OrganizationResolverMixin
{
    /**
     * @var \App\Resolvers\OrganizationResolver|\Mockery\MockInterface
     */
    protected $organizationResolver;

    /**
     * OrganizationResolver に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinOrganizationResolver(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(OrganizationResolver::class, fn () => $self->organizationResolver);
        });
        static::beforeEachSpec(function ($self): void {
            $self->organizationResolver = Mockery::mock(OrganizationResolver::class);
            $self->organizationResolver
                ->allows('resolve')
                ->andReturn(Option::from($self->examples->organizations[0]))
                ->byDefault();
        });
    }
}
