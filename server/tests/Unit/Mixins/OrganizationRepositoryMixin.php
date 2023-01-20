<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Mixins;

use Domain\Organization\OrganizationRepository;
use Mockery;

/**
 * OrganizationRepository Mixin.
 *
 * @mixin \Tests\Unit\Helpers\UnitSupport
 */
trait OrganizationRepositoryMixin
{
    /**
     * @var \Domain\Organization\OrganizationRepository|\Mockery\MockInterface
     */
    protected $organizationRepository;

    /**
     * OrganizationRepository に関する初期化・終了処理を登録する.
     *
     * @return void
     */
    public static function mixinOrganizationRepository(): void
    {
        static::beforeEachTest(function ($self): void {
            app()->bind(OrganizationRepository::class, fn () => $self->organizationRepository);
        });
        static::beforeEachSpec(function ($self): void {
            $self->organizationRepository = Mockery::mock(OrganizationRepository::class);
        });
    }
}
